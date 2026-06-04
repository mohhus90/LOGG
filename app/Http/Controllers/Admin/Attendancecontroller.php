<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Admin_panel_setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AttendanceController extends Controller
{
    /**
     * عرض سجلات الحضور مع فلاتر البحث
     */
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

        $data = $query->orderByDesc('attendance_date')->paginate(20);

        return view('admin.attendance.index', compact('data', 'employees'));
    }

    /**
     * عرض نموذج الإضافة
     */
    public function create()
    {
        $employees = Employee::where('is_has_finger', 1)->orderBy('employee_name_A')->get();
        return view('admin.attendance.create', compact('employees'));
    }

    /**
     * حفظ سجل الحضور مع احتساب التأخير والأوفرتايم تلقائيًا
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id'     => 'required|exists:employees,id',
            'attendance_date' => 'required|date',
            'status'          => 'required|integer|between:1,5',
            'check_in_time'   => 'nullable|date_format:H:i,H:i:s',
            'check_out_time'  => 'nullable|date_format:H:i,H:i:s',
            'notes'           => 'nullable|string|max:500',
        ], [
            'employee_id.required'     => 'اختر الموظف',
            'attendance_date.required' => 'أدخل التاريخ',
            'check_in_time.date_format'  => 'وقت الحضور يجب أن يكون بتنسيق HH:MM',
            'check_out_time.date_format' => 'وقت الانصراف يجب أن يكون بتنسيق HH:MM',
        ]);

        $employee = Employee::findOrFail($request->employee_id);

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

        if ($request->status == 1 && $request->check_in_time && $request->check_out_time) {
            $settings     = Admin_panel_setting::where('com_code', $attendance->com_code)->first();
            $graceMinutes = (float)($settings->after_minute_calc_delay ?? 0);
            $multiplier   = $employee->overtime_enabled ?? 1
                ? ($employee->custom_overtime_multiplier ?? (float)($settings->overtime_multiplier ?? 1.5))
                : 0;
            $minuteRate   = (float)($settings->sanctions_value_minute_delay ?? 0) > 0
                ? (float)$settings->sanctions_value_minute_delay : null;

            $attendance->calculateDelayAndOvertime($graceMinutes);
            $dailyRate = $employee->emp_sal ? ($employee->emp_sal / 26) : 0;
            $attendance->calculateAmounts(
                $dailyRate,
                $multiplier,
                $minuteRate,
                (bool)($employee->overtime_enabled ?? 1),
                (bool)($employee->late_deduction_enabled ?? 1)
            );
        }

        $attendance->save();

        return redirect()->route('attendance.index')
            ->with('success', 'تم تسجيل الحضور بنجاح');
    }

    /**
     * إدخال دفعي - تسجيل حضور جميع الموظفين ليوم معين
     */
    public function bulkCreate(Request $request)
    {
        $employees = Employee::where('is_has_finger', 1)->orderBy('employee_name_A')->get();
        $date      = $request->date ?? today()->format('Y-m-d');

        // جلب السجلات المسجلة مسبقًا لهذا اليوم
        $existing = Attendance::where('attendance_date', $date)
            ->pluck('employee_id')->toArray();

        return view('admin.attendance.bulk_create', compact('employees', 'date', 'existing'));
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'attendance_date' => 'required|date',
            'records'         => 'required|array',
        ]);

        $admin    = Auth::guard('admin')->user();
        $date     = $request->attendance_date;
        $saved    = 0;

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
                $settings     = $settings ?? Admin_panel_setting::where('com_code', $admin->com_code)->first();
                $graceMinutes = (float)($settings->after_minute_calc_delay ?? 0);
                $multiplier   = $employee->overtime_enabled ?? 1
                    ? ($employee->custom_overtime_multiplier ?? (float)($settings->overtime_multiplier ?? 1.5))
                    : 0;
                $minuteRate   = (float)($settings->sanctions_value_minute_delay ?? 0) > 0
                    ? (float)$settings->sanctions_value_minute_delay : null;

                $attendance->calculateDelayAndOvertime($graceMinutes);
                $dailyRate = $employee->emp_sal ? ($employee->emp_sal / 26) : 0;
                $attendance->calculateAmounts(
                    $dailyRate,
                    $multiplier,
                    $minuteRate,
                    (bool)($employee->overtime_enabled ?? 1),
                    (bool)($employee->late_deduction_enabled ?? 1)
                );
            }

            $attendance->save();
            $saved++;
        }

        return redirect()->route('attendance.index')
            ->with('success', "تم تسجيل حضور $saved موظف بنجاح ليوم $date");
    }

    public function edit(int $id)
    {
        $attendance = Attendance::with(['employee', 'shift'])->findOrFail($id);
        $employees  = Employee::orderBy('employee_name_A')->get();
        return view('admin.attendance.edit', compact('attendance', 'employees'));
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'check_in_time'  => 'nullable|date_format:H:i,H:i:s',
            'check_out_time' => 'nullable|date_format:H:i,H:i:s',
            'status'         => 'required|integer|between:1,5',
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
        $attendance->updated_by     = Auth::guard('admin')->id();

        if ($attendance->status == 1 && $attendance->check_in_time && $attendance->check_out_time) {
            $settings     = Admin_panel_setting::where('com_code', $attendance->com_code)->first();
            $graceMinutes = (float)($settings->after_minute_calc_delay ?? 0);
            $multiplier   = $employee->overtime_enabled ?? 1
                ? ($employee->custom_overtime_multiplier ?? (float)($settings->overtime_multiplier ?? 1.5))
                : 0;
            $minuteRate   = (float)($settings->sanctions_value_minute_delay ?? 0) > 0
                ? (float)$settings->sanctions_value_minute_delay : null;

            $attendance->calculateDelayAndOvertime($graceMinutes);
            $dailyRate = $employee->emp_sal ? ($employee->emp_sal / 26) : 0;
            $attendance->calculateAmounts(
                $dailyRate,
                $multiplier,
                $minuteRate,
                (bool)($employee->overtime_enabled ?? 1),
                (bool)($employee->late_deduction_enabled ?? 1)
            );
        } else {
            $attendance->late_minutes   = 0;
            $attendance->overtime_hours = 0;
            $attendance->overtime_amount = 0;
            $attendance->late_deduction = 0;
        }

        $attendance->save();

        return redirect()->route('attendance.index')
            ->with('success', 'تم تحديث سجل الحضور بنجاح');
    }

    public function delete(int $id)
    {
        Attendance::findOrFail($id)->delete();
        return redirect()->route('attendance.index')
            ->with('success', 'تم حذف السجل بنجاح');
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

            // قراءة الملف
            $spreadsheet = IOFactory::load($request->file('excel_file')->getPathname());
            $rows        = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

            // تجاهل الصف الأول (headers)
            array_shift($rows);

            // جمع بيانات البصمة: finger_id → [dates → [times]]
            $fingerData = [];
            foreach ($rows as $row) {
                $fingerId = isset($row[0]) ? trim((string)$row[0]) : null;
                if (empty($fingerId)) continue;

                if ($hasDateCol) {
                    // التنسيق: A=fingerId | B=date | C=check_in | D=check_out
                    $rowDate   = isset($row[1]) ? $this->parseExcelDate($row[1]) : $date;
                    $checkIn   = isset($row[2]) ? $this->parseExcelTime($row[2]) : null;
                    $checkOut  = isset($row[3]) ? $this->parseExcelTime($row[3]) : null;
                } else {
                    // التنسيق: A=fingerId | B=check_in | C=check_out
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

            $saved      = 0;
            $skipped    = 0;
            $notFound   = [];

            // جلب الموظفين بـ finger_id
            $employees = Employee::where('com_code', $comCode)
                ->whereNotNull('finger_id')
                ->get()
                ->keyBy('finger_id');

            foreach ($fingerData as $fingerId => $dates) {
                $employee = $employees->get($fingerId);
                if (!$employee) {
                    $notFound[] = $fingerId;
                    continue;
                }

                foreach ($dates as $rowDate => $info) {
                    // لا تستبدل السجل الموجود
                    $existing = Attendance::where('employee_id', $employee->id)
                        ->where('attendance_date', $rowDate)->first();
                    if ($existing) { $skipped++; continue; }

                    $times    = $info['times'] ?? [];
                    $checkIn  = $info['check_in']  ?? (count($times) ? min($times) : null);
                    $checkOut = $info['check_out'] ?? (count($times) > 1 ? max($times) : null);

                    $att = new Attendance();
                    $att->employee_id     = $employee->id;
                    $att->shift_id        = $employee->shifts_types_id;
                    $att->attendance_date = $rowDate;
                    $att->check_in_time   = $checkIn;
                    $att->check_out_time  = $checkOut;
                    $att->status          = 1; // حاضر
                    $att->com_code        = $comCode;
                    $att->added_by        = $admin->id;

                    if ($checkIn && $checkOut) {
                        $settings     = $settings ?? Admin_panel_setting::where('com_code', $comCode)->first();
                        $graceMinutes = (float)($settings->after_minute_calc_delay ?? 0);
                        $multiplier   = $employee->overtime_enabled ?? 1
                            ? ($employee->custom_overtime_multiplier ?? (float)($settings->overtime_multiplier ?? 1.5))
                            : 0;
                        $minuteRate   = (float)($settings->sanctions_value_minute_delay ?? 0) > 0
                            ? (float)$settings->sanctions_value_minute_delay : null;

                        $att->calculateDelayAndOvertime($graceMinutes);
                        $dailyRate = $employee->emp_sal ? ($employee->emp_sal / 26) : 0;
                        $att->calculateAmounts(
                            $dailyRate,
                            $multiplier,
                            $minuteRate,
                            (bool)($employee->overtime_enabled ?? 1),
                            (bool)($employee->late_deduction_enabled ?? 1)
                        );
                    }
                    $att->save();
                    $saved++;
                }
            }

            // تسجيل غياب للغائبين
            if ($markAbsent) {
                $presentIds = $employees->filter(fn($e) => isset($fingerData[$e->finger_id]))->pluck('id');
                $allEmpIds  = Employee::where('com_code', $comCode)->where('is_has_finger', 1)->pluck('id');

                foreach ($allEmpIds as $empId) {
                    if ($presentIds->contains($empId)) continue;
                    $exists = Attendance::where('employee_id', $empId)->where('attendance_date', $date)->exists();
                    if (!$exists) {
                        Attendance::create([
                            'employee_id'     => $empId,
                            'attendance_date' => $date,
                            'status'          => 2, // غائب
                            'com_code'        => $comCode,
                            'added_by'        => $admin->id,
                        ]);
                    }
                }
            }

            $msg = "تم استيراد <strong>$saved</strong> سجل بنجاح.";
            if ($skipped)           $msg .= " تم تجاهل <strong>$skipped</strong> سجل موجود مسبقاً.";
            if (count($notFound))   $msg .= " Finger IDs غير موجودة: <strong>" . implode(', ', $notFound) . "</strong>.";

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

        $output = "\xEF\xBB\xBF"; // BOM for Arabic UTF-8
        foreach ($rows as $row) {
            $output .= implode(',', array_map(fn($v) => '"' . str_replace('"', '""', $v) . '"', $row)) . "\r\n";
        }

        return response($output, 200, $headers);
    }

    // ─── مساعدات تحويل ───

    private function parseExcelTime($value): ?string
    {
        if (empty($value) && $value !== '0') return null;

        // رقم عشري من Excel (0.354166... = 08:30)
        if (is_numeric($value) && $value > 0 && $value < 1) {
            $seconds = (int) round($value * 86400);
            return sprintf('%02d:%02d', intdiv($seconds, 3600), intdiv($seconds % 3600, 60));
        }

        // نص HH:MM أو H:MM
        if (preg_match('/^(\d{1,2}):(\d{2})/', (string)$value, $m)) {
            return sprintf('%02d:%02d', $m[1], $m[2]);
        }

        return null;
    }

    private function parseExcelDate($value): ?string
    {
        if (empty($value)) return null;

        // رقم Excel serial date
        if (is_numeric($value) && $value > 1000) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Exception $e) {}
        }

        // نص تاريخ
        try {
            return Carbon::parse((string)$value)->format('Y-m-d');
        } catch (\Exception $e) {}

        return null;
    }

    /**
     * ملخص حضور موظف لشهر معين
     */
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
            'present_days'    => $records->where('status', 1)->count(),
            'absent_days'     => $records->where('status', 2)->count(),
            'leave_days'      => $records->where('status', 3)->count(),
            'total_late_min'  => $records->sum('late_minutes'),
            'total_overtime'  => $records->sum('overtime_hours'),
            'total_overtime_amount' => $records->sum('overtime_amount'),
            'total_late_deduction'  => $records->sum('late_deduction'),
        ];

        return view('admin.attendance.employee_summary', compact('employee', 'records', 'summary', 'month', 'year'));
    }
}
