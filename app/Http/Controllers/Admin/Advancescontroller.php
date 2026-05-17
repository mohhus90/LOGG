<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Advance;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

class AdvancesController extends Controller
{
    // ✅ FIX: مساعد مركزي لـ com_code
    private function comCode(): int
    {
        return (int) Auth::guard('admin')->user()->com_code;
    }

    // ✅ FIX: إضافة com_code filter لجلب الموظفين
    private function employees()
    {
        return Employee::where('com_code', $this->comCode())
            ->orderBy('employee_name_A')->get();
    }

    public function index(Request $request)
    {
        $employees = $this->employees();

        // ✅ FIX: فلترة بـ com_code
        $query = Advance::with('employee')
            ->where('com_code', $this->comCode());

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $data = $query->orderByDesc('advance_date')->paginate(20);
        return view('admin.advances.index', compact('data', 'employees'));
    }

    public function create()
    {
        $employees = $this->employees();
        return view('admin.advances.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id'  => 'required|exists:employees,id',
            'advance_date' => 'required|date',
            'amount'       => 'required|numeric|min:1',
            'installments' => 'required|integer|min:1',
        ], [
            'employee_id.required' => 'اختر الموظف',
            'amount.required'      => 'أدخل قيمة السلفة',
            'installments.required'=> 'أدخل عدد الأقساط',
        ]);

        $monthlyInstallment = round($request->amount / $request->installments, 2);

        Advance::create([
            'employee_id'          => $request->employee_id,
            'advance_date'         => $request->advance_date,
            'amount'               => $request->amount,
            'installments'         => $request->installments,
            'monthly_installment'  => $monthlyInstallment,
            'remaining_amount'     => $request->amount,
            'status'               => 1,
            'notes'                => $request->notes,
            // ✅ FIX: com_code من الأدمن
            'com_code'             => $this->comCode(),
            'added_by'             => Auth::guard('admin')->id(),
        ]);

        return redirect()->route('advances.index')->with('success', 'تم إضافة السلفة بنجاح');
    }

    public function edit(int $id)
    {
        // ✅ FIX: فلترة بـ com_code لمنع الوصول لسجلات شركات أخرى
        $advance   = Advance::where('com_code', $this->comCode())->findOrFail($id);
        $employees = $this->employees();
        return view('admin.advances.edit', compact('advance', 'employees'));
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'installments' => 'required|integer|min:1',
            'status'       => 'required|integer|between:1,3',
        ]);

        // ✅ FIX: فلترة بـ com_code
        $advance = Advance::where('com_code', $this->comCode())->findOrFail($id);

        $advance->update([
            'installments'         => $request->installments,
            'monthly_installment'  => round($advance->amount / $request->installments, 2),
            'remaining_amount'     => $request->remaining_amount ?? $advance->remaining_amount,
            'status'               => $request->status,
            'notes'                => $request->notes,
            'updated_by'           => Auth::guard('admin')->id(),
        ]);

        return redirect()->route('advances.index')->with('success', 'تم تحديث السلفة بنجاح');
    }

    public function delete(int $id)
    {
        // ✅ FIX: فلترة بـ com_code
        Advance::where('com_code', $this->comCode())->findOrFail($id)->delete();
        return redirect()->route('advances.index')->with('success', 'تم حذف السلفة بنجاح');
    }
}