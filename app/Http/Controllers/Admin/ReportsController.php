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
use App\Exports\CommissionsExport;
use App\Exports\KpiReportExport;
use App\Exports\PayrollExport;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Advance;
use App\Models\EmployeeVacationBalance;
use App\Models\Finance_calender;
use App\Models\Department;
use App\Models\Branche;
use App\Models\Admin_panel_setting;
use App\Models\Commission;
use App\Models\KpiEmployeeScore;
use App\Models\KpiDefinition;
use App\Models\MonthlyPayroll;
use Illuminate\Support\Facades\Schema;

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
        $branches    = Branche::where('com_code', $this->comCode())->get();
        $years       = Finance_calender::where('com_code', $this->comCode())->orderBy('finance_yr','desc')->get();

        $orderColumn     = Schema::hasColumn('kpi_definitions', 'sort_order') ? 'sort_order' : 'id';
        $kpiDefs         = KpiDefinition::where('com_code', $this->comCode())->where('is_active', 1)->orderBy($orderColumn)->get();
        $commissionTypes = Commission::where('com_code', $this->comCode())
            ->whereNotNull('commission_type')->where('commission_type', '!=', '')
            ->distinct()->orderBy('commission_type')->pluck('commission_type');

        return view('admin.reports.index', compact('employees', 'departments', 'branches', 'years', 'kpiDefs', 'commissionTypes'));
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
        $filters = $request->only(['employee_id','from_date','to_date','branch_id','sort_by','status']);
        $format  = $request->input('format', 'excel');
        $sortBy  = $request->input('sort_by', 'date_desc');

        if ($format === 'excel') {
            $filename = 'advances_' . now()->format('Ymd_His') . '.xlsx';
            return Excel::download(new AdvancesExport($filters), $filename);
        }

        $query = Advance::with(['employee.branches'])->where('advances.com_code', $this->comCode());
        if (!empty($filters['employee_id'])) $query->where('advances.employee_id', $filters['employee_id']);
        if (!empty($filters['from_date']))   $query->where('advances.advance_date', '>=', $filters['from_date']);
        if (!empty($filters['to_date']))     $query->where('advances.advance_date', '<=', $filters['to_date']);
        if (!empty($filters['status']))      $query->where('advances.status', $filters['status']);
        if (!empty($filters['branch_id'])) {
            $query->whereHas('employee', fn($q) => $q->where('branches_id', $filters['branch_id']));
        }

        if (in_array($sortBy, ['name_asc', 'name_desc'])) {
            $dir = $sortBy === 'name_asc' ? 'asc' : 'desc';
            $query->join('employees', 'advances.employee_id', '=', 'employees.id')
                  ->orderBy('employees.employee_name_A', $dir)
                  ->select('advances.*');
        } elseif ($sortBy === 'date_asc') {
            $query->orderBy('advances.advance_date', 'asc');
        } else {
            $query->orderBy('advances.advance_date', 'desc');
        }

        $data     = $query->get();
        $branches = Branche::where('com_code', $this->comCode())->get();

        return view('admin.reports.advances_print', compact('data', 'filters', 'sortBy', 'branches'));
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
    //  تقرير الرواتب
    // ─────────────────────────────────────────────
    public function payroll(Request $request)
    {
        $filters = $request->only(['employee_id', 'month', 'year', 'status', 'branch_id', 'sort_by']);
        $format  = $request->input('format', 'excel');

        if ($format === 'excel') {
            $filename = 'payroll_' . now()->format('Ymd_His') . '.xlsx';
            return Excel::download(new PayrollExport($filters), $filename);
        }

        $query = MonthlyPayroll::with(['employee.branches'])
            ->where('monthly_payrolls.com_code', $this->comCode());

        if (!empty($filters['month']))       $query->where('month', $filters['month']);
        if (!empty($filters['year']))        $query->where('year',  $filters['year']);
        if (!empty($filters['status']))      $query->where('status', $filters['status']);
        if (!empty($filters['employee_id'])) $query->where('monthly_payrolls.employee_id', $filters['employee_id']);
        if (!empty($filters['branch_id'])) {
            $query->whereHas('employee', fn($q) => $q->where('branches_id', $filters['branch_id']));
        }

        $sort = $filters['sort_by'] ?? 'name_asc';
        match ($sort) {
            'net_desc'   => $query->orderBy('net_salary', 'desc'),
            'net_asc'    => $query->orderBy('net_salary', 'asc'),
            'gross_desc' => $query->orderBy('gross_salary', 'desc'),
            'gross_asc'  => $query->orderBy('gross_salary', 'asc'),
            'month_desc' => $query->orderBy('year', 'desc')->orderBy('month', 'desc'),
            'month_asc'  => $query->orderBy('year', 'asc')->orderBy('month', 'asc'),
            'name_desc'  => $query->join('employees', 'monthly_payrolls.employee_id', '=', 'employees.id')
                                  ->orderBy('employees.employee_name_A', 'desc')
                                  ->select('monthly_payrolls.*'),
            default      => $query->join('employees', 'monthly_payrolls.employee_id', '=', 'employees.id')
                                  ->orderBy('employees.employee_name_A', 'asc')
                                  ->select('monthly_payrolls.*'),
        };

        $data = $query->get();

        $employeeName = null;
        $branchName   = null;

        if (!empty($filters['employee_id'])) {
            $emp          = Employee::find($filters['employee_id']);
            $employeeName = $emp->employee_name_A ?? null;
        }
        if (!empty($filters['branch_id'])) {
            $branch     = Branche::find($filters['branch_id']);
            $branchName = $branch->branch_name ?? null;
        }

        return view('admin.reports.payroll_print', compact('data', 'filters', 'employeeName', 'branchName'));
    }

    // ─────────────────────────────────────────────
    //  تقرير العمولات
    // ─────────────────────────────────────────────
    public function commissions(Request $request)
    {
        $filters = $request->only(['employee_id', 'month', 'year', 'status', 'commission_type', 'sort_by']);
        $format  = $request->input('format', 'excel');

        if ($format === 'excel') {
            $filename = 'commissions_' . now()->format('Ymd_His') . '.xlsx';
            return Excel::download(new CommissionsExport($filters), $filename);
        }

        $query = Commission::with('employee')->where('com_code', $this->comCode());

        if (!empty($filters['employee_id']))     $query->where('employee_id',     $filters['employee_id']);
        if (!empty($filters['month']))           $query->where('month',           $filters['month']);
        if (!empty($filters['year']))            $query->where('year',            $filters['year']);
        if (!empty($filters['status']))          $query->where('status',          $filters['status']);
        if (!empty($filters['commission_type'])) $query->where('commission_type', $filters['commission_type']);

        match ($filters['sort_by'] ?? 'date_desc') {
            'amount_asc'  => $query->orderBy('amount', 'asc'),
            'amount_desc' => $query->orderBy('amount', 'desc'),
            'month_asc'   => $query->orderBy('year', 'asc')->orderBy('month', 'asc'),
            'month_desc'  => $query->orderBy('year', 'desc')->orderBy('month', 'desc'),
            'date_asc'    => $query->orderBy('commission_date', 'asc'),
            default       => $query->orderBy('commission_date', 'desc'),
        };

        $data = $query->get();

        $employeeName = null;
        if (!empty($filters['employee_id'])) {
            $emp          = Employee::find($filters['employee_id']);
            $employeeName = $emp->employee_name_A ?? null;
        }

        return view('admin.reports.commissions_print', compact('data', 'filters', 'employeeName'));
    }

    // ─────────────────────────────────────────────
    //  تقرير مؤشرات الأداء KPI
    // ─────────────────────────────────────────────
    public function kpiReport(Request $request)
    {
        $filters = $request->only(['employee_id', 'month', 'year', 'kpi_id', 'category', 'sort_by']);
        $format  = $request->input('format', 'excel');
        $month   = $filters['month'] ?? now()->month;
        $year    = $filters['year']  ?? now()->year;

        if ($format === 'excel') {
            $filename = 'kpi_report_' . now()->format('Ymd_His') . '.xlsx';
            return Excel::download(new KpiReportExport($filters), $filename);
        }

        $query = KpiEmployeeScore::with(['employee', 'kpi'])
            ->where('com_code', $this->comCode())
            ->where('month', $month)->where('year', $year);

        if (!empty($filters['employee_id'])) $query->where('employee_id', $filters['employee_id']);
        if (!empty($filters['kpi_id']))      $query->where('kpi_id', $filters['kpi_id']);
        if (!empty($filters['category'])) {
            $catIds = KpiDefinition::where('com_code', $this->comCode())
                ->where('category', $filters['category'])->pluck('id');
            $query->whereIn('kpi_id', $catIds);
        }

        $scores = $query->get();

        $byEmployee = $scores->groupBy('employee_id')->map(function ($empScores) {
            $emp = $empScores->first()->employee;
            return [
                'employee'        => $emp,
                'total_score'     => round($empScores->sum('score'), 2),
                'avg_achievement' => round($empScores->avg('achievement_pct'), 1),
                'total_bonus'     => $empScores->where('effect_direction', 1)->sum('salary_effect_amount'),
                'total_deduction' => $empScores->where('effect_direction', 2)->sum('salary_effect_amount'),
                'net_effect'      => $empScores->where('effect_direction', 1)->sum('salary_effect_amount')
                                   - $empScores->where('effect_direction', 2)->sum('salary_effect_amount'),
                'scores'          => $empScores,
            ];
        })->sortByDesc('total_score');

        $sortBy       = $filters['sort_by'] ?? 'score_desc';
        $byEmployee   = match ($sortBy) {
            'achievement_desc' => $byEmployee->sortByDesc('avg_achievement'),
            'achievement_asc'  => $byEmployee->sortBy('avg_achievement'),
            'name_asc'         => $byEmployee->sortBy(fn($d) => $d['employee']->employee_name_A ?? ''),
            'name_desc'        => $byEmployee->sortByDesc(fn($d) => $d['employee']->employee_name_A ?? ''),
            'score_asc'        => $byEmployee->sortBy('total_score'),
            default            => $byEmployee->sortByDesc('total_score'),
        };

        $employeeName = null;
        $kpiName      = null;
        if (!empty($filters['employee_id'])) {
            $emp          = Employee::find($filters['employee_id']);
            $employeeName = $emp->employee_name_A ?? null;
        }
        if (!empty($filters['kpi_id'])) {
            $kpi     = KpiDefinition::find($filters['kpi_id']);
            $kpiName = $kpi->name ?? null;
        }
        $category = $filters['category'] ?? null;

        return view('admin.reports.kpi_print', compact(
            'byEmployee', 'month', 'year', 'filters',
            'employeeName', 'kpiName', 'category'
        ));
    }

    // ─────────────────────────────────────────────
    private function applyAttendanceFilters($query, array $filters): void
    {
        if (!empty($filters['employee_id'])) $query->where('attendances.employee_id', $filters['employee_id']);
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
