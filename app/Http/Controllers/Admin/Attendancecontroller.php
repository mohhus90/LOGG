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
    //  مساعدات خاصة
    // ─────────────────────────────────────────────
    private function comCode(): int
    {
        return Auth::guard('admin')->user()->com_code;
    }

    private function employees()
    {
        return Employee::where('com_code', $this->comCode())
            ->orderBy('employee_name_A')
            ->get();
    }

    // =========================================================
    //  INDEX
    // =========================================================
    public function index(Request $request)
    {
        $employees = $this->employees();

        $query = Attendance::with(['employee', 'shift'])
            ->where('com_code', $this->comCode());

        if ($request->filled('employee_id')) $query->where('employee_id', $request->employee_id);
        if ($request->filled('from_date'))   $query->where('attendance_date', '>=', $request->from_date);
        if ($request->filled('to_date'))     $query->where('attendance_date', '<=', $request->to_date);
        if ($request->filled('status'))      $query->where('status', $request->status);

        $data = $query->orderByDesc('attendance_date')->paginate(20);

        return view('admin.attendance.index', compact('data', 'employees'));
    }

    // =========================================================
    //  CREATE — فردي يدوي
    // =========================================================
    public function create()
    {
        $employees = $this->employees();

        if ($employees->isEmpty()) {
            return redirect()->route('attendance.index')
                ->with('error', 'لا يوجد موظفون مسجلون. يرجى إضافة موظفين أولاً من قسم الموظفين.');
        }

        return view('admin.attendance.create', compact('employees'));
    }

    // =========================================================
    //  STORE
    // =========================================================
    public function store(Request $request)
    {
        $request->validate([
            'employee_id'     => 'required|exists:employees,id',
            'attendance_date' => 'required|date',
            'status'          => 'required|integer|between:1,5',
            'check_in_time'   => 'nullable|date_format:H:i',
            'check_out_time'  => 'nullable|date_format:H:i',
        ]);

        // التحقق من عدم التكرار
        if (Attendance::where('employee_id', $request->employee_id)
            ->where('attendance_date', $request->attendance_date)->exists()) {
            return back()->with('error', 'يوجد سجل حضور مسبق لهذا الموظف في هذا التاريخ.')->withInput();
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

    // =========================================================
    //  BULK CREATE — إدخال دفعي يدوي
    // =========================================================
    public function bulkCreate(Request $request)
    {
        $employees = $this->employees();
        $date      = $request->date ?? today()->format('Y-m-d');

        if ($employees->isEmpty()) {
            return redirect()->route('attendance.index')
                ->with('error', 'لا يوجد موظفون مسجلون. يرجى إضافة موظفين أولاً من قسم الموظفين.');
        }

        $existing = Attendance::where('attendance_date', $date)
            ->where('com_code', $this->comCode())
            ->pluck('employee_id')->toArray();

        $existingRecords = Attendance::where('attendance_date', $date)
            ->where('com_code', $this->comCode())
            ->get()->keyBy('employee_id');

        return view('admin.attendance.bulk_create',
            compact('employees', 'date', 'existing', 'existingRecords'));
    }

    // =========================================================
    //  BULK STORE
    // =========================================================
    public function bulkStore(Request $request)
    {
        $request->validate(['attendance_date' => 'required|date', 'records' => 'required|array']);

        $admin = Auth::guard('admin')->user();
        $date  = $request->attendance_date;
        $saved = 0;

        DB::beginTransaction();
        try {
            foreach ($request->records as $empId => $record) {
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
                $attendance->com_code       = $admin->com_code;
                $attendance->added_by       = $admin->id;

                if ($attendance->status == 1 && $attendance->check_in_time && $attendance->check_out_time) {
                    $attendance->calculateDelayAndOvertime();
                    $dailyRate = $employee->emp_sal ? ($employee->emp_sal / 26) : 0;
                    $attendance->calculateAmounts($dailyRate);
                } else {
                    $attendance->late_minutes = $attendance->overtime_hours =
                    $attendance->overtime_amount = $attendance->late_deduction = 0;
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

    // =========================================================
    //  EXCEL IMPORT — رفع ملف Excel من جهاز البصمة
    //
    //  تنسيق الملف:
    //  A: finger_id | B: التاريخ | C: وقت الحضور | D: وقت الانصراف
    //  (أو A: finger_id | B: وقت الحضور | C: وقت الانصراف — إذا كان التاريخ ثابتاً)
    //  الموظفون الغائبون عن الملف يُسجَّل لهم غياب تلقائي
    // =========================================================
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

        // ── قراءة الملف ──
        try {
            $spreadsheet = IOFactory::load($request->file('excel_file')->getPathname());
        } catch (\Exception $e) {
            return back()->with('error', 'تعذّر قراءة الملف. تأكد من أن الملف بصيغة xlsx أو xls أو csv.');
        }

        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        // تجاهل الصف الأول إذا كان headers
        $firstRow = array_values($rows)[0] ?? [];
        $isHeader = !is_numeric(trim($firstRow['A'] ?? ''));
        if ($isHeader) array_shift($rows);

        // ── بناء map: finger_id → { check_in, check_out } ──
        $excelData = [];

        foreach ($rows as $row) {
            $fingerId = trim((string)($row['A'] ?? ''));
            if ($fingerId === '') continue;

            if ($hasDateCol) {
                // A=finger_id, B=date, C=check_in, D=check_out
                $checkIn  = $this->parseTime($row['C'] ?? null);
                $checkOut = $this->parseTime($row['D'] ?? null);
            } else {
                // A=finger_id, B=check_in, C=check_out
                $checkIn  = $this->parseTime($row['B'] ?? null);
                $checkOut = $this->parseTime($row['C'] ?? null);
            }

            // إذا تكرر الـ finger_id خذ أبكر حضور وأحدث انصراف
            if (isset($excelData[$fingerId])) {
                if ($checkIn)  $excelData[$fingerId]['check_in']  = min($excelData[$fingerId]['check_in']  ?? $checkIn,  $checkIn);
                if ($checkOut) $excelData[$fingerId]['check_out'] = max($excelData[$fingerId]['check_out'] ?? $checkOut, $checkOut);
            } else {
                $excelData[$fingerId] = ['check_in' => $checkIn, 'check_out' => $checkOut];
            }
        }

        // ── جلب كل موظفي الشركة ──
        $allEmployees = Employee::where('com_code', $admin->com_code)->get();
        $fingerMap    = $allEmployees->keyBy(fn($e) => (string)$e->finger_id);

        $imported  = 0;
        $absent    = 0;
        $notFound  = [];

        DB::beginTransaction();
        try {
            // 1️⃣ تسجيل من في الملف
            foreach ($excelData as $fingerId => $times) {
                $employee = $fingerMap->get((string)$fingerId);

                if (!$employee) {
                    $notFound[] = $fingerId;
                    continue;
                }

                $att = Attendance::firstOrNew([
                    'employee_id'     => $employee->id,
                    'attendance_date' => $date,
                ]);

                $att->shift_id       = $employee->shifts_types_id;
                $att->check_in_time  = $times['check_in'];
                $att->check_out_time = $times['check_out'];
                $att->status         = 1;
                $att->com_code       = $admin->com_code;
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

            // 2️⃣ الموظفون الغائبون عن الملف → غياب تلقائي
            if ($markAbsent) {
                foreach ($allEmployees as $emp) {
                    $fid = (string)$emp->finger_id;
                    if (array_key_exists($fid, $excelData)) continue;

                    $alreadyLogged = Attendance::where('employee_id', $emp->id)
                        ->where('attendance_date', $date)->exists();
                    if ($alreadyLogged) continue;

                    Attendance::create([
                        'employee_id'     => $emp->id,
                        'shift_id'        => $emp->shifts_types_id,
                        'attendance_date' => $date,
                        'check_in_time'   => null,
                        'check_out_time'  => null,
                        'status'          => 2,
                        'late_minutes'    => 0,
                        'overtime_hours'  => 0,
                        'overtime_amount' => 0,
                        'late_deduction'  => 0,
                        'notes'           => 'غياب تلقائي - Excel',
                        'com_code'        => $admin->com_code,
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
        if ($absent)          $msg .= " 🔴 $absent غياب تلقائي.";
        if (!empty($notFound)) $msg .= " ⚠️ Finger IDs غير معروفة: " . implode('، ', $notFound) . '.';

        return redirect()->route('attendance.index')->with('success', $msg);
    }

    // ─────────────────────────────────────────────
    //  تحويل وقت Excel إلى HH:MM
    // ─────────────────────────────────────────────
    private function parseTime($value): ?string
    {
        if ($value === null || $value === '') return null;

        // Excel numeric time fraction (e.g. 0.354166... = 08:30)
        if (is_numeric($value) && (float)$value < 1) {
            $secs    = round((float)$value * 86400);
            return sprintf('%02d:%02d', intdiv($secs, 3600), intdiv($secs % 3600, 60));
        }

        // نص مثل "08:30" أو "08:30:00" أو "2024-01-01 08:30"
        if (preg_match('/(\d{1,2}):(\d{2})/', (string)$value, $m)) {
            return sprintf('%02d:%02d', $m[1], $m[2]);
        }

        return null;
    }

    // =========================================================
    //  EDIT / UPDATE / DELETE
    // =========================================================
    public function edit(int $id)
    {
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
            $attendance->late_minutes = $attendance->overtime_hours =
            $attendance->overtime_amount = $attendance->late_deduction = 0;
        }

        $attendance->save();
        return redirect()->route('attendance.index')->with('success', 'تم تحديث سجل الحضور بنجاح');
    }

    public function delete(int $id)
    {
        Attendance::where('com_code', $this->comCode())->findOrFail($id)->delete();
        return redirect()->route('attendance.index')->with('success', 'تم حذف السجل بنجاح');
    }

    // =========================================================
    //  ملخص الموظف الشهري
    // =========================================================
    public function employeeSummary(Request $request, int $employeeId)
    {
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

    // =========================================================
    //  تحميل نموذج Excel فارغ
    // =========================================================
    public function excelTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('الحضور');

        // Headers
        $headers = ['A1' => 'Finger ID', 'B1' => 'وقت الحضور (HH:MM)', 'C1' => 'وقت الانصراف (HH:MM)'];
        foreach ($headers as $cell => $val) { $sheet->setCellValue($cell, $val); }

        // تنسيق
        $sheet->getStyle('A1:C1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                       'startColor' => ['rgb' => '2C3E50']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(25);

        // بيانات نموذجية للموظفين
        $employees = Employee::where('com_code', $this->comCode())
            ->whereNotNull('finger_id')->take(10)->get();

        $row = 2;
        foreach ($employees as $emp) {
            $sheet->setCellValue("A{$row}", $emp->finger_id);
            $sheet->setCellValue("B{$row}", '08:00');
            $sheet->setCellValue("C{$row}", '17:00');
            $row++;
        }

        // ملاحظة
        if ($employees->isEmpty()) {
            $sheet->setCellValue('A2', '1');
            $sheet->setCellValue('B2', '08:00');
            $sheet->setCellValue('C2', '17:00');
        }
        $sheet->setCellValue("A{$row}", '← ادخل Finger ID لكل موظف من بيانات الموظف في النظام');

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
}
