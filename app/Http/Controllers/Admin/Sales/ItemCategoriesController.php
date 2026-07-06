<?php
namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ItemCategoriesController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function index()
    {
        $data = ItemCategory::where('com_code', $this->comCode())->with('parent')->orderBy('name')->paginate(20);
        return view('admin.sales.item_categories.index', compact('data'));
    }

    public function create()
    {
        $parents = ItemCategory::where('com_code', $this->comCode())->whereNull('parent_id')->orderBy('name')->get();
        return view('admin.sales.item_categories.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:150']);
        ItemCategory::create([
            'com_code'  => $this->comCode(),
            'code'      => $request->code,
            'name'      => $request->name,
            'name_en'   => $request->name_en,
            'parent_id' => $request->parent_id ?: null,
            'is_active' => $request->boolean('is_active', true),
        ]);
        return redirect()->route('item_categories.index')->with('success', 'تم إضافة المجموعة بنجاح');
    }

    public function edit($id)
    {
        $cat     = ItemCategory::where('com_code', $this->comCode())->findOrFail($id);
        $parents = ItemCategory::where('com_code', $this->comCode())->whereNull('parent_id')->where('id', '!=', $id)->orderBy('name')->get();
        return view('admin.sales.item_categories.edit', compact('cat', 'parents'));
    }

    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:150']);
        $cat = ItemCategory::where('com_code', $this->comCode())->findOrFail($id);
        $cat->update([
            'code'      => $request->code,
            'name'      => $request->name,
            'name_en'   => $request->name_en,
            'parent_id' => $request->parent_id ?: null,
            'is_active' => $request->boolean('is_active', true),
        ]);
        return redirect()->route('item_categories.index')->with('success', 'تم تعديل المجموعة بنجاح');
    }

    public function delete($id)
    {
        ItemCategory::where('com_code', $this->comCode())->findOrFail($id)->delete();
        return redirect()->route('item_categories.index')->with('success', 'تم حذف المجموعة');
    }
}
