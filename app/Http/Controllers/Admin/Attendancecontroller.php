<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Admin_panel_setting;
use App\Models\Shifts_type;
use App\Models\FingerprintLog;
use App\Models\FingerprintDevice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Http\Controllers\Admin\LeaveCompensationController;

class AttendanceController extends Controller
{
    /**
     * يحسب مقسوم اليوم ومعاملات الساعة من الإعدادات
     */
    private function resolveRates(Employee $employee, Admin_panel_setting $settings, string $date): array
    {
        $dayDivisor = match ((int)($settings->day_rate_divisor_type ?? 1)) {
            2 => 30,
            3 => Carbon::parse($date)->daysInMonth,
            4 => max(1, (float)($settings->day_rate_divisor_custom ?? 26)),
            default => 26,
        };

        return [
            'dailyRate'         => $employee->emp_sal ? ($employee->emp_sal / $dayDivisor) : 0,
            'hourDivisorType'   => (int)($settings->hour_rate_divisor_type ?? 1),
            'hourDivisorCustom' => max(1.0, (float)($settings->hour_rate_divisor_custom ?? 8)),
        ];
    }

    /**
     * يحسب مضاعف الأوفرتايم الفعلي مع مراعاة أولوية إعداد الشركة:
     * - إذا overtime_multiplier=0 في الضبط العام → معطّل لجميع الموظفين
     * - إذا overtime_enabled=0 على الموظف → معطّل لهذا الموظف
     * - وإلا يستخدم custom_overtime_multiplier للموظف أو قيمة الضبط العام
     */
    private function resolveOvertimeMultiplier(Employee $employee, Admin_panel_setting $settings): float
    {
        $settingsRate = (float)($settings->overtime_multiplier ?? 1.5);
        if ($settingsRate == 0.0) return 0.0;
        if (!($employee->overtime_enabled ?? 1)) return 0.0;
        return (float)($employee->custom_overtime_multiplier ?? $settingsRate);
    }

    /**
     * يجمع جميع معاملات الحساب من الإعدادات في مصفوفة واحدة
     */
    private function buildCalcParams(Employee $employee, Admin_panel_setting $settings, string $date): array
    {
        $rates = $this->resolveRates($employee, $settings, $date);

        $sanctionsMultiplier = (float)($settings->sanctions_value_minute_delay ?? 1);
        if ($sanctionsMultiplier == 0) $sanctionsMultiplier = 1.0;

        return array_merge($rates, [
            'overtimeMultiplier'        => $this->resolveOvertimeMultiplier($employee, $settings),
            'sanctionsMultiplier'       => $sanctionsMultiplier,
            'overtimeEnabled'           => (bool)($employee->overtime_enabled ?? 1),
            'lateDeductEnabled'         => (bool)($employee->late_deduction_enabled ?? 1),
            'graceMinutes'              => (float)($settings->after_minute_calc_delay ?? 0),
            'graceEarlyMinutes'         => (float)($settings->after_minute_calc_early ?? 0),
            'delayCalcMode'             => (int)($settings->delay_calc_mode ?? 1),
            'afterMinuteQuarterday'     => (float)($settings->after_minute_quarterday ?? 0),
            'delayTier1Minutes'         => (float)($settings->delay_tier1_minutes ?? 0),
            'delayHalfDayMinutes'       => (float)($settings->delay_halfday_minutes ?? 0),
            'delayFullDayMinutes'       => (float)($settings->delay_fullday_minutes ?? 0),
            'earlyHalfDayMinutes'       => (float)($settings->early_departure_halfday_minutes ?? 0),
            'earlyFullDayMinutes'       => (float)($settings->early_departure_fullday_minutes ?? 0),
            'earlyFullPlusHalfMinutes'  => (float)($settings->early_departure_fullplushalf_minutes ?? 0),
        ]);
    }

    /**
     * يطبق الحسابات على سجل الحضور بعد ضبط حقلي check_in/check_out
     */
    private function applyCalculations(Attendance $attendance, Employee $employee, Admin_panel_setting $settings, string $date): void
    {
        // إذا كان التاريخ قبل تاريخ تعيين الموظف → صفّر كل الخصومات ولا تحتسب
        if ($employee->emp_start_date && $date < $employee->emp_start_date) {
            $attendance->late_deduction              = 0;
            $attendance->early_departure_deduction   = 0;
            $attendance->overtime_amount             = 0;
            $attendance->late_fraction               = null;
            $attendance->early_departure_fraction    = null;
            $attendance->absence_deduction_days      = 0;
            $attendance->is_before_hire              = 1;
            $attendance->calculateDelayAndOvertime(0, 0);
            return;
        }

        $attendance->is_before_hire = 0;
        $p = $this->buildCalcParams($employee, $settings, $date);

        $attendance->calculateDelayAndOvertime($p['graceMinutes'], $p['graceEarlyMinutes']);
        $attendance->calculateAmounts(
            $p['dailyRate'],
            $p['overtimeMultiplier'],
            $p['sanctionsMultiplier'],
            $p['overtimeEnabled'],
            $p['lateDeductEnabled'],
            $p['hourDivisorType'],
            $p['hourDivisorCustom'],
            $p['delayCalcMode'],
            $p['afterMinuteQuarterday'],
            $p['delayTier1Minutes'],
            $p['delayHalfDayMinutes'],
            $p['delayFullDayMinutes'],
            $p['earlyHalfDayMinutes'],
            $p['earlyFullDayMinutes'],
            $p['earlyFullPlusHalfMinutes']
        );
    }

    // رابط العودة للـ index مع الحفاظ على فلاتر البحث المخزنة في الـ session
    private function backToIndex(): \Illuminate\Http\RedirectResponse
    {
        $qs  = session('attendance_filters_qs', '');
        $url = route('attendance.index') . ($qs ? '?' . $qs : '');
        return redirect($url);
    }

