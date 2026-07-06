<?php
namespace App\Http\Controllers\Admin\Manufacturing;

use App\Http\Controllers\Controller;
use App\Models\{BillOfMaterial, BillOfMaterialLine, Item, ItemUnit};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

class BillOfMaterialsController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function index()
    {
        $data = BillOfMaterial::with('item')->where('com_code', $this->comCode())->orderByDesc('id')->paginate(20);
        return view('admin.manufacturing.boms.index', compact('data'));
    }

    public function create()
    {
        $comCode = $this->comCode();
        // المنتجات التامة/نصف المصنعة فقط تصلح كمخرج لقائمة مواد
        $finishedItems = Item::where('com_code', $comCode)->whereIn('type', ['product', 'semi_finished'])->where('is_active', true)->orderBy('name')->get();
        $componentItems = Item::where('com_code', $comCode)->whereIn('type', ['raw_material', 'semi_finished'])->where('is_active', true)->orderBy('name')->get();
        $units = ItemUnit::where('com_code', $comCode)->where('is_active', true)->get();
        return view('admin.manufacturing.boms.create', compact('finishedItems', 'componentItems', 'units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_id'   => 'required|exists:items,id',
            'lines'     => 'required|array|min:1',
        ]);

        $comCode = $this->comCode();
        $lastVersion = BillOfMaterial::where('com_code', $comCode)->where('item_id', $request->item_id)->max('version');

        DB::transaction(function () use ($request, $comCode, $lastVersion) {
            $bom = BillOfMaterial::create([
                'com_code'        => $comCode,
                'item_id'         => $request->item_id,
                'version'         => ($lastVersion ?? 0) + 1,
                'output_quantity' => $request->output_quantity ?? 1,
                'is_active'       => true,
                'notes'           => $request->notes,
                'created_by'      => Auth::guard('admin')->id(),
            ]);

            foreach ($request->lines as $row) {
                if (empty($row['component_item_id']) || empty($row['quantity'])) continue;
                BillOfMaterialLine::create([
                    'bom_id'             => $bom->id,
                    'component_item_id'  => $row['component_item_id'],
                    'quantity'           => $row['quantity'],
                    'unit_id'            => $row['unit_id'] ?? null,
                    'scrap_percent'      => $row['scrap_percent'] ?? 0,
                    'notes'              => $row['notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('bill_of_materials.index')->with('success', 'تم إنشاء قائمة المواد بنجاح');
    }

    public function show($id)
    {
        $bom = BillOfMaterial::with(['item', 'lines.componentItem', 'lines.unit'])->where('com_code', $this->comCode())->findOrFail($id);
        return view('admin.manufacturing.boms.show', compact('bom'));
    }

    public function delete($id)
    {
        $bom = BillOfMaterial::where('com_code', $this->comCode())->findOrFail($id);
        if (\App\Models\ProductionOrder::where('bom_id', $bom->id)->exists()) {
            return back()->with('error', 'لا يمكن حذف قائمة مواد مرتبطة بأوامر إنتاج - عطّلها بدلاً من ذلك');
        }
        $bom->delete();
        return redirect()->route('bill_of_materials.index')->with('success', 'تم حذف قائمة المواد');
    }

    public function toggle($id)
    {
        $bom = BillOfMaterial::where('com_code', $this->comCode())->findOrFail($id);
        $bom->update(['is_active' => !$bom->is_active]);
        return back()->with('success', 'تم تحديث حالة قائمة المواد');
    }
}
