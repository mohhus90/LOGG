<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Shifts_type;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AttendanceController extends Controller
{
    // ─────────────────────────────────────────────
    // مساعدات
    // ─────────────────────────────────────────────
    private function comCode(): int
    {
        return (int) Auth::guard('admin')->user()->com_code;
    }

    private function employees()
    {
        // ✅ FIX: إزالة is_has_finger restriction — جلب كل موظفي الشركة
        return Employee::where('com_code', $this->comCode())
            ->orderBy('employee_name_A')->get();
    }

    // ─────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────
    public function index(Request $request)
    {
        $employees = $this->employees();

        // ✅ FIX: فلترة بـ com_code
        $query = Attendance::with(['employee', 'shift'])
            ->where('com_code', $this->comCode());

        if ($request->filled('employee_id')) $query->where('employee_id', $request->employee_id);
        if ($request->filled('from_date'))   $query->where('attendance_date', '>=', $request->from_date);
        if ($request->filled('to_date'))     $query->where('attendance_date', '<=', $request->to_date);
        if ($request->filled('status'))      $query->where('status', $request->status);

        $data = $query->orderByDesc('attendance_date')->paginate(20);

        return view('admin.attendance.index', compact('data', 'employees'));
    }

    // ─────────────────────────────────────────────
    // CREATE
    // ─────────────────────────────────────────────
    public function create()
    {
        $employees = $this->employees();

        if ($employees->isEmpty()) {
            return redirect()->route('attendance.index')
                ->with('error', 'لا يوجد موظفون مسجلون. يرجى إضافة موظفين أولاً من قسم الموظفين.');
        }

        return view('admin.attendance.create', compact('employees'));
    }

    // ─────────────────────────────────────────────
    // STORE
    // ─────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'employee_id'     => 'required|exists:employees,id',
            'attendance_date' => 'required|date',
            'status'          => 'required|integer|between:1,5',
            'check_in_time'   => 'nullable|date_format:H:i',
            'check_out_time'  => 'nullable|date_format:H:i',
            'notes'           => 'nullable|string|max:500',
        ], [
            'employee_id.required'     => 'اختر الموظف',
            'attendance_date.required' => 'أدخل التاريخ',
        ]);

        // ✅ FIX: التحقق من عدم التكرار
        $exists = Attendance::where('employee_id', $request->employee_id)
            ->where('attendance_date', $request->attendance_date)->exists();
        if ($exists) {
            return back()->with('error', 'يوجد سجل حضور مسجل مسبقاً لهذا الموظف في هذا التاريخ.')->withInput();
        }

        $employee   = Employee::findOrFail($request->employee_id);
        $attendance = new Attendance();

        $attendance->employee_id     = $request->employee_id;
        $attendance->shift_id        = $employee->shifts_types_id;
        $attendance->attendance_date = $request->attendance_date;
        $attendance->check_in_time   = $request->check_in_time;
        $attendance->check_out_time  = $request->check_out_time;
        $attendance->status          = $request->status;
        $attendance->notes           = $request->notes;
        // ✅ FIX: com_code من الأدمن
        $attendance->com_code        = $this->comCode();
        $attendance->added_by        = Auth::guard('admin')->id();

        if ($request->status == 1 && $request->check_in_time && $request->check_out_time) {
            $attendance->calculateDelayAndOvertime();
            $dailyRate = $employee->emp_sal ? ($employee->emp_sal / 26) : 0;
            $attendance->calculateAmounts($dailyRate);
        }

        $attendance->save();

        return redirect()->route('attendance.index')->with('success', 'تم تسجيل الحضور بنجاح');
    }

    // ─────────────────────────────────────────────
    // BULK CREATE
    // ─────────────────────────────────────────────
    public function bulkCreate(Request $request)
    {
        $employees = $this->employees();
        $date      = $request->date ?? today()->format('Y-m-d');

        if ($employees->isEmpty()) {
            return redirect()->route('attendance.index')
                ->with('error', 'لا يوجد موظفون. يرجى إضافة موظفين أولاً.');
        }

        // ✅ FIX: فلترة بـ com_code + جلب existingRecords
        $existing = Attendance::where('attendance_date', $date)
            ->where('com_code', $this->comCode())
            ->pluck('employee_id')->toArray();

        // ✅ FIX: إضافة existingRecords للـ view
        $existingRecords = Attendance::where('attendance_date', $date)
            ->where('com_code', $this->comCode())
            ->get()->keyBy('employee_id');

        return view('admin.attendance.bulk_create',
            compact('employees', 'date', 'existing', 'existingRecords'));
    }

    // ─────────────────────────────────────────────
    // BULK STORE
    // ─────────────────────────────────────────────
    public function bulkStore(Request $request)
    {
        $request->validate([
            'attendance_date' => 'required|date',
            'records'         => 'required|array',
        ]);

        $admin = Auth::guard('admin')->user();
        $date  = $request->attendance_date;
        $saved = 0;

        DB::beginTransaction();
        try {
            foreach ($request->records as $empId => $record) {
                // ✅ FIX: فلترة الموظف بـ com_code
                $employee = Employee::where('id', $empId)
                    ->where('com_code', $admin->com_code)->first();
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
                $attendance->com_code       = (int)$admin->com_code;
                $attendance->added_by       = $admin->id;

                if ($attendance->status == 1 && $attendance->check_in_time && $attendance->check_out_time) {
                    $attendance->calculateDelayAndOvertime();
                    $dailyRate = $employee->emp_sal ? ($employee->emp_sal / 26) : 0;
                    $attendance->calculateAmounts($dailyRate);
                } else {
                    $attendance->late_minutes    = 0;
                    $attendance->overtime_hours  = 0;
                    $attendance->overtime_amount = 0;
                    $attendance->late_deduction  = 0;
                }

                $attendance->save();
                $saved++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'خطأ أثناء الحفظ: ' . $e->getMessage());
        }

        return redirect()->route('attendance.index')
            ->with('success', "تم تسجيل حضور $saved موظف بنجاح ليوم $date");
    }

    // ─────────────────────────────────────────────
    // EXCEL IMPORT
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
            'mark_absent'     => 'nullable|boolean',
            'has_date_col'    => 'nullable|boolean',
        ]);

        $admin      = Auth::guard('admin')->user();
        $date       = $request->attendance_date;
        $markAbsent = $request->boolean('mark_absent', true);
        $hasDateCol = $request->boolean('has_date_col', false);

        try {
            $spreadsheet = IOFactory::load($request->file('excel_file')->getPathname());
        } catch (\Exception $e) {
            return back()->with('error', 'تعذّر قراءة الملف: ' . $e->getMessage());
        }

        $rows     = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        $firstRow = array_values($rows)[0] ?? [];
        $isHeader = !is_numeric(trim($firstRow['A'] ?? ''));
        if ($isHeader) array_shift($rows);

        $excelData = [];
        foreach ($rows as $row) {
            $fingerId = trim((string)($row['A'] ?? ''));
            if ($fingerId === '') continue;

            $checkIn  = $hasDateCol ? $this->parseTime($row['C'] ?? null) : $this->parseTime($row['B'] ?? null);
            $checkOut = $hasDateCol ? $this->parseTime($row['D'] ?? null) : $this->parseTime($row['C'] ?? null);

            if (isset($excelData[$fingerId])) {
                if ($checkIn)  $excelData[$fingerId]['check_in']  = min($excelData[$fingerId]['check_in'] ?? $checkIn, $checkIn);
                if ($checkOut) $excelData[$fingerId]['check_out'] = max($excelData[$fingerId]['check_out'] ?? $checkOut, $checkOut);
            } else {
                $excelData[$fingerId] = ['check_in' => $checkIn, 'check_out' => $checkOut];
            }
        }

        $allEmployees = Employee::where('com_code', $admin->com_code)->get();
        $fingerMap    = $allEmployees->keyBy(fn($e) => (string)$e->finger_id);
        $imported = $absent = 0;
        $notFound = [];

        DB::beginTransaction();
        try {
            foreach ($excelData as $fingerId => $times) {
                $employee = $fingerMap->get((string)$fingerId);
                if (!$employee) { $notFound[] = $fingerId; continue; }

                $att = Attendance::firstOrNew(['employee_id' => $employee->id, 'attendance_date' => $date]);
                $att->shift_id       = $employee->shifts_types_id;
                $att->check_in_time  = $times['check_in'];
                $att->check_out_time = $times['check_out'];
                $att->status         = 1;
                $att->com_code       = (int)$admin->com_code;
                $att->added_by       = $admin->id;
                $att->notes          = 'Excel import';

                if ($att->check_in_time && $att->check_out_time) {
                    $att->calculateDelayAndOvertime();
                    $dailyRate = $employee->emp_sal ? ($employee->emp_sal / 26) : 0;
                    $att->calculateAmounts($dailyRate);
                }
                $att->save();
                $imported++;
            }

            if ($markAbsent) {
                foreach ($allEmployees as $emp) {
                    $fid = (string)$emp->finger_id;
                    if (array_key_exists($fid, $excelData)) continue;
                    if (Attendance::where('employee_id', $emp->id)->where('attendance_date', $date)->exists()) continue;

                    Attendance::create([
                        'employee_id'     => $emp->id,
                        'shift_id'        => $emp->shifts_types_id,
                        'attendance_date' => $date,
                        'status'          => 2,
                        'late_minutes'    => 0, 'overtime_hours' => 0,
                        'overtime_amount' => 0, 'late_deduction' => 0,
                        'notes'           => 'غياب تلقائي - Excel',
                        'com_code'        => (int)$admin->com_code,
                        'added_by'        => $admin->id,
                    ]);
                    $absent++;
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'خطأ: ' . $e->getMessage());
        }

        $msg = "✅ تم استيراد حضور $imported موظف.";
        if ($absent)           $msg .= " 🔴 $absent غياب تلقائي.";
        if (!empty($notFound)) $msg .= " ⚠️ IDs غير معروفة: " . implode('، ', $notFound);

        return redirect()->route('attendance.index')->with('success', $msg);
    }

    public function excelTemplate()
    {
        $employees = Employee::where('com_code', $this->comCode())
            ->whereNotNull('finger_id')->take(10)->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Finger ID');
        $sheet->setCellValue('B1', 'وقت الحضور (HH:MM)');
        $sheet->setCellValue('C1', 'وقت الانصراف (HH:MM)');

        $row = 2;
        foreach ($employees as $emp) {
            $sheet->setCellValue("A{$row}", $emp->finger_id);
            $sheet->setCellValue("B{$row}", '08:00');
            $sheet->setCellValue("C{$row}", '17:00');
            $row++;
        }

        $filename = 'attendance_template_' . today()->format('Y-m-d') . '.xlsx';
        $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->stream(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    private function parseTime($value): ?string
    {
        if ($value === null || $value === '') return null;
        if (is_numeric($value) && (float)$value < 1) {
            $secs = round((float)$value * 86400);
            return sprintf('%02d:%02d', intdiv($secs, 3600), intdiv($secs % 3600, 60));
        }
        if (preg_match('/(\d{1,2}):(\d{2})/', (string)$value, $m)) {
            return sprintf('%02d:%02d', $m[1], $m[2]);
        }
        return null;
    }

    // ─────────────────────────────────────────────
    // EDIT / UPDATE / DELETE
    // ─────────────────────────────────────────────
    public function edit(int $id)
    {
        // ✅ FIX: فلترة بـ com_code
        $attendance = Attendance::with(['employee', 'shift'])
            ->where('com_code', $this->comCode())->findOrFail($id);
        $employees  = $this->employees();
        return view('admin.attendance.edit', compact('attendance', 'employees'));
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'check_in_time'  => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
            'status'         => 'required|integer|between:1,5',
        ]);

        // ✅ FIX: فلترة بـ com_code
        $attendance = Attendance::where('com_code', $this->comCode())->findOrFail($id);
        $employee   = $attendance->employee;

        $attendance->check_in_time  = $request->check_in_time;
        $attendance->check_out_time = $request->check_out_time;
        $attendance->status         = $request->status;
        $attendance->notes          = $request->notes;
        $attendance->updated_by     = Auth::guard('admin')->id();

        if ($attendance->status == 1 && $attendance->check_in_time && $attendance->check_out_time) {
            $attendance->calculateDelayAndOvertime();
            $dailyRate = $employee->emp_sal ? ($employee->emp_sal / 26) : 0;
            $attendance->calculateAmounts($dailyRate);
        } else {
            $attendance->late_minutes    = 0;
            $attendance->overtime_hours  = 0;
            $attendance->overtime_amount = 0;
            $attendance->late_deduction  = 0;
        }

        $attendance->save();
        return redirect()->route('attendance.index')->with('success', 'تم تحديث سجل الحضور بنجاح');
    }

    public function delete(int $id)
    {
        // ✅ FIX: فلترة بـ com_code
        Attendance::where('com_code', $this->comCode())->findOrFail($id)->delete();
        return redirect()->route('attendance.index')->with('success', 'تم حذف السجل بنجاح');
    }

    // ─────────────────────────────────────────────
    // EMPLOYEE SUMMARY
    // ─────────────────────────────────────────────
    public function employeeSummary(Request $request, int $employeeId)
    {
        // ✅ FIX: فلترة بـ com_code
        $employee = Employee::where('com_code', $this->comCode())->findOrFail($employeeId);
        $month    = $request->month ?? now()->month;
        $year     = $request->year  ?? now()->year;

        $records = Attendance::where('employee_id', $employeeId)
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->orderBy('attendance_date')->get();

        $summary = [
            'present_days'          => $records->where('status', 1)->count(),
            'absent_days'           => $records->where('status', 2)->count(),
            'leave_days'            => $records->whereIn('status', [3, 4])->count(),
            'total_late_min'        => $records->sum('late_minutes'),
            'total_overtime'        => $records->sum('overtime_hours'),
            'total_overtime_amount' => $records->sum('overtime_amount'),
            'total_late_deduction'  => $records->sum('late_deduction'),
        ];

        return view('admin.attendance.employee_summary',
            compact('employee', 'records', 'summary', 'month', 'year'));
    }
}