    // ─────────────────────────────────────────────
    //  INDEX
    // ─────────────────────────────────────────────
    public function index(Request $request)
    {
        // حفظ فلاتر البحث الحالية في الـ session للعودة إليها بعد التعديل
        session(['attendance_filters_qs' => $request->getQueryString() ?? '']);

        $employees = Employee::orderBy('employee_name_A')->get();
        $query     = Attendance::with(['employee', 'shift', 'shiftOverride']);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('from_date')) {
            $query->where('attendance_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('attendance_date', '<=', $request->to_date);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = in_array((int)$request->get('per_page', 20), [10, 20, 50, 100]) ? (int)$request->get('per_page', 20) : 20;
        $data = $query->orderByDesc('attendance_date')->paginate($perPage);

        $comCode = Auth::guard('admin')->user()->com_code;
        $settings = Admin_panel_setting::getByComCode($comCode);

        return view('admin.attendance.index', compact('data', 'employees', 'settings'));
    }

    // ─────────────────────────────────────────────
    //  CREATE / STORE
    // ─────────────────────────────────────────────
    public function create()
    {
        $employees = Employee::where('is_has_finger', 1)->orderBy('employee_name_A')->get();
        return view('admin.attendance.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id'     => 'required|exists:employees,id',
            'attendance_date' => 'required|date',
            'status'          => 'required|integer|between:1,6',
            'check_in_time'   => 'nullable|date_format:H:i,H:i:s',
            'check_out_time'  => 'nullable|date_format:H:i,H:i:s',
            'notes'           => 'nullable|string|max:500',
        ], [
            'employee_id.required'     => 'اختر الموظف',
            'attendance_date.required' => 'أدخل التاريخ',
            'check_in_time.date_format'  => 'وقت الحضور يجب أن يكون بتنسيق HH:MM',
            'check_out_time.date_format' => 'وقت الانصراف يجب أن يكون بتنسيق HH:MM',
        ]);

        $employee   = Employee::findOrFail($request->employee_id);
        $attendance = new Attendance();

        $attendance->employee_id     = $request->employee_id;
        $attendance->shift_id        = $employee->shifts_types_id;
        $attendance->attendance_date = $request->attendance_date;
        $attendance->check_in_time   = $request->check_in_time  ? substr($request->check_in_time,  0, 5) : null;
        $attendance->check_out_time  = $request->check_out_time ? substr($request->check_out_time, 0, 5) : null;
        $attendance->status          = $request->status;
        $attendance->notes           = $request->notes;
        $attendance->com_code        = Auth::guard('admin')->user()->com_code;
        $attendance->added_by        = Auth::guard('admin')->id();

        if ($request->status == 1 && $attendance->check_in_time && $attendance->check_out_time) {
            $settings = Admin_panel_setting::where('com_code', $attendance->com_code)->first();
            $this->applyCalculations($attendance, $employee, $settings, $request->attendance_date);
        }

        $attendance->save();

        return redirect()->route('attendance.index')
            ->with('success', 'تم تسجيل الحضور بنجاح');
    }

