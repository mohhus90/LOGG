<?php

namespace App\Http\Controllers\Admin\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LeaveCompensationSetting;
use App\Models\LeaveCompensationRate;
use App\Models\Jobs_categories;
use App\Models\Branche;
use App\Models\Department;

class LeaveCompensationController extends Controller
{
    private function comCode(): int
    {
        return (int) Auth::guard('admin')->user()->com_code;
    }

    public function index()
    {
        $comCode  = $this->comCode();
        $settings = LeaveCompensationSetting::firstOrCreateForCompany($comCode);

        $jobs  = Jobs_categories::where('com_code', $comCode)->orderBy('job_name')->get();
        $branches = Branche::where('com_code', $comCode)->orderBy('branch_name')->get();
        $depts = Department::where('com_code', $comCode)->orderBy('dep_name')->get();

        $rates = LeaveCompensationRate::where('com_code', $comCode)
            ->get()
            ->keyBy(fn($r) => $r->level_type . '_' . $r->level_id);

        return view('admin.leave_compensation.settings', compact('settings', 'jobs', 'branches', 'depts', 'rates'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'comp_type'    => 'required|integer|in:1,2',
            'day_multiplier' => 'required|numeric|min:0',
            'fixed_level'  => 'required_if:comp_type,2|in:job,branch,department',
        ]);

        $comCode = $this->comCode();

        LeaveCompensationSetting::updateOrCreate(
            ['com_code' => $comCode],
            [
                'comp_type'      => (int)$request->comp_type,
                'day_multiplier' => (float)$request->day_multiplier,
                'fixed_level'    => $request->fixed_level ?? 'job',
            ]
        );

        // حفظ المعدلات الثابتة (النوع 2)
        if ($request->comp_type == 2) {
            $levelType = $request->fixed_level;
            $amounts   = match($levelType) {
                'branch'     => $request->input('rates_branch', []),
                'department' => $request->input('rates_dept', []),
                default      => $request->input('rates_job', []),
            };

            foreach ($amounts as $levelId => $amount) {
                $amount = (float)$amount;
                if ($amount > 0) {
                    LeaveCompensationRate::updateOrCreate(
                        ['com_code' => $comCode, 'level_type' => $levelType, 'level_id' => (int)$levelId],
                        ['amount' => $amount]
                    );
                } else {
                    LeaveCompensationRate::where('com_code', $comCode)
                        ->where('level_type', $levelType)
                        ->where('level_id', (int)$levelId)
                        ->delete();
                }
            }
        }

        return redirect()->route('leave_compensation.index')
            ->with('success', 'تم حفظ إعدادات بدل الإجازة بنجاح');
    }

    /**
     * حساب بدل الإجازة لموظف محدد في يوم راحة عمل فيه
     */
    public static function calculate(int $comCode, \App\Models\Employee $employee, float $dailyRate): float
    {
        $settings = LeaveCompensationSetting::getByComCode($comCode);
        if (!$settings) return 0.0;

        if ($settings->comp_type == 1) {
            return round($dailyRate * (float)$settings->day_multiplier, 2);
        }

        // النوع 2: مبلغ ثابت حسب المستوى
        $levelType = $settings->fixed_level;
        $levelId   = match($levelType) {
            'branch'     => (int)($employee->branches_id ?? 0),
            'department' => (int)($employee->emp_departments_id ?? 0),
            default      => (int)($employee->emp_jobs_id ?? 0),
        };

        if (!$levelId) return 0.0;

        return LeaveCompensationRate::getAmount($comCode, $levelType, $levelId);
    }
}
