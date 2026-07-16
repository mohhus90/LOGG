<?php

namespace App\Http\Controllers\Admin\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MonthlyPayroll;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\PayrollFactor;
use App\Models\Advance;
use App\Models\AdvanceDeductionLog;
use App\Models\Commission;
use App\Models\Deduction;
use App\Models\KpiEmployeeScore;
use App\Models\Bonus;
use App\Models\EmployeeSanction;
use App\Models\Admin_panel_setting;
use App\Models\IncomeTaxBracket;
use App\Services\SmsService;
use App\Services\Accounting\JournalPostingService;
use App\Exports\PayrollDisbursementExport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class PayrollController extends Controller
{
    private function comCode(): int
    {
        return (int) Auth::guard('admin')->user()->com_code;
    }

    // خلال أول 5 أيام من الشهر يكون المسير المقصود عادةً هو شهر سابق (يُقفل متأخراً)
    private function defaultPeriod(): array
    {
        $today = now();
        $base  = $today->day <= 5 ? $today->copy()->subMonthNoOverflow() : $today->copy();

        return [
            'month'       => $base->month,
            'year'        => $base->year,
            'period_from' => $base->copy()->startOfMonth()->format('Y-m-d'),
            'period_to'   => $base->copy()->endOfMonth()->format('Y-m-d'),
        ];
    }

    public function index(Request $request)
    {
        $default = $this->defaultPeriod();

        if ($request->has('month') || $request->has('year')) {
            $month = (int) ($request->month ?? $default['month']);
            $year  = (int) ($request->year  ?? $default['year']);
            session(['payroll_index_month' => $month, 'payroll_index_year' => $year]);
        } else {
            $month = (int) session('payroll_index_month', $default['month']);
            $year  = (int) session('payroll_index_year', $default['year']);
        }

        $data = MonthlyPayroll::with('employee')
            ->where('month', $month)->where('year', $year)
            ->orderBy('employee_id')->paginate(20);

        $clients = \App\Models\Client::where('com_code', $this->comCode())
            ->orderBy('client_name')->get();

        return view('admin.payroll.index', compact('data', 'month', 'year', 'clients'));
    }

    public function create()
    {
        $employees = Employee::where('com_code', $this->comCode())
            ->orderBy('employee_name_A')->get();
        $default = $this->defaultPeriod();
        return view('admin.payroll.create', compact('employees', 'default'));
    }

    // ملف صرف الرواتب: شيت لكل بنك + شيت Cash + شيت الموقوفين عن الصرف
    public function disbursement(Request $request)
    {
        $request->validate([
            'month'      => 'required|integer|between:1,12',
            'year'       => 'required|integer|min:2020',
            'client_id'  => 'nullable|exists:clients,id',
        ]);

        $export = new PayrollDisbursementExport([
            'month'     => (int) $request->month,
            'year'      => (int) $request->year,
            'client_id' => $request->client_id ?: null,
        ]);

        $fileName = "payroll_disbursement_{$request->year}_{$request->month}.xlsx";
        return Excel::download($export, $fileName);
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

        // منع احتساب راتب لموظف غير نشط في شهر لاحق لشهر استقالته
        if ($employee->functional_status == 2 && $employee->resignation_date) {
            $resignYear  = (int) Carbon::parse($employee->resignation_date)->year;
            $resignMonth = (int) Carbon::parse($employee->resignation_date)->month;
            $reqYear     = (int) $request->year;
            $reqMonth    = (int) $request->month;
            if ($reqYear > $resignYear || ($reqYear === $resignYear && $reqMonth > $resignMonth)) {
                return back()->with('error', 'لا يمكن احتساب راتب موظف ترك العمل في شهر سابق للكشف المحدد');
            }
        }

        $periodFrom = Carbon::parse($request->period_from);
        $periodTo   = Carbon::parse($request->period_to);

        $existing = MonthlyPayroll::where('employee_id', $employee->id)
            ->where('month', $request->month)->where('year', $request->year)->first();

        if ($existing && $existing->status != 1) {
            return back()->with('error', 'يوجد كشف راتب معتمد لهذا الموظف لهذا الشهر');
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

        $payMonth  = (int) $request->month;
        $payYear   = (int) $request->year;
        $employees = Employee::where('com_code', $this->comCode())
            ->where(function ($q) use ($payMonth, $payYear) {
                $q->where('functional_status', 1)
                  ->orWhere(function ($q2) use ($payMonth, $payYear) {
                      $q2->where('functional_status', 2)
                         ->whereNotNull('resignation_date')
                         ->whereYear('resignation_date', $payYear)
                         ->whereMonth('resignation_date', $payMonth);
                  });
            })
            ->get();
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

        // ── العملاء الخارجيون (Klivvr, Opay, ...) يُحسبون من مؤثرات شهرية مُدخلة/مستوردة
        // بدلاً من بصمة الحضور اليومية، لأن هذه بيانات غير متوفرة لديهم أصلاً ──
        $payrollFactor = PayrollFactor::where('employee_id', $employee->id)
            ->where('month', $month)->where('year', $year)->first();
        if ($payrollFactor) {
            return $this->computePayrollFromFactors(
                $employee, $payrollFactor, $month, $year, $periodFrom, $periodTo, $admin, $settings
            );
        }

        $totalDays = $periodFrom->diffInDays($periodTo) + 1;
        $basicSal  = (float)($employee->emp_sal ?? 0);
        $dailyRate = $totalDays > 0 ? ($basicSal / $totalDays) : 0;
        $hourlyRate = $dailyRate / 8;

        // ── الحضور (نستبعد سجلات ما قبل تاريخ التعيين من جميع الحسابات) ──
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('attendance_date', [$periodFrom->toDateString(), $periodTo->toDateString()])
            ->where('is_before_hire', 0)
            ->when($employee->emp_start_date, fn($q) => $q->where('attendance_date', '>=', $employee->emp_start_date))
            ->get();

        $presentDays   = $attendances->where('status', 1)->count();
        $absentRecords = $attendances->where('status', 2)->sortBy('attendance_date');
        $absenceDays   = $absentRecords->count();
        $leaveDays     = $attendances->whereIn('status', [3, 4, 5])->count();
        $weeklyOffDays = $attendances->where('status', 6)->count();
        // ملاحظة: أيام الغياب تُحسب هنا ضمن الراتب المستحق (كامل الراتب الأساسي)
        // لأن خصمها الفعلي (بما فيه المضاعف عند التكرار) يتم بالكامل عبر
        // absence_deductions أدناه. استبعادها من هنا كان يسبب خصم يوم الغياب
        // مرتين: مرة ضمنياً هنا ومرة أخرى صراحة في قسم الخصومات.
        $earnedSal     = round($dailyRate * ($presentDays + $leaveDays + $weeklyOffDays + $absenceDays), 2);

        // ── بدل العمل في الإجازة الأسبوعية ──
        $leaveCompensation = round($attendances->sum('leave_compensation_amount'), 2);

        // ── الأوفرتايم ──
        $overtimeAmount = round($attendances->sum('overtime_amount'), 2);

        // ── التأخيرات (تشمل خصم حل البصمة الناقصة دائماً) ──
        $lateDeductions = $this->calcLateDeductions($attendances, $settings, $dailyRate, $hourlyRate)
            + round($attendances->sum('missing_punch_deduction'), 2);

        // ── خصم الغياب (يستخدم الخصم المخصص لكل سجل إن وُجد) ──
        $absenceDeductions = $this->calcAbsenceDeductions($absentRecords, $dailyRate, $settings);

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

        // ── السلف (تُجمع كل السلف النشطة، وليس أول سلفة فقط) ──
        $activeAdvances = Advance::where('employee_id', $employee->id)
            ->where('status', 1)->where('remaining_amount', '>', 0)->get();
        $advanceInstallment = $activeAdvances->sum(
            fn($a) => min((float)$a->monthly_installment, (float)$a->remaining_amount)
        );

        // ── الجزاءات (خصم مالي / خصم باليوم / إيقاف عن العمل) ──
        $sanctionsDeduction = $this->calcSanctionsDeduction($employee, $month, $year, $periodFrom, $periodTo, $dailyRate);

        // ── التأمينات الاجتماعية (نسبة الموظف + نسبة الشركة) ──
        $insuranceBase       = (float)($employee->emp_sal_insurance ?? 0);
        $empInsuranceRate    = (float)($settings->employee_insurance_rate ?? 11.00);
        $comInsuranceRate    = (float)($settings->company_insurance_rate  ?? 18.75);
        $insurance           = $insuranceBase > 0 ? round($insuranceBase * $empInsuranceRate / 100, 2) : 0;
        $companyInsurance    = $insuranceBase > 0 ? round($insuranceBase * $comInsuranceRate  / 100, 2) : 0;
        $totalInsurance      = round($insurance + $companyInsurance, 2);

        // ── الإضافات الثابتة ──
        $fixedAllowances = (float)($employee->emp_fixed_allowances ?? 0);

        // ── ضريبة كسب العمل (اختيارية لكل موظف عبر apply_income_tax) ──
        $incomeTax = 0.0;
        if ($employee->apply_income_tax) {
            $exemption   = (float)($settings->income_tax_exemption_monthly ?? 0);
            $taxableBase = max(0, ($earnedSal + $fixedAllowances + $overtimeAmount) - $insurance - $exemption);
            $incomeTax   = $this->calcMonthlyIncomeTax($this->comCode(), $taxableBase);
        }

        // ── KPI ──
        $kpiScores    = KpiEmployeeScore::where('employee_id', $employee->id)
            ->where('month', $month)->where('year', $year)->get();
        $kpiBonus     = round($kpiScores->where('effect_direction', 1)->sum('salary_effect_amount'), 2);
        $kpiDeduction = round($kpiScores->where('effect_direction', 2)->sum('salary_effect_amount'), 2);

        // ── المكافآت ──
        $bonusRecords  = Bonus::where('employee_id', $employee->id)
            ->where('month', $month)->where('year', $year)->where('status', 1)->get();
        $bonusesAmount = round($bonusRecords->sum(function ($b) use ($dailyRate) {
            return $b->calcAmount($dailyRate);
        }), 2);

        // ── الإجمالي والصافي ──
        $grossSalary = $earnedSal + $fixedAllowances + $overtimeAmount + $commissionsAmount
            + $kpiBonus + $bonusesAmount + $leaveCompensation;
        $netSalary   = max(0, round(
            $grossSalary - $lateDeductions - $absenceDeductions
            - $deductionsAmount - $kpiDeduction - $advanceInstallment - $insurance - $sanctionsDeduction - $incomeTax, 2
        ));

        $result = [
            'employee_id'         => $employee->id,
            'month'               => $month,
            'year'                => $year,
            'period_from'         => $periodFrom->toDateString(),
            'period_to'           => $periodTo->toDateString(),
            'total_days'          => $totalDays,
            'work_days'           => $presentDays,
            'absence_days'        => $absenceDays,
            'leave_days'          => $leaveDays,
            'weekly_off_days'     => $weeklyOffDays,
            'basic_salary'        => $basicSal,
            'daily_rate'          => round($dailyRate, 4),
            'earned_salary'       => $earnedSal,
            'fixed_allowances'    => $fixedAllowances,
            'overtime_amount'     => $overtimeAmount,
            'commissions_amount'  => $commissionsAmount,
            'bonuses_amount'      => $bonusesAmount,
            'leave_compensation_amount' => $leaveCompensation,
            'kpi_bonus_amount'    => $kpiBonus,
            'kpi_deduction_amount' => $kpiDeduction,
            'late_deductions'     => $lateDeductions,
            'absence_deductions'  => $absenceDeductions,
            'deductions_amount'   => $deductionsAmount,
            'advance_installment' => round($advanceInstallment, 2),
            'insurance_deduction' => $insurance,
            'income_tax_deduction' => $incomeTax,
            'sanctions_deduction' => round($sanctionsDeduction, 2),
            'gross_salary'        => round($grossSalary, 2),
            'net_salary'          => $netSalary,
            'status'              => 1,
            'is_held'             => false,
            'hold_reason'         => null,
            'com_code'            => (int)$admin->com_code,
            'added_by'            => $admin->id,
        ];

        if (\Illuminate\Support\Facades\Schema::hasColumn('monthly_payrolls', 'company_insurance_contribution')) {
            $result['company_insurance_contribution'] = $companyInsurance;
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('monthly_payrolls', 'total_insurance')) {
            $result['total_insurance'] = $totalInsurance;
        }

        return (object)$result;
    }

    // ─────────────────────────────────────────────
    //  احتساب راتب عملاء الأوت سورسينج (Klivvr, Opay, ...) من المؤثرات الشهرية
    //  المستوردة، بنفس منطق شيت "Payroll tax" المُعتمد لديهم: القسمة دائمًا
    //  على 30 (أو 240 للساعات) بغض النظر عن عدد أيام الشهر الفعلي، والضريبة
    //  تُحسب بتسنين الوعاء الشهري ×12 ثم توزيعه على الشرائح ثم القسمة على 12
    //  (انظر calcMonthlyIncomeTax) — على عكس مسار الحضور اليومي الذي يحسب
    //  الوعاء الشهري مباشرة.
    // ─────────────────────────────────────────────
    private function computePayrollFromFactors(
        Employee $employee, PayrollFactor $f, int $month, int $year,
        Carbon $periodFrom, Carbon $periodTo, $admin, $settings
    ): object {
        $fixedSalary = (float)($employee->emp_sal ?? 0);
        $totalDays   = $periodFrom->diffInDays($periodTo) + 1;

        if ($f->is_held) {
            return (object)[
                'employee_id' => $employee->id, 'month' => $month, 'year' => $year,
                'period_from' => $periodFrom->toDateString(), 'period_to' => $periodTo->toDateString(),
                'total_days' => $totalDays, 'work_days' => 0, 'absence_days' => 0,
                'leave_days' => 0, 'weekly_off_days' => 0,
                'basic_salary' => $fixedSalary, 'daily_rate' => 0,
                'earned_salary' => 0, 'fixed_allowances' => 0, 'overtime_amount' => 0,
                'commissions_amount' => 0, 'bonuses_amount' => 0, 'leave_compensation_amount' => 0,
                'kpi_bonus_amount' => 0, 'kpi_deduction_amount' => 0,
                'late_deductions' => 0, 'absence_deductions' => 0, 'deductions_amount' => 0,
                'advance_installment' => 0, 'insurance_deduction' => 0, 'income_tax_deduction' => 0,
                'sanctions_deduction' => 0, 'gross_salary' => 0, 'net_salary' => 0,
                'status' => 1, 'is_held' => true, 'hold_reason' => $f->hold_reason,
                'com_code' => (int)$admin->com_code, 'added_by' => $admin->id,
            ];
        }

        // ── الاستحقاقات (القسمة دائمًا على 30، كما فى شيت العميل) ──
        $earnedSal = round($fixedSalary / 30 * (float)$f->working_days, 2);

        // ── الأوفرتايم/التسويات: يظهران فى نفس بند "Overtime (+)" لدى العميل ──
        $overtimeAmount = round(
            ($fixedSalary / 240 * (float)$f->settlement_hours)
            + ($fixedSalary / 30 * (float)$f->settlement_days)
            + (float)$f->settlement_amount, 2
        );

        $fixedAllowances = (float)($employee->emp_fixed_allowances ?? 0);
        $bonusesAmount   = round((float)$f->bonus_amount, 2);
        $otherAllowance  = round((float)$f->other_allowance, 2);

        // ── الخصم على أساس الأيام/الساعات (٪30 أو ٪240 ثابتة، وليس أيام الفترة الفعلية) ──
        $factorsDeduction = $this->calcFactorsDeduction($f, $fixedSalary);

        // ── التأمينات الاجتماعية ──
        $insuranceBase    = (float)($employee->emp_sal_insurance ?? 0);
        $empInsuranceRate = (float)($settings->employee_insurance_rate ?? 11.00);
        $comInsuranceRate = (float)($settings->company_insurance_rate  ?? 18.75);
        $insurance        = $insuranceBase > 0 ? round($insuranceBase * $empInsuranceRate / 100, 2) : 0;
        $companyInsurance = $insuranceBase > 0 ? round($insuranceBase * $comInsuranceRate  / 100, 2) : 0;
        $totalInsurance   = round($insurance + $companyInsurance, 2);

        // ── ضريبة كسب العمل (اختيارية لكل موظف عبر apply_income_tax) ──
        // ملحوظة: "Penalties/other_deduction" لا تُطرح من وعاء الضريبة فى شيت العميل
        // (تُطرح فقط من صافى الراتب النهائى أدناه)، بعكس factorsDeduction والدمغة الشهرية
        $incomeTax = 0.0;
        if ($employee->apply_income_tax) {
            $exemption       = (float)($settings->income_tax_exemption_monthly ?? 0);
            $grossAllowances = $earnedSal + $bonusesAmount + $overtimeAmount + $otherAllowance;
            $taxableBase     = max(0, $grossAllowances - $exemption - $insurance
                - (float)$f->monthly_stamp_tax - $factorsDeduction);
            $incomeTax = $this->calcMonthlyIncomeTax($this->comCode(), $taxableBase);
        }

        $grossSalary = $earnedSal + $fixedAllowances + $overtimeAmount + $bonusesAmount + $otherAllowance;

        // ── صندوق دعم أسر الشهداء والمصابين: 0.05% من إجمالي المستحقات (قانوني) ──
        $martyrsFund = round($grossSalary * 0.0005, 2);

        $netSalary   = max(0, round(
            $grossSalary - $factorsDeduction - (float)$f->other_deduction
            - (float)$f->monthly_stamp_tax - $insurance - $incomeTax - $martyrsFund, 0
        ));

        $result = [
            'employee_id'         => $employee->id,
            'month'               => $month,
            'year'                => $year,
            'period_from'         => $periodFrom->toDateString(),
            'period_to'           => $periodTo->toDateString(),
            'total_days'          => $totalDays,
            'work_days'           => (int)round($f->working_days),
            'absence_days'        => (int)round($f->absence_hours / 8),
            'leave_days'          => (int)round($f->leave_days + $f->unpaid_leave_days + $f->sick_leave_days),
            'weekly_off_days'     => 0,
            'basic_salary'        => $fixedSalary,
            'daily_rate'          => round($fixedSalary / 30, 4),
            'earned_salary'       => $earnedSal,
            'fixed_allowances'    => round($fixedAllowances + $otherAllowance, 2),
            'overtime_amount'     => $overtimeAmount,
            'commissions_amount'  => 0,
            'bonuses_amount'      => $bonusesAmount,
            'leave_compensation_amount' => 0,
            'kpi_bonus_amount'    => 0,
            'kpi_deduction_amount' => 0,
            'late_deductions'     => 0,
            'absence_deductions'  => $factorsDeduction,
            'deductions_amount'   => round((float)$f->other_deduction + (float)$f->monthly_stamp_tax + $martyrsFund, 2),
            'advance_installment' => 0,
            'insurance_deduction' => $insurance,
            'income_tax_deduction' => $incomeTax,
            'sanctions_deduction' => 0,
            'gross_salary'        => round($grossSalary, 2),
            'net_salary'          => $netSalary,
            'status'              => 1,
            'is_held'             => false,
            'hold_reason'         => null,
            'com_code'            => (int)$admin->com_code,
            'added_by'            => $admin->id,
        ];

        if (\Illuminate\Support\Facades\Schema::hasColumn('monthly_payrolls', 'company_insurance_contribution')) {
            $result['company_insurance_contribution'] = $companyInsurance;
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('monthly_payrolls', 'total_insurance')) {
            $result['total_insurance'] = $totalInsurance;
        }

        return (object)$result;
    }

    // خصم الأيام/الساعات الثابت المستخدم لدى عملاء الأوت سورسينج (بدلاً من مضاعفات
    // التكرار 1×/2×/3×/4× المستخدمة لموظفي الشركة أنفسهم عبر calcAbsenceDeductions)
    private function calcFactorsDeduction(PayrollFactor $f, float $fixedSalary): float
    {
        $days = (float)$f->leave_days + (float)$f->no_show_days + (float)$f->unpaid_leave_days
            + (float)$f->sick_leave_days + (float)$f->sick_leave_balance + (float)$f->penalty_days;

        return round(($fixedSalary / 30 * $days) + ($fixedSalary / 240 * (float)$f->absence_hours), 2);
    }

    // تُسنّن الوعاء الشهري (×12، وتُقرَّب لأقرب 10 جنيه)، تحسب الضريبة على الشرائح
    // السنوية المُعرّفة فى income_tax_brackets، ثم تُقسّم الناتج على 12
    private function calcMonthlyIncomeTax(int $comCode, float $monthlyTaxableBase): float
    {
        if ($monthlyTaxableBase <= 0) return 0.0;

        $annualBase = floor(($monthlyTaxableBase * 12) / 10) * 10;
        $annualTax  = IncomeTaxBracket::calcTax($comCode, $annualBase);

        return round($annualTax / 12, 0);
    }

    // ─────────────────────────────────────────────
    //  احتساب خصومات التأخير حسب وضع الضبط
    //
    //  ملاحظة مهمة: late_minutes المخزّنة على سجل الحضور محسوبة بالفعل
    //  بعد خصم فترة السماح (after_minute_calc_delay) مرة واحدة عند حفظ/تعديل
    //  السجل (انظر Attendance::calculateDelayAndOvertime). ونفس السجل يحمل
    //  late_deduction محسوبة فعلياً بصيغة الإعدادات الصحيحة (مضاعف الدقيقة ×
    //  سعر الدقيقة المشتق من الراتب، بمقسوم الساعة المضبوط للشركة) عبر
    //  Attendance::calculateAmounts(). لذلك لا داعي لإعادة حساب خصم التأخير
    //  من الصفر هنا لوضعي 1 و3 (وهو ما كان يُسبب طرح السماح مرتين ويُخطئ في
    //  تفسير "مضاعف خصم الدقيقة" كسعر مطلق) — يكفي تجميع القيمة المحسوبة
    //  مسبقاً لكل سجل حتى تتطابق شاشة الحضور مع مسير الرواتب دائماً.
    // ─────────────────────────────────────────────
    private function calcLateDeductions($attendances, $settings, float $dailyRate, float $hourlyRate): float
    {
        $mode = (int) ($settings->delay_calc_mode ?? 1);

        if ($mode === 2) {
            // نصف يوم / يوم بعد X مرة تأخير — يُحسب على عدد مرات التأخير الفعلي
            // (late_minutes > 0 لأن السماح مطروح منها بالفعل مرة واحدة)
            $count     = $attendances->where('status', 1)->filter(fn($att) => $att->late_minutes > 0)->count();
            $halfAfter = (int) ($settings->after_time_half_daycut ?? 0);
            $fullAfter = (int) ($settings->after_time_allday_daycut ?? 0);

            if ($fullAfter > 0 && $count >= $fullAfter) return round($dailyRate, 2);
            if ($halfAfter > 0 && $count >= $halfAfter) return round($dailyRate / 2, 2);
            return 0.0;
        }

        // الأوضاع 1 و3 والافتراضي: مجموع خصومات التأخير المحسوبة فعلياً لكل سجل حضور
        return round($attendances->sum('late_deduction'), 2);
    }

    private function calcAbsenceDeductions(\Illuminate\Support\Collection $absentRecords, float $dailyRate, $settings): float
    {
        if ($absentRecords->isEmpty()) return 0.0;

        $s1 = (float)($settings->sanctions_value_first_abcence  ?? 1);
        $s2 = (float)($settings->sanctions_value_second_abcence ?? 2);
        $s3 = (float)($settings->sanctions_value_third_abcence  ?? 3);
        $s4 = (float)($settings->sanctions_value_forth_abcence  ?? 4);

        $deduction = 0.0;
        $seqIndex  = 0;

        foreach ($absentRecords as $record) {
            if ($record->absence_deduction_days !== null) {
                // استخدام الخصم المخصص لهذا السجل
                $deduction += $dailyRate * (float)$record->absence_deduction_days;
            } else {
                // الصيغة التسلسلية من الضبط العام
                $seqIndex++;
                $multiplier = match (true) {
                    $seqIndex === 1 => $s1,
                    $seqIndex === 2 => $s2,
                    $seqIndex === 3 => $s3,
                    default         => $s4,
                };
                $deduction += $dailyRate * $multiplier;
            }
        }

        return round($deduction, 2);
    }

    // ─────────────────────────────────────────────
    //  احتساب خصم الجزاءات (خصم مالي / خصم باليوم / إيقاف عن العمل)
    //  ملاحظة: النوعان 3 و5 يخزّنان شهر الاستقطاع في نفس عمود deduct_month
    // ─────────────────────────────────────────────
    private function calcSanctionsDeduction(
        Employee $employee, int $month, int $year,
        Carbon $periodFrom, Carbon $periodTo, float $dailyRate
    ): float {
        $deductMonth = sprintf('%04d-%02d', $year, $month);

        // النوع 3: خصم مالي مباشر — النوع 5: خصم بعدد أيام × سعر اليوم
        $monthlyDeduction = EmployeeSanction::where('employee_id', $employee->id)
            ->where('status', 1)
            ->where('deduct_month', $deductMonth)
            ->whereIn('type', [3, 5])
            ->get()
            ->sum(fn($s) => (int)$s->type === 5
                ? (float)$s->deduct_days * $dailyRate
                : (float)$s->amount);

        // النوع 4: إيقاف عن العمل — يُخصم بحساب تداخل أيام الإيقاف مع فترة الكشف
        $suspensions = EmployeeSanction::where('employee_id', $employee->id)
            ->where('status', 1)->where('type', 4)
            ->whereNotNull('date')->where('suspension_days', '>', 0)
            ->get();

        $suspensionDeduction = 0.0;
        foreach ($suspensions as $sanction) {
            $suspStart = Carbon::parse($sanction->date);
            $suspEnd   = $suspStart->copy()->addDays(max(0, (int)$sanction->suspension_days - 1));

            $overlapStart = $suspStart->greaterThan($periodFrom) ? $suspStart : $periodFrom->copy();
            $overlapEnd   = $suspEnd->lessThan($periodTo) ? $suspEnd : $periodTo->copy();

            if ($overlapEnd->greaterThanOrEqualTo($overlapStart)) {
                $overlapDays = $overlapStart->diffInDays($overlapEnd) + 1;
                $suspensionDeduction += $overlapDays * $dailyRate;
            }
        }

        return round($monthlyDeduction + $suspensionDeduction, 2);
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
            return back()->with('error', 'لا يمكن اعتماد كشف غير في حالة مسودة');
        }

        DB::transaction(function () use ($payroll) {
            $payroll->update(['status' => 2, 'updated_by' => Auth::guard('admin')->id()]);
            $this->postPayrollJournal($payroll);

            if ($payroll->advance_installment > 0) {
                $activeAdvances = Advance::where('employee_id', $payroll->employee_id)
                    ->where('status', 1)->where('remaining_amount', '>', 0)->get();

                foreach ($activeAdvances as $advance) {
                    $installment = min((float)$advance->monthly_installment, (float)$advance->remaining_amount);
                    if ($installment <= 0) continue;

                    $remaining = $advance->remaining_amount - $installment;
                    $advance->update([
                        'remaining_amount' => max(0, $remaining),
                        'status'           => $remaining <= 0 ? 2 : 1,
                    ]);

                    AdvanceDeductionLog::create([
                        'advance_id'         => $advance->id,
                        'monthly_payroll_id' => $payroll->id,
                        'amount'             => $installment,
                    ]);
                }
            }
        });

        // SMS إشعار الراتب
        $employee = $payroll->employee;
        if ($employee && $employee->emp_mobile) {
            try {
                (new SmsService($this->comCode()))->sendPayrollApproved(
                    $employee->emp_mobile,
                    $employee->employee_name_A,
                    (float)$payroll->net_salary,
                    (int)$payroll->month,
                    (int)$payroll->year
                );
            } catch (\Exception $e) {
                Log::warning('SMS payroll failed: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'تم اعتماد كشف الراتب بنجاح');
    }

    /** ترحيل قيد استحقاق الراتب عند الاعتماد (Phase 3): مصروف رواتب مقابل رواتب مستحقة */
    private function postPayrollJournal(MonthlyPayroll $payroll): void
    {
        $employee = $payroll->employee;
        $comCode  = (int) ($employee->com_code ?? $this->comCode());
        if (JournalPostingService::alreadyPosted($comCode, 'payroll', $payroll->id)) {
            return;
        }
        if ((float) $payroll->net_salary <= 0) {
            return;
        }

        // مصروف الرواتب يُحمَّل بالصافي قبل ضريبة كسب العمل تحديدًا (نفس قيمة net_salary
        // القديمة قبل إضافة هذا الخصم) — الضريبة نفسها تُفصل كالتزام مستقل تجاه المصلحة
        // بدل تخفيض مصروف الشركة، بينما صافي المستحق للموظف (SALARY_PAYABLE) يبقى بعد خصمها.
        $incomeTax   = (float) $payroll->income_tax_deduction;
        $expenseBase = (float) $payroll->net_salary + $incomeTax;

        $lines = [
            ['role' => 'SALARY_EXPENSE', 'debit' => $expenseBase, 'credit' => 0],
            ['role' => 'SALARY_PAYABLE', 'debit' => 0, 'credit' => $payroll->net_salary, 'party_type' => 'employee', 'party_id' => $payroll->employee_id],
        ];
        if ($incomeTax > 0) {
            $lines[] = ['role' => 'INCOME_TAX_PAYABLE', 'debit' => 0, 'credit' => $incomeTax];
        }

        JournalPostingService::post('payroll_approved', $comCode, $lines, [
            'source_module' => 'payroll',
            'source_id'     => $payroll->id,
            'entry_date'    => $payroll->period_to ?? now(),
            'reference'     => sprintf('%02d-%04d', $payroll->month, $payroll->year),
            'description'   => 'مسير رواتب '.($employee->employee_name_A ?? '').' - '.$payroll->month.'/'.$payroll->year,
            'created_by'    => Auth::guard('admin')->id(),
        ]);
    }

    // ─────────────────────────────────────────────
    //  إلغاء اعتماد كشف الراتب — يعيد أرصدة السلف التي خُصمت عند الاعتماد
    // ─────────────────────────────────────────────
    public function unapprove(int $id)
    {
        $payroll = MonthlyPayroll::findOrFail($id);

        if ($payroll->status == 3) {
            return back()->with('error', 'لا يمكن إلغاء اعتماد كشف تم صرفه بالفعل');
        }
        if ($payroll->status != 2) {
            return back()->with('error', 'لا يمكن إلغاء اعتماد كشف غير معتمد');
        }

        DB::transaction(function () use ($payroll) {
            $logs = AdvanceDeductionLog::where('monthly_payroll_id', $payroll->id)->get();

            foreach ($logs as $log) {
                $advance = Advance::find($log->advance_id);
                if ($advance) {
                    $advance->update([
                        'remaining_amount' => $advance->remaining_amount + $log->amount,
                        'status'           => 1,
                    ]);
                }
                $log->delete();
            }

            $comCode = (int) ($payroll->employee->com_code ?? $this->comCode());
            JournalPostingService::reverseBySource($comCode, 'payroll', $payroll->id, Auth::guard('admin')->id(), 'إلغاء اعتماد كشف الراتب');

            $payroll->update(['status' => 1, 'updated_by' => Auth::guard('admin')->id()]);
        });

        return back()->with('success', 'تم إلغاء اعتماد الكشف وإرجاع أرصدة السلف');
    }

    public function delete(int $id)
    {
        $payroll = MonthlyPayroll::findOrFail($id);
        if ($payroll->status != 1) {
            return back()->with('error', 'لا يمكن حذف كشف معتمد أو مدفوع');
        }
        $payroll->delete();
        return redirect()->route('payroll.index')->with('success', 'تم حذف الكشف');
    }
}