<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bonus;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

class BonusesController extends Controller
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

        $query = Bonus::with('employee')->where('com_code', $this->comCode());

        if ($request->filled('employee_id')) $query->where('employee_id', $request->employee_id);
        if ($request->filled('month'))       $query->where('month', $request->month);
        if ($request->filled('year'))        $query->where('year', $request->year);
        if ($request->filled('status'))      $query->where('status', $request->status);
        if ($request->filled('bonus_type'))  $query->where('bonus_type', $request->bonus_type);

        $data = $query->orderByDesc('bonus_date')->paginate(20);
        return view('admin.bonuses.index', compact('data', 'employees'));
    }

    public function create()
    {
        $employees = $this->employees();
        return view('admin.bonuses.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'bonus_date'  => 'required|date',
            'bonus_type'  => 'required|in:1,2',
            'month'       => 'required|integer|between:1,12',
            'year'        => 'required|integer|min:2020',
            'amount'      => 'required_if:bonus_type,1|nullable|numeric|min:0.01',
            'days'        => 'required_if:bonus_type,2|nullable|numeric|min:0.01',
            'day_multiplier' => 'nullable|numeric|min:0.01',
        ]);

        Bonus::create([
            'employee_id'    => $request->employee_id,
            'bonus_date'     => $request->bonus_date,
            'bonus_type'     => $request->bonus_type,
            'amount'         => $request->bonus_type == 1 ? $request->amount : null,
            'days'           => $request->bonus_type == 2 ? $request->days : null,
            'day_multiplier' => $request->bonus_type == 2 ? ($request->day_multiplier ?? 1) : 1,
            'month'          => $request->month,
            'year'           => $request->year,
            'status'         => $request->status ?? 1,
            'notes'          => $request->notes,
            'com_code'       => $this->comCode(),
            'added_by'       => Auth::guard('admin')->id(),
        ]);

        return redirect()->route('bonuses.index')->with('success', 'تم إضافة المكافأة بنجاح');
    }

    public function edit(int $id)
    {
        $bonus     = Bonus::where('com_code', $this->comCode())->findOrFail($id);
        $employees = $this->employees();
        return view('admin.bonuses.edit', compact('bonus', 'employees'));
    }

    public function update(Request $request, int $id)
    {
        $bonus = Bonus::where('com_code', $this->comCode())->findOrFail($id);

        $request->validate([
            'bonus_date'  => 'required|date',
            'bonus_type'  => 'required|in:1,2',
            'month'       => 'required|integer|between:1,12',
            'year'        => 'required|integer|min:2020',
            'amount'      => 'required_if:bonus_type,1|nullable|numeric|min:0.01',
            'days'        => 'required_if:bonus_type,2|nullable|numeric|min:0.01',
            'day_multiplier' => 'nullable|numeric|min:0.01',
        ]);

        $bonus->update([
            'bonus_date'     => $request->bonus_date,
            'bonus_type'     => $request->bonus_type,
            'amount'         => $request->bonus_type == 1 ? $request->amount : null,
            'days'           => $request->bonus_type == 2 ? $request->days : null,
            'day_multiplier' => $request->bonus_type == 2 ? ($request->day_multiplier ?? 1) : 1,
            'month'          => $request->month,
            'year'           => $request->year,
            'status'         => $request->status ?? $bonus->status,
            'notes'          => $request->notes,
            'updated_by'     => Auth::guard('admin')->id(),
        ]);

        return redirect()->route('bonuses.index')->with('success', 'تم تحديث المكافأة بنجاح');
    }

    public function delete(int $id)
    {
        Bonus::where('com_code', $this->comCode())->findOrFail($id)->delete();
        return redirect()->route('bonuses.index')->with('success', 'تم حذف المكافأة بنجاح');
    }
}
