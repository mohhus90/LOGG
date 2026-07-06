<?php
namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\Branche;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WarehousesController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function index(Request $request)
    {
        $query = Warehouse::where('com_code', $this->comCode());
        if ($request->filled('search'))    $query->where('name', 'like', '%'.$request->search.'%');
        if ($request->filled('is_active')) $query->where('is_active', $request->is_active);
        $data = $query->orderBy('name')->paginate(20);
        return view('admin.inventory.warehouses.index', compact('data'));
    }

    public function create()
    {
        $branches = Branche::where('com_code', $this->comCode())->get();
        return view('admin.inventory.warehouses.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:200']);

        if ($request->boolean('is_default')) {
            Warehouse::where('com_code', $this->comCode())->update(['is_default' => false]);
        }

        Warehouse::create([
            'com_code'   => $this->comCode(),
            'code'       => $request->code,
            'name'       => $request->name,
            'branch_id'  => $request->branch_id,
            'location'   => $request->location,
            'is_default' => $request->boolean('is_default'),
            'is_active'  => $request->boolean('is_active', true),
            'notes'      => $request->notes,
        ]);

        return redirect()->route('warehouses.index')->with('success', 'تم إضافة المخزن بنجاح');
    }

    public function edit($id)
    {
        $warehouse = Warehouse::where('com_code', $this->comCode())->findOrFail($id);
        $branches  = Branche::where('com_code', $this->comCode())->get();
        return view('admin.inventory.warehouses.edit', compact('warehouse', 'branches'));
    }

    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:200']);
        $warehouse = Warehouse::where('com_code', $this->comCode())->findOrFail($id);

        if ($request->boolean('is_default')) {
            Warehouse::where('com_code', $this->comCode())->where('id', '!=', $id)->update(['is_default' => false]);
        }

        $warehouse->update([
            'code'       => $request->code,
            'name'       => $request->name,
            'branch_id'  => $request->branch_id,
            'location'   => $request->location,
            'is_default' => $request->boolean('is_default'),
            'is_active'  => $request->boolean('is_active', true),
            'notes'      => $request->notes,
        ]);

        return redirect()->route('warehouses.index')->with('success', 'تم تعديل المخزن بنجاح');
    }

    public function delete($id)
    {
        Warehouse::where('com_code', $this->comCode())->findOrFail($id)->delete();
        return redirect()->route('warehouses.index')->with('success', 'تم حذف المخزن');
    }
}
