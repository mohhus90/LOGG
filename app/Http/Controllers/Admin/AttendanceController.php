<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Shifts_type;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

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
            'check_in_time'   => 'nullable|date_format:H:i',
            'check_out_time'  => 'nullable|date_format:H:i',
            'notes'           => 'nullable|string|max:500',
        ], [
            'employee_id.required'     => 'اختر الموظف',
            'attendance_date.required' => 'أدخل التاريخ',
        ]);

        $employee = Employee::findOrFail($request->employee_id);

        $attendance = new Attendance();
        $attendance->employee_id     = $request->employee_id;
        $attendance->shift_id        = $employee->shifts_types_id;
        $attendance->attendance_date = $request->attendance_date;
        $attendance->check_in_time   = $request->check_in_time;
        $attendance->check_out_time  = $request->check_out_time;
        $attendance->status          = $request->status;
        $attendance->notes           = $request->notes;
        $attendance->com_code        = Auth::guard('admin')->user()->com_code;
        $attendance->added_by        = Auth::guard('admin')->id();

        // احتساب التأخير والأوفرتايم
        if ($request->status == 1 && $request->check_in_time && $request->check_out_time) {
            $attendance->calculateDelayAndOvertime();

            // احتساب القيم المالية
            $dailyRate = $employee->emp_sal ? ($employee->emp_sal / 26) : 0; // 26 يوم عمل شهريًا
            $attendance->calculateAmounts($dailyRate);
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
                $attendance->calculateDelayAndOvertime();
                $dailyRate = $employee->emp_sal ? ($employee->emp_sal / 26) : 0;
                $attendance->calculateAmounts($dailyRate);
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
            'check_in_time'  => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
            'status'         => 'required|integer|between:1,5',
        ]);

        $attendance = Attendance::findOrFail($id);
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
