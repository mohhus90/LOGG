<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceExport;
use App\Exports\EmployeeExport;
use App\Exports\AdvancesExport;
use App\Exports\VacationsExport;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Advance;
use App\Models\EmployeeVacationBalance;
use App\Models\Finance_calender;
use App\Models\Department;
use App\Models\Admin_panel_setting;

class ReportsController extends Controller
{
    private function comCode(): int
    {
        return (int) Auth::guard('admin')->user()->com_code;
    }

    // ─────────────────────────────────────────────
    //  صفحة التقارير الرئيسية
    // ─────────────────────────────────────────────
    public function index()
    {
        $employees   = Employee::where('com_code', $this->comCode())->orderBy('employee_name_A')->get();
        $departments = Department::where('com_code', $this->comCode())->get();
        $years       = Finance_calender::where('com_code', $this->comCode())->orderBy('finance_yr','desc')->get();

        return view('admin.reports.index', compact('employees', 'departments', 'years'));
    }

    // ─────────────────────────────────────────────
    //  تقرير الحضور
    // ─────────────────────────────────────────────
    public function attendance(Request $request)
    {
        $filters = $request->only([
            'employee_id','from_date','to_date','status','department_id','sort_by',
        ]);
        $format  = $request->input('format', 'excel');
        $sortBy  = $request->input('sort_by', 'date_desc'); // date_desc | date_asc | name_asc | name_desc

        if ($format === 'excel') {
            $filename = 'attendance_' . now()->format('Ymd_His') . '.xlsx';
            return Excel::download(new AttendanceExport($filters, $sortBy), $filename);
        }

        // PDF — view قابل للطباعة
        $query = Attendance::with(['employee','shift','shiftOverride'])
            ->where('attendances.com_code', $this->comCode());
        $this->applyAttendanceFilters($query, $filters);
        $this->applyAttendanceSort($query, $sortBy);
        $data = $query->get();

        $settings = Admin_panel_setting::getByComCode($this->comCode());

        return view('admin.reports.attendance_print', compact('data', 'filters', 'settings', 'sortBy'));
    }

    // ─────────────────────────────────────────────
    //  تقرير الموظفين
    // ─────────────────────────────────────────────
    public function employees(Request $request)
    {
        $format = $request->input('format', 'excel');
        $deptId = $request->input('department_id');

        if ($format === 'excel') {
            $filename = 'employees_' . now()->format('Ymd_His') . '.xlsx';
            return Excel::download(new EmployeeExport(), $filename);
        }

        $query = Employee::with(['department','jobs_categories'])
            ->where('com_code', $this->comCode());
        if ($deptId) $query->where('emp_departments_id', $deptId);
        $data = $query->orderBy('employee_name_A')->get();

        return view('admin.reports.employees_print', compact('data'));
    }

    // ─────────────────────────────────────────────
    //  تقرير السلف
    // ─────────────────────────────────────────────
    public function advances(Request $request)
    {
        $filters = $request->only(['employee_id','from_date','to_date']);
        $format  = $request->input('format', 'excel');

        if ($format === 'excel') {
            $filename = 'advances_' . now()->format('Ymd_His') . '.xlsx';
            return Excel::download(new AdvancesExport($filters), $filename);
        }

        $query = Advance::with('employee')->where('com_code', $this->comCode());
        if (!empty($filters['employee_id'])) $query->where('employee_id', $filters['employee_id']);
        if (!empty($filters['from_date']))   $query->where('advance_date', '>=', $filters['from_date']);
        if (!empty($filters['to_date']))     $query->where('advance_date', '<=', $filters['to_date']);
        $data = $query->orderBy('advance_date','desc')->get();

        return view('admin.reports.advances_print', compact('data', 'filters'));
    }

    // ─────────────────────────────────────────────
    //  تقرير أرصدة الإجازات
    // ─────────────────────────────────────────────
    public function vacations(Request $request)
    {
        $filters = $request->only(['employee_id']);
        $format  = $request->input('format', 'excel');

        if ($format === 'excel') {
            $filename = 'vacations_' . now()->format('Ymd_His') . '.xlsx';
            return Excel::download(new VacationsExport($filters), $filename);
        }

        $query = EmployeeVacationBalance::with('employee')->where('com_code', $this->comCode());
        if (!empty($filters['employee_id'])) $query->where('employee_id', $filters['employee_id']);
        $data = $query->orderBy('employee_id')->get();

        return view('admin.reports.vacations_print', compact('data', 'filters'));
    }

    // ─────────────────────────────────────────────
    private function applyAttendanceFilters($query, array $filters): void
    {
        if (!empty($filters['employee_id'])) $query->where('employee_id', $filters['employee_id']);
        if (!empty($filters['from_date']))   $query->where('attendance_date', '>=', $filters['from_date']);
        if (!empty($filters['to_date']))     $query->where('attendance_date', '<=', $filters['to_date']);
        if (isset($filters['status']) && $filters['status'] !== '') $query->where('status', $filters['status']);
        if (!empty($filters['department_id'])) {
            $query->whereHas('employee', fn($q) => $q->where('emp_departments_id', $filters['department_id']));
        }
    }

    private function applyAttendanceSort($query, string $sortBy): void
    {
        match ($sortBy) {
            'date_asc'   => $query->orderBy('attendance_date', 'asc'),
            'name_asc'   => $query->join('employees', 'attendances.employee_id', '=', 'employees.id')
                                  ->orderBy('employees.employee_name_A', 'asc')
                                  ->orderBy('attendance_date', 'asc')
                                  ->select('attendances.*'),
            'name_desc'  => $query->join('employees', 'attendances.employee_id', '=', 'employees.id')
                                  ->orderBy('employees.employee_name_A', 'desc')
                                  ->orderBy('attendance_date', 'asc')
                                  ->select('attendances.*'),
            default      => $query->orderBy('attendance_date', 'desc'),
        };
    }
}
