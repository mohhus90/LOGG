<?php
namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\{Branche, CashBox, PosRegister, Warehouse};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PosRegistersController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function index()
    {
        $registers = PosRegister::with(['cashBox', 'warehouse', 'branch'])
            ->where('com_code', $this->comCode())->orderBy('name')->paginate(20);
        return view('admin.sales.pos.registers_index', compact('registers'));
    }

    public function create()
    {
        [$cashBoxes, $warehouses, $branches] = $this->formOptions();
        return view('admin.sales.pos.registers_create', compact('cashBoxes', 'warehouses', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:150',
            'cash_box_id'  => 'required|exists:cash_boxes,id',
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        PosRegister::create([
            'com_code'     => $this->comCode(),
            'name'         => $request->name,
            'cash_box_id'  => $request->cash_box_id,
            'warehouse_id' => $request->warehouse_id,
            'branch_id'    => $request->branch_id,
            'is_active'    => $request->boolean('is_active', true),
        ]);

        return redirect()->route('pos_registers.index')->with('success', 'تم إضافة الكاشير بنجاح');
    }

    public function edit($id)
    {
        $register = PosRegister::where('com_code', $this->comCode())->findOrFail($id);
        [$cashBoxes, $warehouses, $branches] = $this->formOptions();
        return view('admin.sales.pos.registers_edit', compact('register', 'cashBoxes', 'warehouses', 'branches'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'         => 'required|string|max:150',
            'cash_box_id'  => 'required|exists:cash_boxes,id',
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        $register = PosRegister::where('com_code', $this->comCode())->findOrFail($id);
        $register->update([
            'name'         => $request->name,
            'cash_box_id'  => $request->cash_box_id,
            'warehouse_id' => $request->warehouse_id,
            'branch_id'    => $request->branch_id,
            'is_active'    => $request->boolean('is_active', true),
        ]);

        return redirect()->route('pos_registers.index')->with('success', 'تم تحديث الكاشير بنجاح');
    }

    private function formOptions(): array
    {
        $comCode = $this->comCode();
        return [
            CashBox::where('com_code', $comCode)->where('is_active', true)->orderBy('name')->get(),
            Warehouse::where('com_code', $comCode)->where('is_active', true)->orderBy('name')->get(),
            Branche::where('com_code', $comCode)->get(),
        ];
    }
}
