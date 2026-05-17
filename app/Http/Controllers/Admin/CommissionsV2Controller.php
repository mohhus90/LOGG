<?php
// FILE: app/Http/Controllers/Admin/CommissionsV2Controller.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CommissionRule;
use App\Models\SalesRecord;
use App\Models\Commission;
use App\Models\Employee;
use App\Models\branches;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommissionsV2Controller extends Controller
{
    private function comCode(): int { return Auth::guard('admin')->user()->com_code; }

    // ── إدارة قواعد العمولات ──
    public function rules()
    {
        $rules    = CommissionRule::where('com_code', $this->comCode())
            ->with('branch')->orderBy('name')->get();
        $branches = branches::where('com_code', $this->comCode())->get();
        return view('admin.commissions_v2.rules', compact('rules', 'branches'));
    }

    public function createRule()
    {
        $branches = branches::where('com_code', $this->comCode())->get();
        return view('admin.commissions_v2.create_rule', compact('branches'));
    }

    public function storeRule(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:150',
            'code'           => 'required|string|max:50|unique:commission_rules,code',
            'basis'          => 'required|string',
            'recipient_type' => 'required|string',
            'calc_type'      => 'required|string',
        ]);

        // تحويل الـ tiers من الجدول إلى JSON
        $tiers = null;
        if ($request->calc_type === 'tiered') {
            $tiers = collect($request->tier_from ?? [])
                ->zip($request->tier_to ?? [], $request->tier_pct ?? [])
                ->filter(fn($t) => $t[0] !== null)
                ->map(fn($t) => ['from' => (float)$t[0], 'to' => $t[1] ? (float)$t[1] : null, 'pct' => (float)$t[2]])
                ->values()->toArray();
        }

        CommissionRule::create([
            'name'           => $request->name,
            'code'           => $request->code,
            'basis'          => $request->basis,
            'recipient_type' => $request->recipient_type,
            'calc_type'      => $request->calc_type,
            'percentage'     => $request->percentage ?? 0,
            'fixed_amount'   => $request->fixed_amount ?? 0,
            'tiers'          => $tiers,
            'branch_id'      => $request->branch_id ?: null,
            'is_active'      => 1,
            'description'    => $request->description,
            'com_code'       => $this->comCode(),
            'added_by'       => Auth::guard('admin')->id(),
        ]);

        return redirect()->route('commissions_v2.rules')
            ->with('success', 'تم إضافة قاعدة العمولة بنجاح');
    }

    // ── إدخال المبيعات ──
    public function sales(Request $request)
    {
        $month     = $request->month ?? now()->month;
        $year      = $request->year  ?? now()->year;
        $employees = Employee::where('com_code', $this->comCode())
            ->orderBy('employee_name_A')->get();
        $branches  = branches::where('com_code', $this->comCode())->get();

        $existing = SalesRecord::where('com_code', $this->comCode())
            ->where('month', $month)->where('year', $year)
            ->get()->groupBy('employee_id');

        return view('admin.commissions_v2.sales',
            compact('employees', 'branches', 'month', 'year', 'existing'));
    }

    public function saveSales(Request $request)
    {
        $month = $request->month;
        $year  = $request->year;
        $admin = Auth::guard('admin')->user();

        DB::beginTransaction();
        try {
            foreach ($request->sales ?? [] as $empId => $amount) {
                if (!$amount) continue;
                SalesRecord::updateOrCreate(
                    ['employee_id' => $empId, 'month' => $month, 'year' => $year, 'com_code' => $admin->com_code],
                    ['sales_amount' => $amount, 'branch_id' => $request->branch_id[$empId] ?? null, 'added_by' => $admin->id]
                );
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('commissions_v2.calculate', ['month' => $month, 'year' => $year])
            ->with('success', 'تم حفظ المبيعات. يمكنك الآن احتساب العمولات.');
    }

    // ── احتساب العمولات تلقائياً من قواعد المبيعات ──
    public function calculate(Request $request)
    {
        $month    = $request->month ?? now()->month;
        $year     = $request->year  ?? now()->year;
        $rules    = CommissionRule::where('com_code', $this->comCode())
            ->where('is_active', 1)->get();
        $employees = Employee::where('com_code', $this->comCode())->get();
        $salesData = SalesRecord::where('com_code', $this->comCode())
            ->where('month', $month)->where('year', $year)->get();

        // احتساب عمولة كل موظف
        $preview = [];
        foreach ($employees as $emp) {
            $empCommissions = [];
            foreach ($rules as $rule) {
                $salesAmount = $this->getSalesForRule($rule, $emp, $salesData);
                if ($salesAmount <= 0 && $rule->calc_type !== 'fixed_amount') continue;

                $amount = $rule->calculate($salesAmount);
                if ($amount > 0) {
                    $empCommissions[] = [
                        'rule'         => $rule->name,
                        'basis'        => $rule->basis_label,
                        'sales_amount' => $salesAmount,
                        'commission'   => $amount,
                    ];
                }
            }
            if (!empty($empCommissions)) {
                $preview[$emp->id] = [
                    'employee'    => $emp,
                    'commissions' => $empCommissions,
                    'total'       => array_sum(array_column($empCommissions, 'commission')),
                ];
            }
        }

        return view('admin.commissions_v2.calculate',
            compact('preview', 'month', 'year', 'rules'));
    }

    public function confirmCalculate(Request $request)
    {
        $month = $request->month;
        $year  = $request->year;
        $admin = Auth::guard('admin')->user();

        DB::beginTransaction();
        try {
            foreach ($request->approve ?? [] as $empId => $commissions) {
                foreach ($commissions as $commissionData) {
                    if (!($commissionData['approved'] ?? false)) continue;
                    Commission::create([
                        'employee_id'      => $empId,
                        'commission_date'  => now()->format('Y-m-d'),
                        'commission_type'  => $commissionData['rule'],
                        'amount'           => $commissionData['amount'],
                        'month'            => $month,
                        'year'             => $year,
                        'status'           => 1,
                        'notes'            => 'محتسبة تلقائياً من قاعدة: ' . $commissionData['rule'],
                        'com_code'         => $admin->com_code,
                        'added_by'         => $admin->id,
                    ]);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('commissions.index', ['month' => $month, 'year' => $year])
            ->with('success', 'تم اعتماد العمولات وإضافتها لمسير الرواتب');
    }

    private function getSalesForRule(CommissionRule $rule, Employee $emp, $salesData): float
    {
        switch ($rule->basis) {
            case 'individual_sales':
                return (float) $salesData->where('employee_id', $emp->id)->sum('sales_amount');

            case 'branch_sales':
                $branchId = $emp->branch_id ?? $rule->branch_id;
                return (float) $salesData->where('branch_id', $branchId)->sum('sales_amount');

            case 'area_sales':
            case 'company_sales':
                return (float) $salesData->sum('sales_amount');

            case 'fixed':
                return 1; // يُضرب في fixed_amount

            default:
                return 0;
        }
    }

    public function deleteRule(int $id)
    {
        CommissionRule::where('com_code', $this->comCode())->findOrFail($id)->delete();
        return redirect()->route('commissions_v2.rules')->with('success', 'تم الحذف');
    }
}
