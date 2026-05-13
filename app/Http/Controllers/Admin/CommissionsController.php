<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Commission;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

class CommissionsController extends Controller
{
    public function index(Request $request)
    {
        $employees = Employee::orderBy('employee_name_A')->get();
        $query = Commission::with('employee');

        if ($request->filled('employee_id')) $query->where('employee_id', $request->employee_id);
        if ($request->filled('month')) $query->where('month', $request->month);
        if ($request->filled('year'))  $query->where('year', $request->year);
        if ($request->filled('status')) $query->where('status', $request->status);

        $data = $query->orderByDesc('commission_date')->paginate(20);
        return view('admin.commissions.index', compact('data', 'employees'));
    }

    public function create()
    {
        $employees = Employee::orderBy('employee_name_A')->get();
        return view('admin.commissions.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id'      => 'required|exists:employees,id',
            'commission_date'  => 'required|date',
            'amount'           => 'required|numeric|min:0.01',
            'month'            => 'required|integer|between:1,12',
            'year'             => 'required|integer|min:2020',
        ]);

        Commission::create([
            'employee_id'      => $request->employee_id,
            'commission_date'  => $request->commission_date,
            'commission_type'  => $request->commission_type,
            'amount'           => $request->amount,
            'month'            => $request->month,
            'year'             => $request->year,
            'status'           => $request->status ?? 1,
            'notes'            => $request->notes,
            'com_code'         => Auth::guard('admin')->user()->com_code,
            'added_by'         => Auth::guard('admin')->id(),
        ]);

        return redirect()->route('commissions.index')->with('success', 'تم إضافة العمولة بنجاح');
    }

    public function edit(int $id)
    {
        $commission = Commission::findOrFail($id);
        $employees  = Employee::orderBy('employee_name_A')->get();
        return view('admin.commissions.edit', compact('commission', 'employees'));
    }

    public function update(Request $request, int $id)
    {
        $commission = Commission::findOrFail($id);
        $commission->update(array_merge($request->except('_token'), ['updated_by' => Auth::guard('admin')->id()]));
        return redirect()->route('commissions.index')->with('success', 'تم تحديث العمولة بنجاح');
    }

    public function delete(int $id)
    {
        Commission::findOrFail($id)->delete();
        return redirect()->route('commissions.index')->with('success', 'تم حذف العمولة بنجاح');
    }
}
