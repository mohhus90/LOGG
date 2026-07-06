<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Admin_panel_setting;
use App\Models\Shifts_type;
use App\Models\Department;
use App\Models\Branche;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Http\Controllers\Admin\LeaveCompensationController;
use App\Models\FingerprintLog;
use App\Services\FingerprintService;

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

    // ─────────────────────────────────────────────
    //  INDEX
    // ─────────────────────────────────────────────
    public function index(Request $request)
    {
        $employees = Employee::orderBy('employee_name_A')->get();
        $query     = Attendance::with(['employee', 'shift']);

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
        $data     = $query->orderByDesc('attendance_date')->paginate($perPage);
        $comCode  = Auth::guard('admin')->user()->com_code;
        $settings = Admin_panel_setting::where('com_code', $comCode)->first();

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
    //  RANGE BATCH CREATE / STORE
    //  (تسجيل نفس الحالة/الأوقات لعدة موظفين عبر نطاق تاريخ)
    // ─────────────────────────────────────────────
    public function rangeBatchCreate()
    {
        $comCode = Auth::guard('admin')->user()->com_code;

        $employees   = Employee::where('com_code', $comCode)->orderBy('employee_name_A')->get();
        $departments = Department::where('com_code', $comCode)->orderBy('dep_name')->get(['id', 'dep_name']);
        $branches    = Branche::where('com_code', $comCode)->orderBy('branch_name')->get(['id', 'branch_name']);
        $clients     = Client::where('com_code', $comCode)->where('active', 1)->orderBy('client_name')->get(['id', 'client_name']);

        return view('admin.attendance.range_batch', compact('employees', 'departments', 'branches', 'clients'));
    }

    public function rangeBatchStore(Request $request)
    {
        $request->validate([
            'from_date'    => 'required|date',
            'to_date'      => 'required|date|after_or_equal:from_date',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
            'status'          => 'required|integer|between:1,6',
            'check_in_time'   => 'nullable|date_format:H:i,H:i:s',
            'check_out_time'  => 'nullable|date_format:H:i,H:i:s',
            'notes'           => 'nullable|string|max:500',
        ], [
            'from_date.required'    => 'حدد تاريخ البداية',
            'to_date.required'      => 'حدد تاريخ النهاية',
            'to_date.after_or_equal'=> 'تاريخ النهاية يجب أن يكون بعد البداية',
            'employee_ids.required' => 'اختر موظف واحد على الأقل',
            'check_in_time.date_format'  => 'وقت الحضور يجب أن يكون بتنسيق HH:MM',
            'check_out_time.date_format' => 'وقت الانصراف يجب أن يكون بتنسيق HH:MM',
        ]);

        $admin           = Auth::guard('admin')->user();
        $comCode         = $admin->com_code;
        $skipWeeklyOff   = $request->boolean('skip_weekly_off');
        $overwrite       = $request->boolean('overwrite_existing');
        $checkIn         = $request->check_in_time  ? substr($request->check_in_time,  0, 5) : null;
        $checkOut        = $request->check_out_time ? substr($request->check_out_time, 0, 5) : null;
        $status          = (int)$request->status;

        $employees = Employee::whereIn('id', $request->employee_ids)->get()->keyBy('id');
        $settings  = Admin_panel_setting::where('com_code', $comCode)->first();

        $fromDate = Carbon::parse($request->from_date);
        $toDate   = Carbon::parse($request->to_date);

        $saved           = 0;
        $skippedWeekly   = 0;
        $skippedExisting = 0;

        $current = $fromDate->copy();
        while ($current->lte($toDate)) {
            $dateStr   = $current->format('Y-m-d');
            $dayOfWeek = $current->dayOfWeek;

            foreach ($employees as $employee) {
                if ($skipWeeklyOff && $employee->weekly_off_day !== null && (int)$employee->weekly_off_day === $dayOfWeek) {
                    $skippedWeekly++;
                    continue;
                }

                $attendance = Attendance::where('employee_id', $employee->id)
                    ->where('attendance_date', $dateStr)
                    ->first();

                if ($attendance && !$overwrite) {
                    $skippedExisting++;
                    continue;
                }

                $attendance = $attendance ?? new Attendance();
                $attendance->employee_id     = $employee->id;
                $attendance->shift_id        = $employee->shifts_types_id;
                $attendance->attendance_date = $dateStr;
                $attendance->check_in_time   = $status == 1 ? $checkIn  : null;
                $attendance->check_out_time  = $status == 1 ? $checkOut : null;
                $attendance->status          = $status;
                $attendance->notes           = $request->notes;
                $attendance->com_code        = $comCode;
                $attendance->added_by        = $attendance->added_by ?? $admin->id;
                $attendance->updated_by      = $admin->id;

                $this->applyStatusCalculations($attendance, $employee, $settings, $dateStr);

                $attendance->save();
                $saved++;
            }

            $current->addDay();
        }

        $msg = "تم تسجيل <strong>{$saved}</strong> سجل حضور بنجاح.";
        if ($skippedExisting) {
            $msg .= " تم تجاهل <strong>{$skippedExisting}</strong> حالة لوجود سجل حضور محفوظ مسبقاً لنفس الموظف/اليوم"
                  . ($overwrite ? '' : ' — فعّل خيار "استبدال السجلات الموجودة مسبقاً" إذا أردت الكتابة فوقها.');
        }
        if ($skippedWeekly) {
            $msg .= " وتم تجاهل <strong>{$skippedWeekly}</strong> حالة لكونها يوم الراحة الأسبوعي للموظف.";
        }

        return redirect()->route('attendance.index')->with('success', $msg);
    }

    /**
     * يحسب/يصفّر حقول الحضور حسب الحالة (يُستخدم في التسجيل الدفعي عبر نطاق تاريخ)
     */
    private function applyStatusCalculations(Attendance $attendance, Employee $employee, ?Admin_panel_setting $settings, string $date): void
    {
        if ($attendance->status == 6) {
            $attendance->is_weekly_off_worked      = 0;
            $attendance->leave_compensation_amount = 0;
            $attendance->weekly_off_overtime       = null;
            $attendance->late_minutes              = 0;
            $attendance->early_departure_minutes   = 0;
            $attendance->overtime_hours            = 0;
            $attendance->overtime_amount           = 0;
            $attendance->late_deduction            = 0;
            $attendance->early_departure_deduction = 0;
        } elseif ($attendance->status == 1 && $attendance->check_in_time && $attendance->check_out_time) {
            $this->applyCalculations($attendance, $employee, $settings, $date);
        } else {
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
        }
    }

    // ─────────────────────────────────────────────
    //  EDIT / UPDATE
    // ─────────────────────────────────────────────
    public function edit(Request $request, int $id)
    {
        $attendance   = Attendance::with(['employee', 'shift', 'shiftOverride'])->findOrFail($id);
        $employees    = Employee::orderBy('employee_name_A')->get();
        $comCode      = Auth::guard('admin')->user()->com_code;
        $shifts_types = Shifts_type::where('com_code', $comCode)->orderBy('type')->get();
        $settings     = Admin_panel_setting::getByComCode($comCode);
        $backUrl      = $request->query('back', route('attendance.index'));

        $date     = $attendance->attendance_date->format('Y-m-d');
        $nextDate = Carbon::parse($date)->addDay()->format('Y-m-d');

        $fingerId   = $attendance->employee->finger_id ?? null;
        $fingerLogs = collect();

        if ($fingerId) {
            $shift       = $attendance->shiftOverride ?? $attendance->shift ?? $attendance->employee->shifts_type;
            $isNight     = $shift && ($shift->to_time < $shift->from_time);
            $windowStart = Carbon::parse($date . ' ' . ($shift->from_time ?? '00:00:00'))->subHours(3);
            $windowEnd   = $isNight
                ? Carbon::parse($nextDate . ' ' . $shift->to_time)->addHours(3)
                : Carbon::parse($nextDate . ' 06:00:00');

            $fingerLogs = FingerprintLog::with('device')
                ->where('com_code', $comCode)
                ->where('finger_id', $fingerId)
                ->whereBetween('punch_time', [
                    $windowStart->format('Y-m-d H:i:s'),
                    $windowEnd->format('Y-m-d H:i:s'),
                ])
                ->orderBy('punch_time')
                ->get();
        }

        return view('admin.attendance.edit', compact(
            'attendance', 'employees', 'shifts_types', 'settings',
            'backUrl', 'date', 'nextDate', 'fingerLogs'
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
        ], [
            'check_in_time.date_format'  => 'وقت الحضور يجب أن يكون بتنسيق HH:MM',
            'check_out_time.date_format' => 'وقت الانصراف يجب أن يكون بتنسيق HH:MM',
        ]);

        $attendance = Attendance::findOrFail($id);
        $employee   = $attendance->employee;

        $attendance->check_in_time  = $request->check_in_time  ? substr($request->check_in_time,  0, 5) : null;
        $attendance->check_out_time = $request->check_out_time ? substr($request->check_out_time, 0, 5) : null;
        $attendance->status         = $request->status;
        $attendance->notes          = $request->notes;
        $attendance->is_manual_lock = $request->boolean('is_manual_lock');
        $attendance->updated_by     = Auth::guard('admin')->id();

        // إذا تم إدخال كلا الوقتين يدوياً → مسح حالة البصمة الناقصة
        if ($attendance->check_in_time && $attendance->check_out_time) {
            $attendance->missing_punch            = null;
            $attendance->missing_punch_resolution = null;
            $attendance->missing_punch_hours      = null;
            $attendance->missing_punch_deduction  = 0;
        }

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
            // ─── حضور: احتساب التأخير/الأوفرتايم ───
            $this->applyCalculations($attendance, $employee, $settings, $date);

            // ─── إذا كان يوم راحة عمل فيه: احتساب بدل الإجازة ───
            $attendance->is_weekly_off_worked = (int)($request->is_weekly_off_worked ?? 0);
            $this->handleWeeklyOffWorked($attendance, $employee, $settings);

        } else {
            // ─── غياب / إجازة / مأمورية: صفر الحسابات ───
            $attendance->late_minutes            = 0;
            $attendance->early_departure_minutes = 0;
            $attendance->overtime_hours          = 0;
            $attendance->overtime_amount         = 0;
            $attendance->late_deduction          = 0;
            $attendance->early_departure_deduction = 0;
            $attendance->late_fraction             = null;
            $attendance->early_departure_fraction  = null;
            $attendance->is_weekly_off_worked      = 0;
            $attendance->leave_compensation_amount = 0;
            $attendance->weekly_off_overtime       = null;
        }

        $attendance->save();

        $backUrl = $request->input('_back_url', route('attendance.index'));
        return redirect()->to($backUrl)->with('success', 'تم تحديث سجل الحضور بنجاح');
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
        $attendance->missing_punch_deduction  = $deduction;
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

        if ($attendance->check_in_time && $attendance->check_out_time && $attendance->status == 1) {
            $this->applyCalculations($attendance, $employee, $settings, $attendance->attendance_date->format('Y-m-d'));
        }

        $attendance->save();

        return redirect()->back()->with('success', 'تم تحديث الشيفت وإعادة الاحتساب');
    }

    // ─────────────────────────────────────────────
    //  تبديل بدل الإجازة الأسبوعية (toggle)
    // ─────────────────────────────────────────────
    public function toggleWeeklyOff(Request $request, int $id)
    {
        $attendance = Attendance::findOrFail($id);
        $employee   = $attendance->employee;
        $comCode    = Auth::guard('admin')->user()->com_code;
        $settings   = Admin_panel_setting::where('com_code', $comCode)->first();

        $attendance->is_weekly_off_worked = $attendance->is_weekly_off_worked ? 0 : 1;
        $attendance->updated_by           = Auth::guard('admin')->id();

        $this->handleWeeklyOffWorked($attendance, $employee, $settings);
        $attendance->save();

        $msg = $attendance->is_weekly_off_worked
            ? 'تم تفعيل بدل الإجازة: ' . number_format($attendance->leave_compensation_amount, 2) . ' ج.م'
            : 'تم إلغاء بدل الإجازة الأسبوعية';

        $backUrl = $request->input('_back_url', route('attendance.index'));
        return redirect()->to($backUrl)->with('success', $msg);
    }

    // ─────────────────────────────────────────────
    //  تفريغ البصمة وتحويل السجلات إلى غياب
    // ─────────────────────────────────────────────
    public function voidFingerprint(Request $request)
    {
        $comCode = Auth::guard('admin')->user()->com_code;

        $query = Attendance::where('com_code', $comCode)
            ->where('is_manual_lock', 0);

        if ($request->filled('employee_id')) $query->where('employee_id', $request->employee_id);
        if ($request->filled('from_date'))   $query->where('attendance_date', '>=', $request->from_date);
        if ($request->filled('to_date'))     $query->where('attendance_date', '<=', $request->to_date);
        if ($request->filled('status'))      $query->where('status', $request->status);

        $records = $query->get();

        if ($records->isEmpty()) {
            return redirect()->back()->with('error', 'لا توجد سجلات مطابقة للفلتر المحدد');
        }

        $employeeIds = $records->pluck('employee_id')->unique()->values();
        $minDate     = $records->min('attendance_date');
        $maxDate     = $records->max('attendance_date');

        // تحويل السجلات إلى غياب وتصفير البيانات
        $query->update([
            'status'                    => 2,
            'check_in_time'             => null,
            'check_out_time'            => null,
            'missing_punch'             => null,
            'missing_punch_resolution'  => null,
            'missing_punch_hours'       => null,
            'missing_punch_deduction'   => 0,
            'late_minutes'              => 0,
            'early_departure_minutes'   => 0,
            'overtime_hours'            => 0,
            'overtime_amount'           => 0,
            'late_deduction'            => 0,
            'early_departure_deduction' => 0,
            'late_fraction'             => null,
            'early_departure_fraction'  => null,
            'is_weekly_off_worked'      => 0,
            'leave_compensation_amount' => 0,
            'weekly_off_overtime'       => null,
        ]);

        // إعادة بصمات الموظفين المتأثرين في النطاق لغير معالَجة
        $fingerIds = \App\Models\Employee::where('com_code', $comCode)
            ->whereIn('id', $employeeIds)
            ->whereNotNull('finger_id')
            ->pluck('finger_id');

        if ($fingerIds->isNotEmpty()) {
            FingerprintLog::where('com_code', $comCode)
                ->whereIn('finger_id', $fingerIds)
                ->where('is_processed', 1)
                ->whereBetween('punch_time', [
                    Carbon::parse($minDate)->startOfDay()->format('Y-m-d H:i:s'),
                    Carbon::parse($maxDate)->addDay()->endOfDay()->format('Y-m-d H:i:s'),
                ])
                ->update(['is_processed' => 0]);
        }

        return redirect()->route('attendance.index', $request->only(['employee_id', 'from_date', 'to_date', 'status']))
            ->with('success', "تم تفريغ {$records->count()} سجل وتحويلها إلى غياب وإعادة البصمات لغير معالَجة");
    }

    // ─────────────────────────────────────────────
    //  إعادة معالجة البصمة للسجلات المفلترة
    // ─────────────────────────────────────────────
    public function bulkReprocessFingerprint(Request $request)
    {
        $comCode = Auth::guard('admin')->user()->com_code;

        // تحديد نطاق التاريخ
        $fromDate = $request->filled('from_date')
            ? $request->from_date
            : Carbon::today()->subMonth()->format('Y-m-d');

        $toDate = $request->filled('to_date')
            ? $request->to_date
            : Carbon::today()->format('Y-m-d');

        $employeeId = $request->filled('employee_id') ? (int)$request->employee_id : null;

        $service = new FingerprintService();
        $result  = $service->processLogs($comCode, $fromDate, $toDate, true, $employeeId);

        if (!$result['success']) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء إعادة المعالجة: ' . ($result['error'] ?? ''));
        }

        $msg = "تمت إعادة المعالجة: حضور {$result['imported']}، بصمة ناقصة {$result['missing']}، غياب {$result['absent']}.";
        if (!empty($result['notFound'])) {
            $msg .= ' ⚠️ IDs غير معروفة: ' . implode('، ', array_unique($result['notFound']));
        }

        return redirect()->route('attendance.index', $request->only(['employee_id', 'from_date', 'to_date', 'status']))
            ->with('success', $msg);
    }

    // ─────────────────────────────────────────────
    //  إعادة معالجة البصمة لسجل واحد (مع شيفت جديد)
    // ─────────────────────────────────────────────
    public function reprocessFingerprint(Request $request, int $id)
    {
        $attendance = Attendance::with(['employee', 'shift', 'shiftOverride'])->findOrFail($id);
        $employee   = $attendance->employee;
        $comCode    = Auth::guard('admin')->user()->com_code;

        // استخدام الشيفت المخصص إن وُجد، وإلا شيفت الموظف
        $shift = $attendance->shiftOverride ?? $attendance->shift ?? $employee->shifts_type;

        if (!$shift) {
            return redirect()->back()->with('error', 'لم يُحدد شيفت للموظف أو للسجل');
        }

        $service = new FingerprintService();
        $result  = $service->reprocessAttendanceFromLogs($attendance, $employee, $shift);

        if (!$result['success'] && empty($result['resigned']) && empty($result['cleared'])) {
            return redirect()->back()->with('error', $result['error'] ?? 'لم يُعثر على بصمات في النافذة الزمنية');
        }

        // إعادة احتساب التأخير والأوفرتايم إذا يوجد حضور وانصراف
        if ($attendance->check_in_time && $attendance->check_out_time && $attendance->status == 1) {
            $settings = Admin_panel_setting::where('com_code', $comCode)->first();
            $this->applyCalculations($attendance, $employee, $settings, $attendance->attendance_date->format('Y-m-d'));
        }

        $attendance->updated_by = Auth::guard('admin')->id();
        $attendance->save();

        $msg = $result['success']
            ? "تمت إعادة معالجة البصمة: حضور {$result['checkIn']} — انصراف " . ($result['checkOut'] ?? 'ناقص')
            : ($result['error'] ?? 'تم تحديث السجل');

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
