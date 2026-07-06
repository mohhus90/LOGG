<?php
namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\ItemUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ItemUnitsController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function index()
    {
        $data = ItemUnit::where('com_code', $this->comCode())->orderBy('name')->paginate(20);
        return view('admin.sales.item_units.index', compact('data'));
    }

    public function create() { return view('admin.sales.item_units.create'); }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);
        ItemUnit::create([
            'com_code'  => $this->comCode(),
            'name'      => $request->name,
            'name_en'   => $request->name_en,
            'symbol'    => $request->symbol,
            'is_active' => $request->boolean('is_active', true),
        ]);
        return redirect()->route('item_units.index')->with('success', 'تم إضافة وحدة القياس بنجاح');
    }

    public function edit($id)
    {
        $unit = ItemUnit::where('com_code', $this->comCode())->findOrFail($id);
        return view('admin.sales.item_units.edit', compact('unit'));
    }

    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:100']);
        $unit = ItemUnit::where('com_code', $this->comCode())->findOrFail($id);
        $unit->update([
            'name'      => $request->name,
            'name_en'   => $request->name_en,
            'symbol'    => $request->symbol,
            'is_active' => $request->boolean('is_active', true),
        ]);
        return redirect()->route('item_units.index')->with('success', 'تم تعديل وحدة القياس بنجاح');
    }

    public function delete($id)
    {
        ItemUnit::where('com_code', $this->comCode())->findOrFail($id)->delete();
        return redirect()->route('item_units.index')->with('success', 'تم حذف وحدة القياس');
    }
}
