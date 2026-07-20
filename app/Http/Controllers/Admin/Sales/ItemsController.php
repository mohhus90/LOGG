<?php
namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ItemsController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function index(Request $request)
    {
        $query = Item::with(['category','unit'])->where('com_code', $this->comCode());
        if ($request->filled('search'))      $query->where('name', 'like', '%'.$request->search.'%');
        if ($request->filled('category_id')) $query->where('category_id', $request->category_id);
        if ($request->filled('type'))        $query->where('type', $request->type);
        if ($request->filled('is_active'))   $query->where('is_active', $request->is_active);
        $data       = $query->orderBy('name')->paginate(20);
        $categories = ItemCategory::where('com_code', $this->comCode())->orderBy('name')->get();
        return view('admin.sales.items.index', compact('data', 'categories'));
    }

    public function create()
    {
        $categories = ItemCategory::where('com_code', $this->comCode())->orderBy('name')->get();
        $units      = ItemUnit::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        return view('admin.sales.items.create', compact('categories', 'units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:200',
            'type'          => 'required|in:product,service,raw_material,semi_finished',
            'selling_price' => 'required|numeric|min:0',
        ]);

        $data = $request->only(['code','barcode','name','name_en','category_id','unit_id','type',
                                'cost_price','selling_price','min_selling_price','description','external_sku']);
        $data['com_code']  = $this->comCode();
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('items', 'public');
        }

        Item::create($data);
        return redirect()->route('items.index')->with('success', 'تم إضافة الصنف بنجاح');
    }

    public function show($id)
    {
        $item = Item::with(['category','unit'])->where('com_code', $this->comCode())->findOrFail($id);
        return view('admin.sales.items.show', compact('item'));
    }

    public function edit($id)
    {
        $item       = Item::where('com_code', $this->comCode())->findOrFail($id);
        $categories = ItemCategory::where('com_code', $this->comCode())->orderBy('name')->get();
        $units      = ItemUnit::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        return view('admin.sales.items.edit', compact('item', 'categories', 'units'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'          => 'required|string|max:200',
            'type'          => 'required|in:product,service,raw_material,semi_finished',
            'selling_price' => 'required|numeric|min:0',
        ]);
        $item = Item::where('com_code', $this->comCode())->findOrFail($id);
        $data = $request->only(['code','barcode','name','name_en','category_id','unit_id','type',
                                'cost_price','selling_price','min_selling_price','description','external_sku']);
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('items', 'public');
        }
        $item->update($data);
        return redirect()->route('items.index')->with('success', 'تم تعديل الصنف بنجاح');
    }

    public function delete($id)
    {
        Item::where('com_code', $this->comCode())->findOrFail($id)->delete();
        return redirect()->route('items.index')->with('success', 'تم حذف الصنف');
    }

    public function ajaxSearch(Request $request)
    {
        $items = Item::where('com_code', $this->comCode())
            ->where('is_active', true)
            ->where(function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->q.'%')
                  ->orWhere('code', 'like', '%'.$request->q.'%');
            })
            ->with('unit')
            ->limit(20)
            ->get(['id','code','name','selling_price','unit_id']);
        return response()->json($items);
    }
}
