<?php

namespace App\Http\Controllers\Admin\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\BranchCommissionPlan;
use App\Models\BranchCommissionPlanMember;
use App\Models\BranchTarget;
use App\Models\EmployeeBranchTarget;
use App\Models\EmployeeTargetEvent;
use App\Models\SalesRecord;
use App\Models\Commission;
use App\Models\Employee;
use App\Models\Branche;

class BranchCommissionsController extends Controller
{
    private function comCode(): int
    {
        return Auth::guard('admin')->user()->com_code;
    }

    // ── قائمة الخطط ──
    public function index()
    {
        $plans = BranchCommissionPlan::where('com_code', $this->comCode())
            ->with(['branch', 'members'])
            ->orderBy('branch_id')
            ->get();

        return view('admin.branch_commissions.index', compact('plans'));
    }

    // ── إنشاء خطة ──
    public function create()
    {
        $branches  = Branche::where('com_code', $this->comCode())->get();
        $employees = Employee::where('com_code', $this->comCode())
            ->orderBy('employee_name_A')->get();

        return view('admin.branch_commissions.create', compact('branches', 'employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:150',
            'branch_id' => 'required|integer',
        ]);

        $tiers = $this->buildTiers($request);

        $plan = BranchCommissionPlan::create([
            'name'        => $request->name,
            'branch_id'   => $request->branch_id,
            'description' => $request->description,
            'tiers'       => $tiers,
            'is_active'   => 1,
            'com_code'    => $this->comCode(),
            'added_by'    => Auth::guard('admin')->id(),
        ]);

        $this->syncMembers($plan->id, $request);

        return redirect()->route('branch_commissions.index')
            ->with('success', 'تم إنشاء خطة العمولة بنجاح');
    }