    // ─────────────────────────────────────────────
    //  BULK CREATE / STORE
    // ─────────────────────────────────────────────
    public function bulkCreate(Request $request)
    {
        $employees = Employee::where('is_has_finger', 1)->orderBy('employee_name_A')->get();
        $date      = $request->date ?? today()->format('Y-m-d');
        $existing  = Attendance::where('attendance_date', $date)->pluck('employee_id')->toArray();

        return view('admin.attendance.bulk_create', compact('employees', 'date', 'existing'));
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'attendance_date' => 'required|date',
            'records'         => 'required|array',
        ]);

        $admin   = Auth::guard('admin')->user();
        $date    = $request->attendance_date;
        $saved   = 0;
        $settings = null;

        foreach ($request->records as $empId => $record) {
            $employee = Employee::find($empId);
            if (!$employee) continue;

            $attendance = Attendance::firstOrNew([
                'employee_id'     => $empId,
                'attendance_date' => $date,
            ]);

            $attendance->shift_id       = $employee->shifts_types_id;
            $attendance->check_in_time  = $record['check_in']  ?? null;
            $attendance->check_out_time = $record['check_out'] ?? null;
            $attendance->status         = $record['status']    ?? 1;
            $attendance->notes          = $record['notes']     ?? null;
            $attendance->com_code       = $admin->com_code;
            $attendance->added_by       = $admin->id;

            if ($attendance->status == 1 && $attendance->check_in_time && $attendance->check_out_time) {
                $settings = $settings ?? Admin_panel_setting::where('com_code', $admin->com_code)->first();
                $this->applyCalculations($attendance, $employee, $settings, $date);
            }

            $attendance->save();
            $saved++;
        }

        return redirect()->route('attendance.index')
            ->with('success', "تم تسجيل حضور $saved موظف بنجاح ليوم $date");
    }

    // ─────────────────────────────────────────────
    //  EDIT / UPDATE
    // ─────────────────────────────────────────────
    public function edit(int $id)
    {
        $attendance   = Attendance::with(['employee', 'shift', 'shiftOverride'])->findOrFail($id);
        $employees    = Employee::orderBy('employee_name_A')->get();
        $comCode      = Auth::guard('admin')->user()->com_code;
        $shifts_types = Shifts_type::where('com_code', $comCode)->orderBy('type')->get();
        $settings     = Admin_panel_setting::getByComCode($comCode);

        // ── رابط العودة مع الفلاتر المحفوظة ──────────────────────
        $qs      = session('attendance_filters_qs', '');
        $backUrl = route('attendance.index') . ($qs ? '?' . $qs : '');

        // ── بصمات الموظف لنفس اليوم واليوم التالي ────────────────
        $employee   = $attendance->employee;
        $date       = $attendance->attendance_date->format('Y-m-d');
        $nextDate   = $attendance->attendance_date->copy()->addDay()->format('Y-m-d');
        $fingerId   = (int)($employee->finger_id ?? 0);
        $fingerLogs = collect();

        if ($fingerId) {
            $deviceIds = [];
            if ($employee->branches_id) {
                $deviceIds = FingerprintDevice::where('com_code', $comCode)
                    ->get()
                    ->filter(fn($d) =>
                        $d->branches_id === $employee->branches_id
                        || in_array($employee->branches_id, $d->extra_branch_ids ?? [])
                    )
                    ->pluck('id')
                    ->toArray();
            }

            $logsQuery = FingerprintLog::with('device')
                ->where('com_code', $comCode)
                ->where('finger_id', $fingerId)
                ->whereBetween('punch_time', [$date . ' 00:00:00', $nextDate . ' 23:59:59'])
                ->orderBy('punch_time');

            if (!empty($deviceIds)) {
                $logsQuery->whereIn('device_id', $deviceIds);
            }

            $fingerLogs = $logsQuery->get();
        }

        return view('admin.attendance.edit', compact(
            'attendance', 'employees', 'shifts_types', 'settings',
            'backUrl', 'fingerLogs', 'date', 'nextDate'
        ));
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'check_in_time'  => 'nullable|date_format:H:i,H:i:s',
            'check_out_time' => 'nullable|date_format:H:i,H:i:s',
            'status'         => 'required|integer|between:1,6',
            'permission_minutes'           => 'nullable|integer|min:0',
            'permission_early_minutes'     => 'nullable|integer|min:0',
            'is_weekly_off_worked'         => 'nullable|integer|between:0,1',
            'leave_compensation_amount'    => 'nullable|numeric|min:0',
            'absence_deduction_days'       => 'nullable|numeric|min:0|max:30',
        ], [
            'check_in_time.date_format'       => 'وقت الحضور يجب أن يكون بتنسيق HH:MM',
            'check_out_time.date_format'      => 'وقت الانصراف يجب أن يكون بتنسيق HH:MM',
            'absence_deduction_days.numeric'  => 'عدد أيام الخصم يجب أن يكون رقماً',
            'absence_deduction_days.min'      => 'عدد أيام الخصم لا يمكن أن يكون سالباً',
            'absence_deduction_days.max'      => 'عدد أيام الخصم لا يمكن أن يتجاوز 30',
        ]);

        $attendance = Attendance::findOrFail($id);
        $employee   = $attendance->employee;

        $attendance->check_in_time  = $request->check_in_time  ? substr($request->check_in_time,  0, 5) : null;
        $attendance->check_out_time = $request->check_out_time ? substr($request->check_out_time, 0, 5) : null;
        $attendance->status         = $request->status;
        $attendance->notes          = $request->notes;
        $attendance->is_manual_lock = $request->boolean('is_manual_lock') ? 1 : 0;
        $attendance->updated_by     = Auth::guard('admin')->id();

        $attendance->permission_minutes       = max(0, (int)($request->permission_minutes       ?? 0));
        $attendance->permission_early_minutes = max(0, (int)($request->permission_early_minutes ?? 0));

        $settings = Admin_panel_setting::where('com_code', $attendance->com_code)->first();
        $date     = $attendance->attendance_date->format('Y-m-d');

        if ($attendance->status == 6) {
            // ─── إجازة أسبوعية بحتة (لم يبصم) ───
            $attendance->is_weekly_off_worked      = 0;
            $attendance->leave_compensation_amount = 0;
            $attendance->weekly_off_overtime       = null;

        } elseif ($attendance->status == 1 && $attendance->check_in_time && $attendance->check_out_time) {
            // ─── إذا اكتملت أوقات الحضور والانصراف: إلغاء علامة البصمة المفقودة ───
            $attendance->missing_punch            = null;
            $attendance->missing_punch_resolution = null;
            $attendance->missing_punch_hours      = null;

            // ─── حضور: احتساب التأخير/الأوفرتايم ───
            $this->applyCalculations($attendance, $employee, $settings, $date);

            // ─── يوم راحة عمل فيه: احتساب بدل الإجازة فقط إذا كان محدداً ───
            $attendance->is_weekly_off_worked = (int)($request->is_weekly_off_worked ?? 0);
            if ($attendance->is_weekly_off_worked) {
                $this->handleWeeklyOffWorked($attendance, $employee, $settings);
            } else {
                $attendance->leave_compensation_amount = 0;
                $attendance->weekly_off_overtime       = null;
            }

        } else {
            // ─── غياب / إجازة / مأمورية: صفر الحسابات ───
            $attendance->late_minutes              = 0;
            $attendance->early_departure_minutes   = 0;
            $attendance->overtime_hours            = 0;
            $attendance->overtime_amount           = 0;
            $attendance->late_deduction            = 0;
            $attendance->early_departure_deduction = 0;
            $attendance->late_fraction             = null;
            $attendance->early_departure_fraction  = null;
            $attendance->is_weekly_off_worked      = 0;
            $attendance->leave_compensation_amount = 0;
            $attendance->weekly_off_overtime       = null;

            if ($attendance->status == 2) {
                // ─── غياب: حفظ أيام الخصم (مخصص أو الضبط العام) ───
                if ($request->filled('absence_deduction_days')) {
                    $attendance->absence_deduction_days = (float)$request->absence_deduction_days;
                } elseif ($attendance->absence_deduction_days === null) {
                    // تعيين القيمة الافتراضية من الضبط العام إن لم تكن محددة مسبقاً
                    $attendance->absence_deduction_days = (float)($settings->sanctions_value_first_abcence ?? 1);
                }
            } else {
                $attendance->absence_deduction_days = null;
            }
        }

        $attendance->save();

        return $this->backToIndex()->with('success', 'تم تحديث سجل الحضور بنجاح');
    }

    /**
     * منطق يوم الراحة الأسبوعي المعمول فيه:
     * - يُصفَّر التأخير والانصراف المبكر دائماً
     * - يُحسب بدل الإجازة إذا كان is_weekly_off_worked=1
     * - يمكن إلغاء البدل يدوياً من الـ edit view
     */
    private function handleWeeklyOffWorked(Attendance $attendance, Employee $employee, ?Admin_panel_setting $settings): void
    {
        // صفّر الخصومات
        $attendance->late_minutes              = 0;
        $attendance->early_departure_minutes   = 0;
        $attendance->late_deduction            = 0;
        $attendance->early_departure_deduction = 0;
        $attendance->late_fraction             = null;
        $attendance->early_departure_fraction  = null;
        $attendance->overtime_hours            = 0;
        $attendance->overtime_amount           = 0;

        if ($attendance->is_weekly_off_worked && $settings) {
            $rates  = $this->resolveRates($employee, $settings, $attendance->attendance_date->format('Y-m-d'));
            $amount = LeaveCompensationController::calculate(
                $attendance->com_code,
                $employee,
                $rates['dailyRate']
            );
            $attendance->leave_compensation_amount = $amount;
        } else {
            $attendance->leave_compensation_amount = 0;
        }
    }

    // يُعيّن سجل الحضور كإجازة أسبوعية مع تصفير جميع الحسابات
    private function markAttendanceAsWeeklyOff(Attendance $att, int $updatedBy): void
    {
        $att->status                    = 6;
        $att->check_in_time             = null;
        $att->check_out_time            = null;
        $att->late_minutes              = 0;
        $att->overtime_hours            = 0;
        $att->overtime_amount           = 0;
        $att->late_deduction            = 0;
        $att->early_departure_minutes   = 0;
        $att->early_departure_deduction = 0;
        $att->late_fraction             = null;
        $att->early_departure_fraction  = null;
        $att->is_weekly_off_worked      = 0;
        $att->leave_compensation_amount = 0;
        $att->missing_punch             = null;
        $att->missing_punch_resolution  = null;
        $att->updated_by                = $updatedBy;
        $att->save();
    }

    // ─────────────────────────────────────────────
    //  DELETE
    // ─────────────────────────────────────────────
    public function delete(int $id)
    {
        Attendance::findOrFail($id)->delete();
        return redirect()->route('attendance.index')
            ->with('success', 'تم حذف السجل بنجاح');
    }

    public function bulkDelete(Request $request)
    {
        $query = Attendance::query();

        if ($request->filled('employee_id')) $query->where('employee_id', $request->employee_id);
        if ($request->filled('from_date'))   $query->where('attendance_date', '>=', $request->from_date);
        if ($request->filled('to_date'))     $query->where('attendance_date', '<=', $request->to_date);
        if ($request->filled('status'))      $query->where('status', $request->status);

        $count = $query->count();
        $query->delete();

        return redirect()->route('attendance.index')
            ->with('success', "تم حذف {$count} سجل بنجاح");
    }

    // ─────────────────────────────────────────────
    //  تفريغ البصمة — تحويل سجلات الحضور المفلترة إلى غياب
    // ─────────────────────────────────────────────
    public function voidFingerprint(Request $request)
    {
        $comCode = Auth::guard('admin')->user()->com_code;

        $query = Attendance::where('com_code', $comCode);
        if ($request->filled('employee_id')) $query->where('employee_id', $request->employee_id);
        if ($request->filled('from_date'))   $query->where('attendance_date', '>=', $request->from_date);
        if ($request->filled('to_date'))     $query->where('attendance_date', '<=', $request->to_date);
        if ($request->filled('status'))      $query->where('status', $request->status);

        $records = $query->with('employee')->get();
        if ($records->isEmpty()) {
            return redirect()->back()->with('error', 'لا توجد سجلات تطابق الفلتر المحدد.');
        }

        $updatedBy = Auth::guard('admin')->id();
        $voided    = 0;
        $locked    = 0;

        foreach ($records as $att) {
            // سجل مثبَّت يدوياً — لا يُعدَّل بتفريغ البصمة
            if ($att->is_manual_lock) { $locked++; continue; }

            $employee    = $att->employee;
            $dayOfWeek   = $att->attendance_date->dayOfWeek;
            $isWeeklyOff = $employee
                           && $employee->weekly_off_day !== null
                           && (int)$employee->weekly_off_day === $dayOfWeek;

            $att->update([
                'status'                    => $isWeeklyOff ? 6 : 2,
                'check_in_time'             => null,
                'check_out_time'            => null,
                'late_minutes'              => 0,
                'overtime_hours'            => 0,
                'overtime_amount'           => 0,
                'late_deduction'            => 0,
                'early_departure_minutes'   => 0,
                'early_departure_deduction' => 0,
                'early_departure_fraction'  => null,
                'missing_punch'             => null,
                'missing_punch_resolution'  => null,
                'missing_punch_hours'       => null,
                'permission_early_minutes'  => 0,
                'notes'                     => $isWeeklyOff ? 'إجازة أسبوعية - تفريغ بصمة' : 'تم تفريغ البصمة يدوياً',
                'updated_by'                => $updatedBy,
            ]);

            // إعادة البصمات المقابلة إلى غير معالَجة
            if ($att->employee?->finger_id) {
                FingerprintLog::where('com_code', $comCode)
                    ->where('finger_id', $att->employee->finger_id)
                    ->whereDate('punch_time', $att->attendance_date)
                    ->update(['is_processed' => 0]);
            }

            $voided++;
        }

        $msg = "✅ تم تفريغ بصمة {$voided} سجل حضور وتحويلها إلى غياب.";
        if ($locked) $msg .= " | 🔒 تم تجاهل {$locked} سجل مثبَّت.";

        return redirect()->route('attendance.index', $request->only(['employee_id','from_date','to_date','status','per_page']))
            ->with('success', $msg);
    }

    // ─────────────────────────────────────────────
    //  إعادة معالجة البصمة الجماعية حسب الفلتر
    // ─────────────────────────────────────────────
    public function bulkReprocessFingerprint(Request $request)
    {
        $comCode  = Auth::guard('admin')->user()->com_code;
        $settings = Admin_panel_setting::getByComCode($comCode);

        $query = Attendance::where('com_code', $comCode)->with(['employee', 'shift', 'shiftOverride']);
        if ($request->filled('employee_id')) $query->where('employee_id', $request->employee_id);
        if ($request->filled('from_date'))   $query->where('attendance_date', '>=', $request->from_date);
        if ($request->filled('to_date'))     $query->where('attendance_date', '<=', $request->to_date);
        if ($request->filled('status'))      $query->where('status', $request->status);

        $records = $query->orderBy('attendance_date')->get();

        if ($records->isEmpty()) {
            return redirect()->back()->with('error', 'لا توجد سجلات تطابق الفلتر المحدد.');
        }

        $service   = new \App\Services\FingerprintService();
        $updatedBy = Auth::guard('admin')->id();
        $success   = 0;
        $failed    = 0;
        $noShift   = 0;
        $noFinger  = 0;
        $weeklyOff = 0;
        $locked    = 0;

        foreach ($records as $att) {
            // سجل مثبَّت يدوياً — لا تُعيد معالجته
            if ($att->is_manual_lock) { $locked++; continue; }

            $employee = $att->employee;
            if (!$employee) { $failed++; continue; }

            $date        = $att->attendance_date->format('Y-m-d');
            $dayOfWeek   = $att->attendance_date->dayOfWeek; // Carbon: 0=الأحد...6=السبت
            $isWeeklyOff = $employee->weekly_off_day !== null
                           && (int)$employee->weekly_off_day === $dayOfWeek;

            // موظف بدون رقم بصمة — لا يمكن إعادة المعالجة، لكن نصحح يوم الراحة
            if (!$employee->finger_id) {
                if ($isWeeklyOff && $att->status != 6) {
                    $this->markAttendanceAsWeeklyOff($att, $updatedBy);
                    $weeklyOff++;
                } else {
                    $noFinger++;
                }
                continue;
            }

            $shift = $att->effective_shift;

            // بدون شيفت — لا يمكن إعادة المعالجة، لكن نصحح يوم الراحة
            if (!$shift) {
                if ($isWeeklyOff && $att->status != 6) {
                    $this->markAttendanceAsWeeklyOff($att, $updatedBy);
                    $weeklyOff++;
                } else {
                    $noShift++;
                }
                continue;
            }

            $result = $service->reprocessAttendanceFromLogs($att, $employee, $shift);

            if ($result['success']) {
                // وُجدت بصمة — الموظف حضر
                if ($att->check_in_time && $att->check_out_time) {
                    $this->applyCalculations($att, $employee, $settings, $date);
                }
                // إذا كان يوم الراحة وجاء في بصمة → إجازة أسبوعية مشتغل فيها
                if ($isWeeklyOff) {
                    $att->is_weekly_off_worked = 1;
                    $this->handleWeeklyOffWorked($att, $employee, $settings);
                }
                $att->updated_by = $updatedBy;
                $att->save();
                $success++;
            } else {
                // لا توجد بصمة — تحقق إذا كان يوم الراحة الأسبوعية
                if ($isWeeklyOff) {
                    $this->markAttendanceAsWeeklyOff($att, $updatedBy);
                    $weeklyOff++;
                } else {
                    $failed++;
                }
            }
        }

        $msg = "✅ تم إعادة معالجة {$success} سجل بنجاح.";
        if ($weeklyOff) $msg .= " | 📅 تحويل إجازة أسبوعية: {$weeklyOff}.";
        if ($failed)    $msg .= " | ❌ فشل (لا بصمة): {$failed}.";
        if ($noShift)   $msg .= " | ⚠️ بدون شيفت: {$noShift}.";
        if ($noFinger)  $msg .= " | ⚠️ بدون رقم بصمة: {$noFinger}.";
        if ($locked)    $msg .= " | 🔒 تجاهل مثبَّت: {$locked}.";

        return redirect()->route('attendance.index', $request->only(['employee_id','from_date','to_date','status','per_page']))
            ->with('success', $msg);
    }

    // ─────────────────────────────────────────────
    //  توليد إجازات أسبوعية تلقائياً
    // ─────────────────────────────────────────────

    public function generateWeeklyLeavesForm()
    {
        return view('admin.attendance.generate_weekly_leaves');
    }

    /**
     * توليد سجلات إجازة أسبوعية لجميع الموظفين في نطاق تاريخ محدد
     * يتخطى السجلات الموجودة مسبقاً — يُنشئ فقط إذا غاب السجل
     */
    public function generateWeeklyLeaves(Request $request)
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date'   => 'required|date|after_or_equal:from_date',
        ], [
            'from_date.required' => 'حدد تاريخ البداية',
            'to_date.required'   => 'حدد تاريخ النهاية',
            'to_date.after_or_equal' => 'تاريخ النهاية يجب أن يكون بعد البداية',
        ]);

        $admin    = Auth::guard('admin')->user();
        $comCode  = $admin->com_code;
        $fromDate = Carbon::parse($request->from_date);
        $toDate   = Carbon::parse($request->to_date);

        // جلب الموظفين الذين لديهم يوم إجازة أسبوعية محدد
        $employees = Employee::where('com_code', $comCode)
            ->whereNotNull('weekly_off_day')
            ->get();

        $created  = 0;
        $skipped  = 0;

        $current = $fromDate->copy();
        while ($current->lte($toDate)) {
            $dateStr   = $current->format('Y-m-d');
            $dayOfWeek = $current->dayOfWeek; // 0=Sun,...,6=Sat

            foreach ($employees as $emp) {
                if ((int)$emp->weekly_off_day !== $dayOfWeek) continue;

                $exists = Attendance::where('employee_id', $emp->id)
                    ->where('attendance_date', $dateStr)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                Attendance::create([
                    'employee_id'     => $emp->id,
                    'shift_id'        => $emp->shifts_types_id,
                    'attendance_date' => $dateStr,
                    'status'          => 6, // إجازة أسبوعية
                    'late_minutes'    => 0,
                    'overtime_hours'  => 0,
                    'overtime_amount' => 0,
                    'late_deduction'  => 0,
                    'com_code'        => $comCode,
                    'added_by'        => $admin->id,
                ]);
                $created++;
            }

            $current->addDay();
        }

        return redirect()->route('attendance.index')
            ->with('success', "تم توليد {$created} إجازة أسبوعية. (تجاهل {$skipped} سجل موجود مسبقاً)");
    }

    // ─────────────────────────────────────────────
    //  استيراد الحضور من Excel
    // ─────────────────────────────────────────────

    public function excelImportForm()
    {
        return view('admin.attendance.excel_import');
    }

    public function excelImport(Request $request)
    {
        $request->validate([
            'excel_file'      => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'attendance_date' => 'required|date',
        ], [
            'excel_file.required'      => 'يجب اختيار ملف Excel',
            'excel_file.mimes'         => 'يُقبل فقط xlsx, xls, csv',
            'attendance_date.required' => 'يجب تحديد تاريخ الحضور',
        ]);

        try {
            $admin       = Auth::guard('admin')->user();
            $comCode     = $admin->com_code;
            $date        = $request->attendance_date;
            $hasDateCol  = (bool) $request->input('has_date_col', 0);
            $markAbsent  = (bool) $request->input('mark_absent', 0);

            $spreadsheet = IOFactory::load($request->file('excel_file')->getPathname());
            $rows        = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
            array_shift($rows);

            $fingerData = [];
            foreach ($rows as $row) {
                $fingerId = isset($row[0]) ? trim((string)$row[0]) : null;
                if (empty($fingerId)) continue;

                if ($hasDateCol) {
                    $rowDate  = isset($row[1]) ? $this->parseExcelDate($row[1]) : $date;
                    $checkIn  = isset($row[2]) ? $this->parseExcelTime($row[2]) : null;
                    $checkOut = isset($row[3]) ? $this->parseExcelTime($row[3]) : null;
                } else {
                    $rowDate  = $date;
                    $checkIn  = isset($row[1]) ? $this->parseExcelTime($row[1]) : null;
                    $checkOut = isset($row[2]) ? $this->parseExcelTime($row[2]) : null;
                }

                if (!$rowDate) continue;

                if (!isset($fingerData[$fingerId][$rowDate])) {
                    $fingerData[$fingerId][$rowDate] = ['times' => []];
                }
                if ($checkIn)  $fingerData[$fingerId][$rowDate]['times'][] = $checkIn;
                if ($checkOut) $fingerData[$fingerId][$rowDate]['times'][] = $checkOut;
                if ($checkIn)  $fingerData[$fingerId][$rowDate]['check_in']  = $checkIn;
                if ($checkOut) $fingerData[$fingerId][$rowDate]['check_out'] = $checkOut;
            }

            $saved    = 0;
            $skipped  = 0;
            $notFound = [];

            $employees = Employee::where('com_code', $comCode)
                ->whereNotNull('finger_id')
                ->get()
                ->keyBy('finger_id');

            $settings = null;

            foreach ($fingerData as $fingerId => $dates) {
                $employee = $employees->get($fingerId);
                if (!$employee) {
                    $notFound[] = $fingerId;
                    continue;
                }

                foreach ($dates as $rowDate => $info) {
                    $existing = Attendance::where('employee_id', $employee->id)
                        ->where('attendance_date', $rowDate)->first();

                    $times    = $info['times'] ?? [];
                    $checkIn  = $info['check_in']  ?? (count($times) ? min($times) : null);
                    $checkOut = $info['check_out'] ?? (count($times) > 1 ? max($times) : null);

                    // إذا كان السجل الحالي إجازة أسبوعية وجاء بصمة → تحويله لحضور + بدل إجازة
                    if ($existing) {
                        if ($existing->status == 6 && $checkIn) {
                            $settings = $settings ?? Admin_panel_setting::where('com_code', $comCode)->first();
                            $existing->status             = 1;
                            $existing->check_in_time      = $checkIn;
                            $existing->check_out_time     = $checkOut;
                            $existing->is_weekly_off_worked = 1;
                            $existing->weekly_off_overtime  = null;

                            if ($checkIn && $checkOut) {
                                $this->applyCalculations($existing, $employee, $settings, $rowDate);
                            }
                            $this->handleWeeklyOffWorked($existing, $employee, $settings);
                            $existing->save();
                        }
                        $skipped++;
                        continue;
                    }

                    // سجل جديد
                    $isWeeklyOff = false;
                    $dayOfWeek   = Carbon::parse($rowDate)->dayOfWeek;
                    if ($employee->weekly_off_day !== null && (int)$employee->weekly_off_day === $dayOfWeek && $checkIn) {
                        $isWeeklyOff = true;
                    }

                    $att = new Attendance();
                    $att->employee_id           = $employee->id;
                    $att->shift_id              = $employee->shifts_types_id;
                    $att->attendance_date        = $rowDate;
                    $att->check_in_time          = $checkIn;
                    $att->check_out_time         = $checkOut;
                    $att->status                 = 1;
                    $att->is_weekly_off_worked   = $isWeeklyOff ? 1 : 0;
                    $att->com_code               = $comCode;
                    $att->added_by               = $admin->id;

                    if ($checkIn && $checkOut) {
                        $settings = $settings ?? Admin_panel_setting::where('com_code', $comCode)->first();
                        $this->applyCalculations($att, $employee, $settings, $rowDate);
                    }
                    if ($isWeeklyOff) {
                        $settings = $settings ?? Admin_panel_setting::where('com_code', $comCode)->first();
                        $this->handleWeeklyOffWorked($att, $employee, $settings);
                    }
                    $att->save();
                    $saved++;
                }
            }

            // تسجيل غياب للغائبين (مع تجاهل موظفي يوم الراحة)
            if ($markAbsent) {
                $presentIds = $employees->filter(fn($e) => isset($fingerData[$e->finger_id]))->pluck('id');
                $allEmpIds  = Employee::where('com_code', $comCode)->where('is_has_finger', 1)->pluck('id');
                $parsedDate = Carbon::parse($date);
                $dayOfWeek  = $parsedDate->dayOfWeek;

                foreach ($allEmpIds as $empId) {
                    if ($presentIds->contains($empId)) continue;
                    $exists = Attendance::where('employee_id', $empId)->where('attendance_date', $date)->exists();
                    if ($exists) continue;

                    $emp = Employee::find($empId);
                    // إذا كان هذا يومه الأسبوعي → إجازة أسبوعية لا غياب
                    $absentStatus = ($emp && $emp->weekly_off_day !== null && (int)$emp->weekly_off_day === $dayOfWeek)
                        ? 6 : 2;

                    Attendance::create([
                        'employee_id'     => $empId,
                        'shift_id'        => $emp->shifts_types_id ?? null,
                        'attendance_date' => $date,
                        'status'          => $absentStatus,
                        'com_code'        => $comCode,
                        'added_by'        => $admin->id,
                    ]);
                }
            }

            $msg = "تم استيراد <strong>$saved</strong> سجل بنجاح.";
            if ($skipped)          $msg .= " تم تجاهل <strong>$skipped</strong> سجل موجود مسبقاً.";
            if (count($notFound))  $msg .= " Finger IDs غير موجودة: <strong>" . implode(', ', $notFound) . "</strong>.";

            return redirect()->route('attendance.excel_import_form')->with('success', $msg);

        } catch (\Exception $ex) {
            Log::error('AttendanceController@excelImport: ' . $ex->getMessage());
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء معالجة الملف. تأكد من صحة التنسيق.')
                ->withInput();
        }
    }

    public function excelTemplate()
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="attendance_template.csv"',
        ];

        $rows = [
            ['Finger ID', 'وقت الحضور (HH:MM)', 'وقت الانصراف (HH:MM)'],
            ['1', '08:00', '17:00'],
            ['2', '08:05', '17:10'],
        ];

        $output = "\xEF\xBB\xBF";
        foreach ($rows as $row) {
            $output .= implode(',', array_map(fn($v) => '"' . str_replace('"', '""', $v) . '"', $row)) . "\r\n";
        }

        return response($output, 200, $headers);
    }

    // ─────────────────────────────────────────────
    //  ملخص الموظف
    // ─────────────────────────────────────────────
    public function employeeSummary(Request $request, int $employeeId)
    {
        $employee = Employee::findOrFail($employeeId);
        $month    = $request->month ?? now()->month;
        $year     = $request->year  ?? now()->year;

        $records = Attendance::where('employee_id', $employeeId)
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->orderBy('attendance_date')
            ->get();

        $summary = [
            'present_days'           => $records->where('status', 1)->count(),
            'absent_days'            => $records->where('status', 2)->count(),
            'leave_days'             => $records->where('status', 3)->count(),
            'weekly_off_days'        => $records->where('status', 6)->count(),
            'total_late_min'         => $records->sum('late_minutes'),
            'total_early_min'        => $records->sum('early_departure_minutes'),
            'total_overtime'         => $records->sum('overtime_hours'),
            'total_overtime_amount'  => $records->sum('overtime_amount'),
            'total_late_deduction'   => $records->sum('late_deduction'),
            'total_early_deduction'  => $records->sum('early_departure_deduction'),
        ];

        return view('admin.attendance.employee_summary', compact('employee', 'records', 'summary', 'month', 'year'));
    }

    // ─────────────────────────────────────────────
    //  معالجة البصمة الناقصة
    // ─────────────────────────────────────────────
    public function resolveMissingPunch(Request $request, int $id)
    {
        $request->validate([
            'missing_punch_resolution' => 'required|integer|between:1,5',
            'missing_punch_hours'      => 'required_if:missing_punch_resolution,5|nullable|numeric|min:0.25|max:24',
        ]);

        $attendance = Attendance::findOrFail($id);

        if (!$attendance->missing_punch) {
            return redirect()->back()->with('error', 'هذا السجل لا يحتوي على بصمة ناقصة');
        }

        $comCode  = Auth::guard('admin')->user()->com_code;
        $settings = Admin_panel_setting::getByComCode($comCode);
        $employee = $attendance->employee;

        $resolution = (int)$request->missing_punch_resolution;
        $hours      = $resolution === 5 ? (float)$request->missing_punch_hours : null;

        $rates     = $this->resolveRates($employee, $settings, $attendance->attendance_date->format('Y-m-d'));
        $dailyRate = $rates['dailyRate'];
        $hourlyRate = $dailyRate / 8;

        $deduction = match ($resolution) {
            1 => round($dailyRate * 0.25, 2),
            2 => round($dailyRate * 0.5, 2),
            3 => round($dailyRate, 2),
            4 => 0.0,
            5 => round($hourlyRate * $hours, 2),
            default => 0.0,
        };

        $attendance->missing_punch_resolution = $resolution;
        $attendance->missing_punch_hours      = $hours;
        $attendance->late_deduction           = $attendance->late_deduction + $deduction;
        $attendance->updated_by               = Auth::guard('admin')->id();
        $attendance->save();

        return redirect()->back()->with('success', 'تم حل البصمة الناقصة: ' . $attendance->missing_punch_resolution_label);
    }

    // ─────────────────────────────────────────────
    //  تحديث الشيفت المخصص
    // ─────────────────────────────────────────────
    public function updateShift(Request $request, int $id)
    {
        $request->validate([
            'shift_override_id' => 'nullable|exists:shifts_types,id',
        ]);

        $attendance = Attendance::findOrFail($id);
        $comCode    = Auth::guard('admin')->user()->com_code;
        $settings   = Admin_panel_setting::getByComCode($comCode);
        $employee   = $attendance->employee;

        $attendance->shift_override_id = $request->shift_override_id ?: null;
        $attendance->updated_by        = Auth::guard('admin')->id();

        $date = $attendance->attendance_date->format('Y-m-d');

        if ($attendance->check_in_time && $attendance->check_out_time) {
            // كلا الوقتين موجودان → تحويل لحضور وإلغاء البصمة الناقصة
            $attendance->status                   = 1;
            $attendance->missing_punch            = null;
            $attendance->missing_punch_resolution = null;
            $attendance->missing_punch_hours      = null;
            $this->applyCalculations($attendance, $employee, $settings, $date);
        } elseif ($attendance->check_in_time && $attendance->status == 1) {
            // حضور فقط بدون انصراف → احتسب ما يمكن
            $this->applyCalculations($attendance, $employee, $settings, $date);
        }

        $attendance->save();

        return redirect()->back()->with('success', 'تم تحديث الشيفت وإعادة الاحتساب بإعدادات الضبط العام الحالية');
    }

    // ─────────────────────────────────────────────
    //  إعادة معالجة البصمة بعد تغيير الشيفت
    // ─────────────────────────────────────────────
    public function reprocessFingerprint(Request $request, int $id)
    {
        $request->validate([
            'shift_override_id' => 'nullable|exists:shifts_types,id',
        ]);

        $attendance = Attendance::findOrFail($id);
        $employee   = $attendance->employee;
        $comCode    = Auth::guard('admin')->user()->com_code;
        $settings   = Admin_panel_setting::getByComCode($comCode);

        // 1. حفظ الشيفت المخصص أولاً
        $attendance->shift_override_id = $request->shift_override_id ?: null;
        $attendance->updated_by        = Auth::guard('admin')->id();
        $attendance->save();

        $shiftToUse = $attendance->fresh()->effective_shift;
        if (!$shiftToUse) {
            return redirect()->back()->with('error', 'لا يوجد شيفت محدد للموظف');
        }

        // 2. إعادة معالجة البصمة باستخدام الشيفت الجديد
        $service     = new \App\Services\FingerprintService();
        $result      = $service->reprocessAttendanceFromLogs($attendance, $employee, $shiftToUse);
        $date        = $attendance->attendance_date->format('Y-m-d');
        $dayOfWeek   = $attendance->attendance_date->dayOfWeek;
        $isWeeklyOff = $employee->weekly_off_day !== null
                       && (int)$employee->weekly_off_day === $dayOfWeek;

        if (!$result['success']) {
            // لا توجد بصمة — إذا كان يوم الراحة الأسبوعية حوّله بدلاً من إعادة خطأ
            if ($isWeeklyOff) {
                $this->markAttendanceAsWeeklyOff($attendance, Auth::guard('admin')->id());
                return redirect()->back()->with('success', 'لا توجد بصمة — تم تحويل السجل إلى إجازة أسبوعية');
            }
            return redirect()->back()->with('error', 'إعادة المعالجة: ' . $result['error']);
        }

        // 3. إعادة احتساب التأخير/الأوفرتايم بعد تحديث أوقات البصمة
        if ($attendance->check_in_time && $attendance->check_out_time) {
            $this->applyCalculations($attendance, $employee, $settings, $date);
        }

        // إذا كان يوم راحة وجاء في بصمة → إجازة أسبوعية مشتغل فيها
        if ($isWeeklyOff) {
            $attendance->is_weekly_off_worked = 1;
            $this->handleWeeklyOffWorked($attendance, $employee, $settings);
        }

        $attendance->save();

        $checkOut = $result['checkOut'] ?? 'ناقص';
        $msg = "تم — حضور: {$result['checkIn']} | انصراف: {$checkOut} | {$result['punches']} بصمة";
        if ($isWeeklyOff) $msg .= ' | 📅 يوم راحة مشتغل فيه';
        return redirect()->back()->with('success', $msg);
    }

    // ─── مساعدات تحويل Excel ───

    private function parseExcelTime($value): ?string
    {
        if (empty($value) && $value !== '0') return null;

        if (is_numeric($value) && $value > 0 && $value < 1) {
            $seconds = (int) round($value * 86400);
            return sprintf('%02d:%02d', intdiv($seconds, 3600), intdiv($seconds % 3600, 60));
        }

        if (preg_match('/^(\d{1,2}):(\d{2})/', (string)$value, $m)) {
            return sprintf('%02d:%02d', $m[1], $m[2]);
        }

        return null;
    }

    private function parseExcelDate($value): ?string
    {
        if (empty($value)) return null;

        if (is_numeric($value) && $value > 1000) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Exception $e) {}
        }

        try {
            return Carbon::parse((string)$value)->format('Y-m-d');
        } catch (\Exception $e) {}

        return null;
    }
}
