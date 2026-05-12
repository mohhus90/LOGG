<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Advance;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

class AdvancesController extends Controller
{
    public function index(Request $request)
    {
        $employees = Employee::orderBy('employee_name_A')->get();
        $query = Advance::with('employee');

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
        $employees = Employee::orderBy('employee_name_A')->get();
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
            'com_code'             => Auth::guard('admin')->user()->com_code,
            'added_by'             => Auth::guard('admin')->id(),
        ]);

        return redirect()->route('advances.index')->with('success', 'تم إضافة السلفة بنجاح');
    }

    public function edit(int $id)
    {
        $advance   = Advance::findOrFail($id);
        $employees = Employee::orderBy('employee_name_A')->get();
        return view('admin.advances.edit', compact('advance', 'employees'));
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'installments' => 'required|integer|min:1',
            'status'       => 'required|integer|between:1,3',
        ]);

        $advance = Advance::findOrFail($id);
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
        Advance::findOrFail($id)->delete();
        return redirect()->route('advances.index')->with('success', 'تم حذف السلفة بنجاح');
    }
}