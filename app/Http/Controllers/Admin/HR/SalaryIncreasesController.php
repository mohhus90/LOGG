<?php

namespace App\Http\Controllers\Admin\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\SalaryIncreaseRule;
use App\Models\Department;
use App\Models\Jobs_categories;
use App\Models\Branche;
use App\Models\Client;
use App\Observers\EmployeeObserver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalaryIncreasesController extends Controller
{
    public function index()
    {
        $comCode = (int) Auth::guard('admin')->user()->com_code;

        $rules = SalaryIncreaseRule::where('com_code', $comCode)
            ->with('addedBy')
            ->orderByDesc('effective_date')
            ->orderByDesc('id')
            ->paginate(20);

        $rules->getCollection()->transform(function (SalaryIncreaseRule $rule) use ($comCode) {
            $rule->matched_count = $rule->matchedEmployeesQuery($comCode)->count();
            return $rule;
        });

        return view('admin.salary_increases.index', compact('rules'));
    }

    public function create()
    {
        $comCode         = (int) Auth::guard('admin')->user()->com_code;
        $departments     = Department::where('com_code', $comCode)->orderBy('dep_name')->get(['id', 'dep_name']);
        $branches        = Branche::where('com_code', $comCode)->orderBy('branch_name')->get(['id', 'branch_name']);
        $jobs_categories = Jobs_categories::where('com_code', $comCode)->orderBy('job_name')->get(['id', 'job_name']);
        $clients         = Client::where('com_code', $comCode)->where('active', 1)->orderBy('client_name')->get(['id', 'client_name']);
        $employees       = Employee::where('com_code', $comCode)->orderBy('employee_name_A')->get(['id', 'employee_id', 'employee_name_A', 'emp_sal']);

        return view('admin.salary_increases.create', compact('departments', 'branches', 'jobs_categories', 'clients', 'employees'));
    }

    private function validateRuleInput(Request $request): array
    {
        return $request->validate([
            'scope_type'      => 'required|in:global,department,branch,job,client,employee',
            'scope_id'        => 'nullable|integer',
            'method'          => 'required|in:fixed_amount,percentage',
            'value'           => 'required|numeric|min:0.01',
            'effective_date'  => 'required|date',
            'notes'           => 'nullable|string',
        ], [
            'scope_type.required' => 'اختر نطاق تطبيق الزيادة',
            'method.required'     => 'اختر طريقة الزيادة',
            'value.required'      => 'أدخل قيمة الزيادة',
            'value.min'           => 'قيمة الزيادة يجب أن تكون أكبر من صفر',
            'effective_date.required' => 'أدخل تاريخ سريان الزيادة',
        ]);
    }

    /** AJAX: preview affected employees + computed new salary, no DB writes. */
    public function preview(Request $request)
    {
        $data    = $this->validateRuleInput($request);
        $comCode = (int) Auth::guard('admin')->user()->com_code;

        if ($data['scope_type'] !== 'global' && empty($data['scope_id'])) {
            return response()->json(['error' => 'اختر عنصر النطاق (الإدارة/الفرع/الوظيفة/العميل/الموظف)'], 422);
        }

        $rule = new SalaryIncreaseRule([
            'scope_type' => $data['scope_type'],
            'scope_id'   => $data['scope_id'] ?? null,
            'method'     => $data['method'],
            'value'      => $data['value'],
        ]);

        $employees = $rule->matchedEmployeesQuery($comCode)->get(['id', 'employee_id', 'employee_name_A', 'emp_sal']);

        $rows = $employees->map(function (Employee $employee) use ($rule) {
            $current = (float) ($employee->emp_sal ?? 0);
            return [
                'id'              => $employee->id,
                'employee_id'     => $employee->employee_id,
                'employee_name_A' => $employee->employee_name_A,
                'current_salary'  => $current,
                'new_salary'      => $rule->computeNewSalary($current),
            ];
        });

        return response()->json([
            'count' => $rows->count(),
            'rows'  => $rows,
        ]);
    }

    public function store(Request $request)
    {
        $data    = $this->validateRuleInput($request);
        $comCode = (int) Auth::guard('admin')->user()->com_code;

        if ($data['scope_type'] !== 'global' && empty($data['scope_id'])) {
            return redirect()->back()->with('error', 'اختر عنصر النطاق (الإدارة/الفرع/الوظيفة/العميل/الموظف)')->withInput();
        }

        DB::beginTransaction();

        try {
            $rule = SalaryIncreaseRule::create([
                'com_code'       => $comCode,
                'scope_type'     => $data['scope_type'],
                'scope_id'       => $data['scope_id'] ?? null,
                'method'         => $data['method'],
                'value'          => $data['value'],
                'effective_date' => $data['effective_date'],
                'notes'          => $data['notes'] ?? null,
                'status'         => 1,
                'added_by'       => Auth::guard('admin')->id(),
            ]);

            $employees   = $rule->matchedEmployeesQuery($comCode)->get();
            $updatedCount = 0;

            EmployeeObserver::withContext([
                'source'        => 'bulk_increase',
                'ruleId'        => $rule->id,
                'method'        => $rule->method,
                'changeValue'   => $rule->value,
                'effectiveDate' => $rule->effective_date,
            ], function () use ($employees, $rule, &$updatedCount) {
                foreach ($employees as $employee) {
                    $newSalary = $rule->computeNewSalary((float) ($employee->emp_sal ?? 0));
                    if ($newSalary != $employee->emp_sal) {
                        $employee->update(['emp_sal' => $newSalary]);
                        $updatedCount++;
                    }
                }
            });

            DB::commit();

            return redirect()->route('salary_increases.index')
                ->with('success', "تم تطبيق الزيادة على {$updatedCount} موظف بنجاح");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'حدث خطأ أثناء تطبيق الزيادة: ' . $e->getMessage())->withInput();
        }
    }

    public function history($employeeId)
    {
        $comCode  = (int) Auth::guard('admin')->user()->com_code;
        $employee = Employee::where('com_code', $comCode)->findOrFail($employeeId);

        return response()->json(
            $employee->salaryHistory()->with('addedBy')->get()
        );
    }
}
