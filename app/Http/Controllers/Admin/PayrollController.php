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
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    /**
     * عرض مسير الرواتب
     */
    public function index(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year  = $request->year  ?? now()->year;

        $data = MonthlyPayroll::with('employee')
            ->where('month', $month)
            ->where('year', $year)
            ->orderBy('employee_id')
            ->paginate(20);

        return view('admin.payroll.index', compact('data', 'month', 'year'));
    }

    /**
     * نموذج إنشاء مسير الرواتب لشهر معين
     */
    public function create()
    {
        $employees = Employee::orderBy('employee_name_A')->get();
        return view('admin.payroll.create', compact('employees'));
    }

    /**
     * احتساب راتب موظف واحد
     * @param int    $employeeId
     * @param int    $month
     * @param int    $year
     * @param string $periodFrom تاريخ بداية الاحتساب (مثلاً 2024-01-26)
     * @param string $periodTo   تاريخ نهاية الاحتساب (مثلاً 2024-02-25)
     */
    public function calculateSingle(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month'       => 'required|integer|between:1,12',
            'year'        => 'required|integer|min:2020',
            'period_from' => 'required|date',
            'period_to'   => 'required|date|after:period_from',
        ]);

        $employee   = Employee::with('shifts_type')->findOrFail($request->employee_id);
        $periodFrom = Carbon::parse($request->period_from);
        $periodTo   = Carbon::parse($request->period_to);
        $month      = $request->month;
        $year       = $request->year;

        // التحقق من عدم وجود مسير سابق
        $existingPayroll = MonthlyPayroll::where('employee_id', $employee->id)
            ->where('month', $month)->where('year', $year)->first();

        if ($existingPayroll && $existingPayroll->status != 1) {
            return back()->with('error', 'يوجد مسير راتب معتمد لهذا الموظف لهذا الشهر');
        }

        DB::beginTransaction();
        try {
            $payroll = $this->computePayroll($employee, $month, $year, $periodFrom, $periodTo);

            if ($existingPayroll) {
                $existingPayroll->update((array) $payroll);
                $savedPayroll = $existingPayroll;
            } else {
                $savedPayroll = MonthlyPayroll::create((array) $payroll);
            }

            DB::commit();
            return redirect()->route('payroll.show', $savedPayroll->id)
                ->with('success', 'تم احتساب الراتب بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'خطأ: ' . $e->getMessage());
        }
    }

    /**
     * احتساب رواتب جميع الموظفين لشهر معين (Bulk)
     */
    public function calculateBulk(Request $request)
    {
        $request->validate([
            'month'       => 'required|integer|between:1,12',
            'year'        => 'required|integer|min:2020',
            'period_from' => 'required|date',
            'period_to'   => 'required|date|after:period_from',
        ]);

        $employees  = Employee::all();
        $periodFrom = Carbon::parse($request->period_from);
        $periodTo   = Carbon::parse($request->period_to);
        $month      = $request->month;
        $year       = $request->year;
        $count      = 0;

        DB::beginTransaction();
        try {
            foreach ($employees as $employee) {
                $payrollData = $this->computePayroll($employee, $month, $year, $periodFrom, $periodTo);

                MonthlyPayroll::updateOrCreate(
                    ['employee_id' => $employee->id, 'month' => $month, 'year' => $year],
                    (array) $payrollData
                );
                $count++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'خطأ أثناء الاحتساب: ' . $e->getMessage());
        }

        return redirect()->route('payroll.index', ['month' => $month, 'year' => $year])
            ->with('success', "تم احتساب رواتب $count موظف بنجاح");
    }

    /**
     * منطق الاحتساب الفعلي للراتب
     */
    private function computePayroll(Employee $employee, int $month, int $year, Carbon $periodFrom, Carbon $periodTo): object
    {
        $admin      = Auth::guard('admin')->user();
        $totalDays  = $periodFrom->diffInDays($periodTo) + 1;
        $basicSal   = $employee->emp_sal ?? 0;
        $dailyRate  = $totalDays > 0 ? ($basicSal / $totalDays) : 0;

        // --- سجلات الحضور في الفترة ---
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('attendance_date', [$periodFrom->format('Y-m-d'), $periodTo->format('Y-m-d')])
            ->get();

        $presentDays  = $attendances->where('status', 1)->count();
        $absenceDays  = $attendances->where('status', 2)->count();
        $leaveDays    = $attendances->whereIn('status', [3, 4])->count();

        // الراتب المستحق = سعر اليوم × (أيام حضور + أيام إجازة) 
        // أيام الغياب لا تُدفع
        $earnedDays  = $presentDays + $leaveDays;
        $earnedSal   = round($dailyRate * $earnedDays, 2);

        // إجمالي الأوفرتايم والتأخيرات من الحضور
        $overtimeAmount  = round($attendances->sum('overtime_amount'), 2);
        $lateDeductions  = round($attendances->sum('late_deduction'), 2);
        $absenceDeductions = round($dailyRate * $absenceDays, 2);

        // --- العمولات ---
        $commissions = Commission::where('employee_id', $employee->id)
            ->where('month', $month)->where('year', $year)->where('status', 1)
            ->sum('amount');

        // --- الخصومات ---
        $deductions = Deduction::where('employee_id', $employee->id)
            ->where('month', $month)->where('year', $year)->where('status', 1)
            ->sum('amount');

        // --- السلفة: قسط الشهر ---
        $advance = Advance::where('employee_id', $employee->id)
            ->where('status', 1)
            ->where('remaining_amount', '>', 0)
            ->first();
        $advanceInstallment = $advance ? $advance->monthly_installment : 0;

        // --- التأمينات ---
        $insurance = $employee->emp_sal_insurance ?? 0;

        // --- الإضافات الثابتة ---
        $fixedAllowances = $employee->emp_fixed_allowances ?? 0;

        // --- الإجمالي قبل الخصم ---
        $grossSalary = $earnedSal + $fixedAllowances + $overtimeAmount + $commissions;

        // --- الصافي ---
        $netSalary = $grossSalary
            - $lateDeductions
            - $absenceDeductions
            - $deductions
            - $advanceInstallment
            - $insurance;

        if ($netSalary < 0) $netSalary = 0;

        return (object) [
            'employee_id'        => $employee->id,
            'month'              => $month,
            'year'               => $year,
            'period_from'        => $periodFrom->format('Y-m-d'),
            'period_to'          => $periodTo->format('Y-m-d'),
            'total_days'         => $totalDays,
            'work_days'          => $presentDays,
            'absence_days'       => $absenceDays,
            'leave_days'         => $leaveDays,
            'basic_salary'       => $basicSal,
            'daily_rate'         => round($dailyRate, 4),
            'earned_salary'      => $earnedSal,
            'fixed_allowances'   => $fixedAllowances,
            'overtime_amount'    => $overtimeAmount,
            'commissions_amount' => round($commissions, 2),
            'late_deductions'    => $lateDeductions,
            'absence_deductions' => $absenceDeductions,
            'deductions_amount'  => round($deductions, 2),
            'advance_installment'=> round($advanceInstallment, 2),
            'insurance_deduction'=> round($insurance, 2),
            'gross_salary'       => round($grossSalary, 2),
            'net_salary'         => round($netSalary, 2),
            'status'             => 1,
            'com_code'           => $admin->com_code,
            'added_by'           => $admin->id,
        ];
    }

    public function show(int $id)
    {
        $payroll = MonthlyPayroll::with('employee')->findOrFail($id);
        return view('admin.payroll.show', compact('payroll'));
    }

    /**
     * اعتماد مسير الراتب
     */
    public function approve(int $id)
    {
        $payroll = MonthlyPayroll::findOrFail($id);
        if ($payroll->status != 1) {
            return back()->with('error', 'لا يمكن اعتماد مسير غير في حالة مسودة');
        }
        $payroll->update(['status' => 2, 'updated_by' => Auth::guard('admin')->id()]);

        // خصم قسط السلفة من الرصيد المتبقي
        if ($payroll->advance_installment > 0) {
            $advance = Advance::where('employee_id', $payroll->employee_id)
                ->where('status', 1)->where('remaining_amount', '>', 0)->first();
            if ($advance) {
                $remaining = $advance->remaining_amount - $payroll->advance_installment;
                $advance->update([
                    'remaining_amount' => max(0, $remaining),
                    'status' => $remaining <= 0 ? 2 : 1,
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
        return redirect()->route('payroll.index')->with('success', 'تم حذف المسير بنجاح');
    }
}