    // ── تعديل خطة ──
    public function edit(int $id)
    {
        $plan      = BranchCommissionPlan::where('com_code', $this->comCode())
            ->with('members')->findOrFail($id);
        $branches  = Branche::where('com_code', $this->comCode())->get();
        $employees = Employee::where('com_code', $this->comCode())
            ->orderBy('employee_name_A')->get();

        return view('admin.branch_commissions.create',
            compact('plan', 'branches', 'employees'));
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'name'      => 'required|string|max:150',
            'branch_id' => 'required|integer',
        ]);

        $plan = BranchCommissionPlan::where('com_code', $this->comCode())->findOrFail($id);

        $plan->update([
            'name'        => $request->name,
            'branch_id'   => $request->branch_id,
            'description' => $request->description,
            'tiers'       => $this->buildTiers($request),
            'is_active'   => $request->boolean('is_active', true),
        ]);

        // حذف الأعضاء القديمة ثم إعادة الإضافة
        BranchCommissionPlanMember::where('plan_id', $id)->delete();
        $this->syncMembers($id, $request);

        return redirect()->route('branch_commissions.index')
            ->with('success', 'تم تحديث الخطة بنجاح');
    }

    // ── حذف خطة ──
    public function delete(int $id)
    {
        $plan = BranchCommissionPlan::where('com_code', $this->comCode())->findOrFail($id);
        BranchCommissionPlanMember::where('plan_id', $id)->delete();
        $plan->delete();

        return redirect()->route('branch_commissions.index')
            ->with('success', 'تم الحذف');
    }

    // ── أهداف الفروع الشهرية ──
    public function targets(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year  = $request->year  ?? now()->year;

        // الفروع التي لديها خطط نشطة فقط
        $branchIds = BranchCommissionPlan::where('com_code', $this->comCode())
            ->where('is_active', true)
            ->pluck('branch_id')->unique();

        $branches = Branche::where('com_code', $this->comCode())
            ->whereIn('id', $branchIds)->get();

        // الأهداف المحفوظة
        $targets = BranchTarget::where('com_code', $this->comCode())
            ->where('month', $month)->where('year', $year)
            ->get()->keyBy('branch_id');

        return view('admin.branch_commissions.targets',
            compact('branches', 'targets', 'month', 'year'));
    }

    public function saveTargets(Request $request)
    {
        $month = $request->month;
        $year  = $request->year;
        $admin = Auth::guard('admin')->user();

        DB::beginTransaction();
        try {
            foreach ($request->targets ?? [] as $branchId => $amount) {
                BranchTarget::updateOrCreate(
                    ['branch_id' => $branchId, 'month' => $month, 'year' => $year, 'com_code' => $admin->com_code],
                    ['target_amount' => (float) $amount ?: 0, 'added_by' => $admin->id]
                );
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('branch_commissions.targets', ['month' => $month, 'year' => $year])
            ->with('success', 'تم حفظ الأهداف الشهرية بنجاح');
    }

    // ── احتساب العمولات ──
    public function calculate(Request $request)
    {
        $month       = $request->month ?? now()->month;
        $year        = $request->year  ?? now()->year;
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $plans = BranchCommissionPlan::where('com_code', $this->comCode())
            ->where('is_active', true)
            ->with(['branch', 'members.employee'])
            ->get();

        // جميع مبيعات الشهر (مع from_day / to_day)
        $allSales = SalesRecord::where('com_code', $this->comCode())
            ->where('month', $month)->where('year', $year)
            ->get();

        $allTargets = BranchTarget::where('com_code', $this->comCode())
            ->where('month', $month)->where('year', $year)
            ->get()->keyBy('branch_id');

        $preview = [];

        foreach ($plans as $plan) {
            $branchId     = $plan->branch_id;
            $targetRecord = $allTargets->get($branchId);
            $target       = $targetRecord ? (float) $targetRecord->target_amount : 0;

            if ($target <= 0) {
                $preview[] = [
                    'plan'            => $plan,
                    'error'           => 'لم يتم تحديد التارجت لهذا الفرع',
                    'target'          => 0,
                    'actual_sales'    => 0,
                    'achievement_pct' => 0,
                    'matched_tier'    => null,
                    'members'         => [],
                ];
                continue;
            }

            $branchSalesRecords = $allSales->where('branch_id', $branchId);
            $actualSales        = (float) $branchSalesRecords->sum('sales_amount');
            $achievementPct     = $actualSales / $target * 100;

            // الشريحة بناءً على تحقيق الفرع — تُستخدم فقط لعمولة المدير
            $branchMatchedTier = $plan->matchTier($achievementPct);

            // التارجت الفعلي لكل موظف (بعد أحداث منتصف الشهر)
            $effectiveTargets = $this->getEffectiveTargets($plan, $month, $year);

            $members = [];
            foreach ($plan->members as $member) {
                $empId           = $member->employee_id;
                $empSalesRecords = $branchSalesRecords->where('employee_id', $empId);
                $empSales        = (float) $empSalesRecords->sum('sales_amount');

                // عدد الأيام من from_day/to_day إن وُجدت
                $daysWorked = $this->calcDaysFromRecords($empSalesRecords, $daysInMonth);

                $empTargetData = $effectiveTargets[$empId] ?? null;
                $baseTarget    = (float) ($empTargetData['base']       ?? 0);
                $effTarget     = (float) ($empTargetData['effective']   ?? 0);
                $adjustments   = $empTargetData['adjustments'] ?? [];

                // تناسب التارجت الفردي مع الأيام (إذا كانت from_day/to_day محددة)
                if ($daysWorked < $daysInMonth && $baseTarget > 0) {
                    $effTarget = round($baseTarget * $daysWorked / $daysInMonth, 2);
                }

                // نسبة تحقيق الموظف الفردي
                $empAchievementPct = $effTarget > 0
                    ? round($empSales / $effTarget * 100, 1)
                    : null;

                // الشريحة الفردية للبائع (بناءً على تحقيقه هو)
                $empMatchedTier = ($empAchievementPct !== null)
                    ? $plan->matchTier($empAchievementPct)
                    : null;

                $commission = 0.0;
                $breakdown  = [];

                if ($member->role === 'seller') {
                    // البائع: يعتمد على تحقيقه الفردي فقط
                    if ($empMatchedTier && $empSales > 0) {
                        $sellerRate = (float) ($empMatchedTier['seller_rate'] ?? 0);
                        if ($sellerRate > 0) {
                            $amount      = round($empSales * $sellerRate / 100, 2);
                            $commission += $amount;
                            $breakdown[] = [
                                'type'        => 'عمولة بائع',
                                'rate'        => $sellerRate,
                                'base'        => $empSales,
                                'amount'      => $amount,
                                'achievement' => $empAchievementPct,
                                'basis'       => 'individual',
                            ];
                        }
                    }
                } elseif ($member->role === 'manager') {
                    // المدير: عمولة الإدارة بناءً على تحقيق الفرع
                    if ($branchMatchedTier) {
                        $managerRate = (float) ($branchMatchedTier['manager_rate'] ?? 0);
                        if ($managerRate > 0) {
                            $amount      = round($actualSales * $managerRate / 100, 2);
                            $commission += $amount;
                            $breakdown[] = [
                                'type'        => 'عمولة مدير فرع',
                                'rate'        => $managerRate,
                                'base'        => $actualSales,
                                'amount'      => $amount,
                                'achievement' => $achievementPct,
                                'basis'       => 'branch',
                            ];
                        }
                    }
                    // المدير أيضاً بائع: يعتمد على تحقيقه الفردي
                    if ($member->also_as_seller && $empMatchedTier && $empSales > 0) {
                        $sellerRate = (float) ($empMatchedTier['seller_rate'] ?? 0);
                        if ($sellerRate > 0) {
                            $amount      = round($empSales * $sellerRate / 100, 2);
                            $commission += $amount;
                            $breakdown[] = [
                                'type'        => 'عمولة بائع (مدير/بائع)',
                                'rate'        => $sellerRate,
                                'base'        => $empSales,
                                'amount'      => $amount,
                                'achievement' => $empAchievementPct,
                                'basis'       => 'individual',
                            ];
                        }
                    }
                }

                $members[] = [
                    'member'           => $member,
                    'emp_sales'        => $empSales,
                    'days_worked'      => $daysWorked,
                    'days_in_month'    => $daysInMonth,
                    'base_target'      => $baseTarget,
                    'effective_target' => $effTarget,
                    'emp_achievement'  => $empAchievementPct,
                    'emp_matched_tier' => $empMatchedTier,
                    'adjustments'      => $adjustments,
                    'commission'       => $commission,
                    'breakdown'        => $breakdown,
                ];
            }

            // إضافة البدلاء
            foreach ($effectiveTargets as $empId => $etData) {
                $alreadyAdded  = collect($members)->contains(fn($m) => $m['member']->employee_id == $empId);
                $isReplacement = collect($etData['adjustments'])->contains('type', 'replacement');

                if (!$alreadyAdded && $isReplacement) {
                    $empSalesRecords   = $branchSalesRecords->where('employee_id', $empId);
                    $empSales          = (float) $empSalesRecords->sum('sales_amount');
                    $daysWorked        = $this->calcDaysFromRecords($empSalesRecords, $daysInMonth);
                    $effTarget         = $etData['effective'];

                    if ($daysWorked < $daysInMonth && $effTarget > 0) {
                        $effTarget = round($effTarget * $daysWorked / $daysInMonth, 2);
                    }

                    $empAchievementPct = $effTarget > 0 ? round($empSales / $effTarget * 100, 1) : null;
                    $empMatchedTier    = $empAchievementPct !== null ? $plan->matchTier($empAchievementPct) : null;

                    $commission = 0.0;
                    $breakdown  = [];
                    if ($empMatchedTier && $empSales > 0) {
                        $sellerRate = (float) ($empMatchedTier['seller_rate'] ?? 0);
                        if ($sellerRate > 0) {
                            $amount     = round($empSales * $sellerRate / 100, 2);
                            $commission = $amount;
                            $breakdown[] = [
                                'type'        => 'عمولة بائع (بديل)',
                                'rate'        => $sellerRate,
                                'base'        => $empSales,
                                'amount'      => $amount,
                                'achievement' => $empAchievementPct,
                                'basis'       => 'individual',
                            ];
                        }
                    }

                    $members[] = [
                        'member'           => (object)[
                            'employee_id'    => $empId,
                            'role'           => 'seller',
                            'also_as_seller' => false,
                            'employee'       => $etData['employee'],
                        ],
                        'emp_sales'        => $empSales,
                        'days_worked'      => $daysWorked,
                        'days_in_month'    => $daysInMonth,
                        'base_target'      => 0,
                        'effective_target' => $effTarget,
                        'emp_achievement'  => $empAchievementPct,
                        'emp_matched_tier' => $empMatchedTier,
                        'adjustments'      => $etData['adjustments'],
                        'commission'       => $commission,
                        'breakdown'        => $breakdown,
                        'is_replacement'   => true,
                    ];
                }
            }

            $preview[] = [
                'plan'            => $plan,
                'error'           => null,
                'target'          => $target,
                'actual_sales'    => $actualSales,
                'achievement_pct' => $achievementPct,
                'matched_tier'    => $branchMatchedTier,
                'members'         => $members,
            ];
        }

        return view('admin.branch_commissions.calculate',
            compact('preview', 'month', 'year'));
    }

    // ── حساب الأيام من from_day / to_day على سجلات المبيعات ──
    private function calcDaysFromRecords($records, int $daysInMonth): int
    {
        $from = $records->whereNotNull('from_day')->min('from_day');
        $to   = $records->whereNotNull('to_day')->max('to_day');

        if ($from !== null && $to !== null && (int) $to >= (int) $from) {
            return min((int) $to - (int) $from + 1, $daysInMonth);
        }

        return $daysInMonth;
    }

    // ── اعتماد العمولات وإضافتها لكشف الرواتب ──
    public function confirmCalculate(Request $request)
    {
        $month = $request->month;
        $year  = $request->year;
        $admin = Auth::guard('admin')->user();

        DB::beginTransaction();
        try {
            foreach ($request->entries ?? [] as $entry) {
                if (empty($entry['approved'])) {
                    continue;
                }
                // updateOrCreate بدل create: لو العمولة دي (نفس الموظف/الشهر/النوع/الخطة)
                // معتمدة بالفعل من قبل، تُحدَّث قيمتها فقط بدل تكرارها كسجل جديد
                Commission::updateOrCreate(
                    [
                        'employee_id'     => $entry['employee_id'],
                        'month'           => $month,
                        'year'            => $year,
                        'commission_type' => $entry['rule_name'],
                        'notes'           => 'عمولة فرع: ' . $entry['plan_name'],
                        'com_code'        => $admin->com_code,
                    ],
                    [
                        'commission_date' => now()->format('Y-m-d'),
                        'amount'          => $entry['amount'],
                        'status'          => 1, // معتمدة
                        'added_by'        => $admin->id,
                    ]
                );
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('commissions.index', ['month' => $month, 'year' => $year])
            ->with('success', 'تم اعتماد العمولات وإضافتها لكشف الرواتب');
    }

    // ── التارجت الفردي للموظفين ──
    public function employeeTargets(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year  = $request->year  ?? now()->year;

        $plans = BranchCommissionPlan::where('com_code', $this->comCode())
            ->where('is_active', true)
            ->with(['branch', 'members.employee'])
            ->get();

        $existing = EmployeeBranchTarget::where('com_code', $this->comCode())
            ->where('month', $month)->where('year', $year)
            ->get()->keyBy(fn($t) => $t->plan_id . '_' . $t->employee_id);

        $branchTargets = BranchTarget::where('com_code', $this->comCode())
            ->where('month', $month)->where('year', $year)
            ->get()->keyBy('branch_id');

        return view('admin.branch_commissions.employee_targets',
            compact('plans', 'existing', 'branchTargets', 'month', 'year'));
    }

    public function saveEmployeeTargets(Request $request)
    {
        $month = $request->month;
        $year  = $request->year;
        $admin = Auth::guard('admin')->user();

        DB::beginTransaction();
        try {
            foreach ($request->targets ?? [] as $planId => $employees) {
                foreach ($employees as $empId => $amount) {
                    EmployeeBranchTarget::updateOrCreate(
                        ['plan_id' => $planId, 'employee_id' => $empId, 'month' => $month, 'year' => $year, 'com_code' => $admin->com_code],
                        ['target_amount' => (float) $amount ?: 0, 'added_by' => $admin->id]
                    );
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم حفظ التارجت الفردي لجميع الموظفين');
    }

    // ── أحداث منتصف الشهر ──
    public function events(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year  = $request->year  ?? now()->year;

        $plans = BranchCommissionPlan::where('com_code', $this->comCode())
            ->where('is_active', true)->with(['branch', 'members.employee'])->get();

        $events = EmployeeTargetEvent::where('com_code', $this->comCode())
            ->where('month', $month)->where('year', $year)
            ->with(['employee', 'replacement', 'branch'])
            ->orderBy('branch_id')->get();

        $employees = Employee::where('com_code', $this->comCode())
            ->orderBy('employee_name_A')->get();

        $branches = Branche::where('com_code', $this->comCode())->get();

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        return view('admin.branch_commissions.events',
            compact('plans', 'events', 'employees', 'branches', 'month', 'year', 'daysInMonth'));
    }

    public function saveEvent(Request $request)
    {
        $request->validate([
            'branch_id'      => 'required|integer',
            'employee_id'    => 'required|integer',
            'last_day_present' => 'required|integer|min:1|max:31',
            'month'          => 'required|integer',
            'year'           => 'required|integer',
        ]);

        EmployeeTargetEvent::create([
            'month'                   => $request->month,
            'year'                    => $request->year,
            'branch_id'               => $request->branch_id,
            'employee_id'             => $request->employee_id,
            'last_day_present'        => $request->last_day_present,
            'replacement_employee_id' => $request->replacement_employee_id ?: null,
            'redistribute_target'     => $request->boolean('redistribute_target'),
            'notes'                   => $request->notes,
            'com_code'                => $this->comCode(),
            'added_by'                => Auth::guard('admin')->id(),
        ]);

        return redirect()->route('branch_commissions.events', ['month' => $request->month, 'year' => $request->year])
            ->with('success', 'تم حفظ الحدث بنجاح');
    }

    public function deleteEvent(int $id)
    {
        EmployeeTargetEvent::where('com_code', $this->comCode())->findOrFail($id)->delete();
        return back()->with('success', 'تم الحذف');
    }

    // ── مساعدات ──
    private function buildTiers(Request $request): array
    {
        $tiers = [];
        $froms = $request->tier_from_pct ?? [];
        $tos   = $request->tier_to_pct   ?? [];
        $sels  = $request->tier_seller    ?? [];
        $mgrs  = $request->tier_manager   ?? [];

        foreach ($froms as $i => $from) {
            if ($from === null || $from === '') {
                continue;
            }
            $tiers[] = [
                'from_pct'     => (float) $from,
                'to_pct'       => ($tos[$i] !== null && $tos[$i] !== '') ? (float) $tos[$i] : null,
                'seller_rate'  => (float) ($sels[$i] ?? 0),
                'manager_rate' => (float) ($mgrs[$i] ?? 0),
            ];
        }

        // ترتيب تصاعدي حسب from_pct
        usort($tiers, fn($a, $b) => $a['from_pct'] <=> $b['from_pct']);

        return $tiers;
    }

    /**
     * احتساب التارجت الفعلي لكل موظف في الفرع بعد تطبيق أحداث منتصف الشهر.
     * يعيد: [employee_id => ['base'=>, 'effective'=>, 'employee'=>, 'adjustments'=>[]]]
     */
    public function getEffectiveTargets(BranchCommissionPlan $plan, int $month, int $year): array
    {
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $baseTargets = EmployeeBranchTarget::where('plan_id', $plan->id)
            ->where('month', $month)->where('year', $year)
            ->get()->keyBy('employee_id');

        $events = EmployeeTargetEvent::where('branch_id', $plan->branch_id)
            ->where('month', $month)->where('year', $year)
            ->where('com_code', $plan->com_code)
            ->get();

        // تهيئة بيانات الموظفين بالتارجت الأساسي
        $data = [];
        foreach ($plan->members as $member) {
            $empId = $member->employee_id;
            $base  = (float) ($baseTargets[$empId]->target_amount ?? 0);
            $data[$empId] = [
                'base'        => $base,
                'effective'   => $base,
                'employee'    => $member->employee,
                'adjustments' => [],
            ];
        }

        foreach ($events as $event) {
            $empId         = $event->employee_id;
            $lastDay       = (int) $event->last_day_present;
            $daysPresent   = $lastDay;
            $daysAbsent    = $daysInMonth - $daysPresent;
            $base          = $data[$empId]['base'] ?? 0;

            if ($base <= 0 || $daysAbsent <= 0) {
                continue;
            }

            $effectiveForPresent = round($base * $daysPresent / $daysInMonth, 2);
            $gapTarget           = round($base * $daysAbsent / $daysInMonth, 2);

            // تقليل تارجت الموظف المغادر/الغائب
            $data[$empId]['effective'] = $effectiveForPresent;
            $data[$empId]['adjustments'][] = [
                'type'         => 'departure',
                'last_day'     => $lastDay,
                'days_present' => $daysPresent,
                'days_absent'  => $daysAbsent,
                'gap_target'   => $gapTarget,
            ];

            // إذا يوجد بديل: يأخذ حصة الأيام المتبقية
            if ($event->replacement_employee_id) {
                $repId = $event->replacement_employee_id;
                if (!isset($data[$repId])) {
                    $data[$repId] = [
                        'base'        => 0,
                        'effective'   => 0,
                        'employee'    => Employee::find($repId),
                        'adjustments' => [],
                    ];
                }
                $data[$repId]['effective'] += $gapTarget;
                $data[$repId]['adjustments'][] = [
                    'type'       => 'replacement',
                    'for'        => $empId,
                    'days'       => $daysAbsent,
                    'added'      => $gapTarget,
                ];
            }

            // إذا التوزيع على الزملاء: يوزع حصة الغياب بنسب تارجتهم
            if ($event->redistribute_target) {
                $remaining        = collect($data)->filter(fn($d, $id) => $id !== $empId && $d['base'] > 0);
                $totalRemaining   = $remaining->sum('base');

                if ($totalRemaining > 0) {
                    foreach ($remaining as $remId => $remData) {
                        $ratio  = $remData['base'] / $totalRemaining;
                        $added  = round($gapTarget * $ratio, 2);
                        $data[$remId]['effective'] += $added;
                        $data[$remId]['adjustments'][] = [
                            'type'  => 'redistribution',
                            'from'  => $empId,
                            'ratio' => $ratio,
                            'added' => $added,
                        ];
                    }
                }
            }
        }

        return $data;
    }

    private function syncMembers(int $planId, Request $request): void
    {
        $employeeIds = $request->members ?? [];
        $roles       = $request->member_role       ?? [];
        $alsoSeller  = $request->member_also_seller ?? [];

        foreach ($employeeIds as $empId) {
            BranchCommissionPlanMember::create([
                'plan_id'       => $planId,
                'employee_id'   => $empId,
                'role'          => $roles[$empId]      ?? 'seller',
                'also_as_seller'=> isset($alsoSeller[$empId]) ? 1 : 0,
            ]);
        }
    }
}
