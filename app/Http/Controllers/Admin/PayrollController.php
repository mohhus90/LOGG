<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MonthlyPayroll;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Advance;
use App\Models\Commission;
use App\Models\Deduction;
use App\Models\KpiEmployeeScore;
use App\Models\Admin_panel_setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    private function comCode(): int
    {
        return (int) Auth::guard('admin')->user()->com_code;
    }

    public function index(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year  = $request->year  ?? now()->year;

        $data = MonthlyPayroll::with('employee')
            ->where('month', $month)->where('year', $year)
            ->orderBy('employee_id')->paginate(20);

        return view('admin.payroll.index', compact('data', 'month', 'year'));
    }

    public function create()
    {
        $employees = Employee::where('com_code', $this->comCode())
            ->orderBy('employee_name_A')->get();
        return view('admin.payroll.create', compact('employees'));
    }

    // =========================================================
    //  احتساب راتب موظف واحد
    // =========================================================
    public function calculateSingle(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month'       => 'required|integer|between:1,12',
            'year'        => 'required|integer|min:2020',
            'period_from' => 'required|date',
            'period_to'   => 'required|date|after:period_from',
        ]);

        // ✅ FIX: فلترة بـ com_code لمنع الوصول لموظفي شركات أخرى
        $employee = Employee::where('com_code', $this->comCode())
            ->findOrFail($request->employee_id);

        $periodFrom = Carbon::parse($request->period_from);
        $periodTo   = Carbon::parse($request->period_to);

        $existing = MonthlyPayroll::where('employee_id', $employee->id)
            ->where('month', $request->month)->where('year', $request->year)->first();

        if ($existing && $existing->status != 1) {
            return back()->with('error', 'يوجد مسير راتب معتمد لهذا الموظف لهذا الشهر');
        }

        DB::beginTransaction();
        try {
            $payroll = $this->computePayroll(
                $employee, $request->month, $request->year, $periodFrom, $periodTo
            );

            if ($existing) {
                $existing->update((array)$payroll);
                $saved = $existing;
            } else {
                $saved = MonthlyPayroll::create((array)$payroll);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'خطأ: ' . $e->getMessage());
        }

        return redirect()->route('payroll.show', $saved->id)
            ->with('success', 'تم احتساب الراتب بنجاح');
    }

    // =========================================================
    //  احتساب رواتب جميع الموظفين دفعة واحدة
    // =========================================================
    public function calculateBulk(Request $request)
    {
        $request->validate([
            'month'       => 'required|integer|between:1,12',
            'year'        => 'required|integer|min:2020',
            'period_from' => 'required|date',
            'period_to'   => 'required|date|after:period_from',
        ]);

        $employees  = Employee::where('com_code', $this->comCode())->get();
        $periodFrom = Carbon::parse($request->period_from);
        $periodTo   = Carbon::parse($request->period_to);
        $count      = 0;

        DB::beginTransaction();
        try {
            foreach ($employees as $employee) {
                $payroll = $this->computePayroll(
                    $employee, $request->month, $request->year, $periodFrom, $periodTo
                );
                MonthlyPayroll::updateOrCreate(
                    ['employee_id' => $employee->id, 'month' => $request->month, 'year' => $request->year],
                    (array)$payroll
                );
                $count++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'خطأ: ' . $e->getMessage());
        }

        return redirect()->route('payroll.index', ['month' => $request->month, 'year' => $request->year])
            ->with('success', "تم احتساب رواتب $count موظف بنجاح");
    }

    // =========================================================
    //  منطق الاحتساب الفعلي
    // =========================================================
    private function computePayroll(
        Employee $employee, int $month, int $year,
        Carbon $periodFrom, Carbon $periodTo
    ): object {
        $admin     = Auth::guard('admin')->user();
        $settings  = Admin_panel_setting::where('com_code', (int)$admin->com_code)->first();
        $totalDays = $periodFrom->diffInDays($periodTo) + 1;
        $basicSal  = (float)($employee->emp_sal ?? 0);
        $dailyRate = $totalDays > 0 ? ($basicSal / $totalDays) : 0;
        $hourlyRate = $dailyRate / 8;

        // ── الحضور ──
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('attendance_date', [$periodFrom->toDateString(), $periodTo->toDateString()])
            ->get();

        $presentDays = $attendances->where('status', 1)->count();
        $absenceDays = $attendances->where('status', 2)->count();
        $leaveDays   = $attendances->whereIn('status', [3, 4, 5])->count();
        $earnedSal   = round($dailyRate * ($presentDays + $leaveDays), 2);

        // ── الأوفرتايم ──
        $overtimeAmount = round($attendances->sum('overtime_amount'), 2);

        // ── التأخيرات ──
        $lateDeductions = $this->calcLateDeductions($attendances, $settings, $dailyRate, $hourlyRate);

        // ── خصم الغياب ──
        $absenceDeductions = $this->calcAbsenceDeductions($absenceDays, $dailyRate, $settings);

        // ── العمولات ──
        $commissionsAmount = round(
            Commission::where('employee_id', $employee->id)
                ->where('month', $month)->where('year', $year)->where('status', 1)
                ->sum('amount'), 2
        );

        // ── الخصومات ──
        $deductionsAmount = round(
            Deduction::where('employee_id', $employee->id)
                ->where('month', $month)->where('year', $year)->where('status', 1)
                ->sum('amount'), 2
        );

        // ── السلفة ──
        $advance = Advance::where('employee_id', $employee->id)
            ->where('status', 1)->where('remaining_amount', '>', 0)->first();
        $advanceInstallment = $advance ? (float)$advance->monthly_installment : 0;

        // ── التأمينات ──
        $insurance = (float)($employee->emp_sal_insurance ?? 0);

        // ── الإضافات الثابتة ──
        $fixedAllowances = (float)($employee->emp_fixed_allowances ?? 0);

        // ── KPI ──
        $kpiScores    = KpiEmployeeScore::where('employee_id', $employee->id)
            ->where('month', $month)->where('year', $year)->get();
        $kpiBonus     = round($kpiScores->where('effect_direction', 1)->sum('salary_effect_amount'), 2);
        $kpiDeduction = round($kpiScores->where('effect_direction', 2)->sum('salary_effect_amount'), 2);

        // ── الإجمالي والصافي ──
        $grossSalary = $earnedSal + $fixedAllowances + $overtimeAmount + $commissionsAmount + $kpiBonus;
        $netSalary   = max(0, round(
            $grossSalary - $lateDeductions - $absenceDeductions
            - $deductionsAmount - $kpiDeduction - $advanceInstallment - $insurance, 2
        ));

        return (object)[
            'employee_id'         => $employee->id,
            'month'               => $month,
            'year'                => $year,
            'period_from'         => $periodFrom->toDateString(),
            'period_to'           => $periodTo->toDateString(),
            'total_days'          => $totalDays,
            'work_days'           => $presentDays,
            'absence_days'        => $absenceDays,
            'leave_days'          => $leaveDays,
            'basic_salary'        => $basicSal,
            'daily_rate'          => round($dailyRate, 4),
            'earned_salary'       => $earnedSal,
            'fixed_allowances'    => $fixedAllowances,
            'overtime_amount'     => $overtimeAmount,
            'commissions_amount'  => $commissionsAmount,
            'late_deductions'     => $lateDeductions,
            'absence_deductions'  => $absenceDeductions,
            'deductions_amount'   => $deductionsAmount,
            'advance_installment' => round($advanceInstallment, 2),
            'insurance_deduction' => round($insurance, 2),
            'gross_salary'        => round($grossSalary, 2),
            'net_salary'          => $netSalary,
            'status'              => 1,
            'com_code'            => (int)$admin->com_code,
            'added_by'            => $admin->id,
        ];
    }

    // ─────────────────────────────────────────────
    //  احتساب خصومات التأخير حسب وضع الضبط
    // ─────────────────────────────────────────────
    private function calcLateDeductions($attendances, $settings, float $dailyRate, float $hourlyRate): float
    {
        $mode = $settings->delay_calc_mode ?? 1;

        $graceMinutes = (float)($settings->after_minute_calc_delay ?? 0);
        $lateAttendances = $attendances->where('status', 1)->filter(function ($att) use ($graceMinutes) {
            return $att->late_minutes > $graceMinutes;
        });

        switch ($mode) {
            case 1: // خصم بالدقيقة
                $minuteRate = (float)($settings->sanctions_value_minute_delay ?? 0) > 0
                    ? (float)$settings->sanctions_value_minute_delay
                    : ($hourlyRate / 60);
                return round($lateAttendances->sum('late_minutes') * $minuteRate, 2);

            case 2: // نصف يوم / يوم بعد X مرة
                $count     = $lateAttendances->count();
                $halfAfter = (int)($settings->after_time_half_daycut ?? 0);
                $fullAfter = (int)($settings->after_time_allday_daycut ?? 0);

                if ($fullAfter > 0 && $count >= $fullAfter) return round($dailyRate, 2);
                if ($halfAfter > 0 && $count >= $halfAfter) return round($dailyRate / 2, 2);
                return 0.0;

            case 3: // مدمج
                $minuteRate = (float)($settings->sanctions_value_minute_delay ?? 0) > 0
                    ? (float)$settings->sanctions_value_minute_delay
                    : ($hourlyRate / 60);
                $totalLate = $lateAttendances->sum(function ($att) use ($graceMinutes) {
                    return max(0, $att->late_minutes - $graceMinutes);
                });
                return round($totalLate * $minuteRate, 2);

            default:
                return round($attendances->sum('late_deduction'), 2);
        }
    }

    private function calcAbsenceDeductions(int $absenceDays, float $dailyRate, $settings): float
    {
        if ($absenceDays <= 0) return 0.0;

        $s1 = (float)($settings->sanctions_value_first_abcence  ?? 1);
        $s2 = (float)($settings->sanctions_value_second_abcence ?? 2);
        $s3 = (float)($settings->sanctions_value_third_abcence  ?? 3);
        $s4 = (float)($settings->sanctions_value_forth_abcence  ?? 4);

        $deduction = 0.0;
        for ($i = 1; $i <= $absenceDays; $i++) {
            $multiplier = match (true) {
                $i === 1 => $s1,
                $i === 2 => $s2,
                $i === 3 => $s3,
                default  => $s4,
            };
            $deduction += $dailyRate * $multiplier;
        }
        return round($deduction, 2);
    }

    public function show(int $id)
    {
        $payroll = MonthlyPayroll::with('employee')->findOrFail($id);
        return view('admin.payroll.show', compact('payroll'));
    }

    public function approve(int $id)
    {
        $payroll = MonthlyPayroll::findOrFail($id);
        if ($payroll->status != 1) {
            return back()->with('error', 'لا يمكن اعتماد مسير غير في حالة مسودة');
        }

        $payroll->update(['status' => 2, 'updated_by' => Auth::guard('admin')->id()]);

        if ($payroll->advance_installment > 0) {
            $advance = Advance::where('employee_id', $payroll->employee_id)
                ->where('status', 1)->where('remaining_amount', '>', 0)->first();
            if ($advance) {
                $remaining = $advance->remaining_amount - $payroll->advance_installment;
                $advance->update([
                    'remaining_amount' => max(0, $remaining),
                    'status'           => $remaining <= 0 ? 2 : 1,
                ]);
            }
        }

        return back()->with('success', 'تم اعتماد مسير الراتب بنجاح');
    }

    public function delete(int $id)
    {
        $payroll = MonthlyPayroll::findOrFail($id);
        if ($payroll->status != 1) {
            return back()->with('error', 'لا يمكن حذف مسير معتمد أو مدفوع');
        }
        $payroll->delete();
        return redirect()->route('payroll.index')->with('success', 'تم حذف المسير');
    }
}