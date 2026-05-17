<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Deduction;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

class DeductionsController extends Controller
{
    private function comCode(): int
    {
        return (int) Auth::guard('admin')->user()->com_code;
    }

    private function employees()
    {
        return Employee::where('com_code', $this->comCode())
            ->orderBy('employee_name_A')->get();
    }

    public function index(Request $request)
    {
        $employees = $this->employees();

        // ✅ FIX: فلترة بـ com_code
        $query = Deduction::with('employee')
            ->where('com_code', $this->comCode());

        if ($request->filled('employee_id')) $query->where('employee_id', $request->employee_id);
        if ($request->filled('month'))       $query->where('month', $request->month);
        if ($request->filled('year'))        $query->where('year', $request->year);
        if ($request->filled('status'))      $query->where('status', $request->status);

        $data = $query->orderByDesc('deduction_date')->paginate(20);
        return view('admin.deductions.index', compact('data', 'employees'));
    }

    public function create()
    {
        $employees = $this->employees();
        return view('admin.deductions.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id'    => 'required|exists:employees,id',
            'deduction_date' => 'required|date',
            'amount'         => 'required|numeric|min:0.01',
            'month'          => 'required|integer|between:1,12',
            'year'           => 'required|integer|min:2020',
        ]);

        Deduction::create([
            'employee_id'    => $request->employee_id,
            'deduction_date' => $request->deduction_date,
            'deduction_type' => $request->deduction_type,
            'amount'         => $request->amount,
            'month'          => $request->month,
            'year'           => $request->year,
            'status'         => $request->status ?? 1,
            'notes'          => $request->notes,
            // ✅ FIX: com_code من الأدمن
            'com_code'       => $this->comCode(),
            'added_by'       => Auth::guard('admin')->id(),
        ]);

        return redirect()->route('deductions.index')->with('success', 'تم إضافة الخصم بنجاح');
    }

    public function edit(int $id)
    {
        // ✅ FIX: فلترة بـ com_code
        $deduction = Deduction::where('com_code', $this->comCode())->findOrFail($id);
        $employees = $this->employees();
        return view('admin.deductions.edit', compact('deduction', 'employees'));
    }

    public function update(Request $request, int $id)
    {
        // ✅ FIX: فلترة بـ com_code
        $deduction = Deduction::where('com_code', $this->comCode())->findOrFail($id);
        $deduction->update(
            array_merge(
                $request->except('_token', '_method'),
                ['updated_by' => Auth::guard('admin')->id()]
            )
        );
        return redirect()->route('deductions.index')->with('success', 'تم تحديث الخصم بنجاح');
    }

    public function delete(int $id)
    {
        // ✅ FIX: فلترة بـ com_code
        Deduction::where('com_code', $this->comCode())->findOrFail($id)->delete();
        return redirect()->route('deductions.index')->with('success', 'تم حذف الخصم بنجاح');
    }
}