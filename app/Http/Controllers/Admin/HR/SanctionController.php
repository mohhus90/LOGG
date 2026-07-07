<?php

namespace App\Http\Controllers\Admin\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\EmployeeSanction;
use App\Models\Employee;
use App\Services\SmsService;

class SanctionController extends Controller
{
    private function comCode(): int
    {
        return (int) Auth::guard('admin')->user()->com_code;
    }

    public function index(Request $request)
    {
        $comCode   = $this->comCode();
        $employees = Employee::where('com_code', $comCode)->orderBy('employee_name_A')->get();

        $query = EmployeeSanction::with('employee')
            ->where('com_code', $comCode);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('from_date')) {
            $query->where('date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('date', '<=', $request->to_date);
        }
        // 'all' = الكل بدون فلتر، أي قيمة أخرى (1/0) تُفلتر، غياب الـ param = افتراضي فعّال
        $status = $request->input('status', '1');
        if ($status !== 'all') {
            $query->where('status', (int)$status);
        }

        $perPage = in_array((int)$request->get('per_page', 20), [10, 20, 50, 100])
            ? (int)$request->get('per_page', 20) : 20;

        $data = $query->orderByDesc('date')->paginate($perPage);

        return view('admin.sanctions.index', compact('data', 'employees'));
    }

    public function create()
    {
        $comCode   = $this->comCode();
        $employees = Employee::where('com_code', $comCode)->orderBy('employee_name_A')->get();
        return view('admin.sanctions.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id'     => 'required|exists:employees,id',
            'type'            => 'required|integer|in:1,2,3,4,5',
            'date'            => 'required|date',
            'amount'          => 'nullable|numeric|min:0',
            'suspension_days' => 'nullable|integer|min:0',
            'deduct_days'       => 'nullable|numeric|min:0|max:30',
            'deduct_month_day'  => 'nullable|regex:/^\d{4}-\d{2}$/',
            'description'       => 'nullable|string|max:1000',
            'deduct_month'      => 'nullable|regex:/^\d{4}-\d{2}$/',
        ], [
            'employee_id.required' => 'اختر الموظف',
            'type.required'        => 'اختر نوع الجزاء',
            'date.required'        => 'أدخل تاريخ الجزاء',
        ]);

        $comCode = $this->comCode();
        $type    = (int)$request->type;

        // شهر الاستقطاع: type=3 يستخدم deduct_month، type=5 يستخدم deduct_month_day
        $deductMonth = null;
        if ($type === 3 && $request->filled('deduct_month')) {
            $deductMonth = $request->deduct_month;
        } elseif ($type === 5 && $request->filled('deduct_month_day')) {
            $deductMonth = $request->deduct_month_day;
        }

        EmployeeSanction::create([
            'com_code'        => $comCode,
            'employee_id'     => $request->employee_id,
            'type'            => $type,
            'amount'          => $type === 3 ? (float)($request->amount ?? 0) : 0,
            'suspension_days' => $type === 4 ? (int)($request->suspension_days ?? 0) : 0,
            'deduct_days'     => $type === 5 ? (float)($request->deduct_days ?? 0) : 0,
            'description'     => $request->description,
            'date'            => $request->date,
            'deduct_month'    => $deductMonth,
            'status'          => 1,
            'added_by'        => Auth::guard('admin')->id(),
        ]);

        // SMS إشعار الجزاء
        $employee = Employee::find($request->employee_id);
        if ($employee && $employee->emp_mobile) {
            try {
                (new SmsService($comCode))->sendSanctionCreated($employee->emp_mobile, $employee->employee_name_A);
            } catch (\Exception $e) {
                Log::warning('SMS sanction failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('sanctions.index')
            ->with('success', 'تم إضافة الجزاء بنجاح');
    }

    public function edit(int $id)
    {
        $sanction  = EmployeeSanction::where('com_code', $this->comCode())->findOrFail($id);
        $employees = Employee::where('com_code', $this->comCode())->orderBy('employee_name_A')->get();
        return view('admin.sanctions.edit', compact('sanction', 'employees'));
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'type'            => 'required|integer|in:1,2,3,4,5',
            'date'            => 'required|date',
            'amount'          => 'nullable|numeric|min:0',
            'suspension_days' => 'nullable|integer|min:0',
            'deduct_days'      => 'nullable|numeric|min:0|max:30',
            'deduct_month_day' => 'nullable|regex:/^\d{4}-\d{2}$/',
            'description'      => 'nullable|string|max:1000',
            'deduct_month'     => 'nullable|regex:/^\d{4}-\d{2}$/',
        ]);

        $sanction = EmployeeSanction::where('com_code', $this->comCode())->findOrFail($id);
        $type     = (int)$request->type;

        $deductMonth = null;
        if ($type === 3 && $request->filled('deduct_month')) {
            $deductMonth = $request->deduct_month;
        } elseif ($type === 5 && $request->filled('deduct_month_day')) {
            $deductMonth = $request->deduct_month_day;
        }

        $sanction->update([
            'type'            => $type,
            'amount'          => $type === 3 ? (float)($request->amount ?? 0) : 0,
            'suspension_days' => $type === 4 ? (int)($request->suspension_days ?? 0) : 0,
            'deduct_days'     => $type === 5 ? (float)($request->deduct_days ?? 0) : 0,
            'description'     => $request->description,
            'date'            => $request->date,
            'deduct_month'    => $deductMonth,
            'updated_by'      => Auth::guard('admin')->id(),
        ]);

        return redirect()->route('sanctions.index')
            ->with('success', 'تم تحديث الجزاء بنجاح');
    }

    public function cancel(int $id)
    {
        $sanction = EmployeeSanction::where('com_code', $this->comCode())->findOrFail($id);
        $sanction->update(['status' => 0, 'updated_by' => Auth::guard('admin')->id()]);
        return redirect()->route('sanctions.index')
            ->with('success', 'تم إلغاء الجزاء');
    }

    public function delete(int $id)
    {
        EmployeeSanction::where('com_code', $this->comCode())->findOrFail($id)->delete();
        return redirect()->route('sanctions.index')
            ->with('success', 'تم حذف الجزاء نهائياً');
    }
}
