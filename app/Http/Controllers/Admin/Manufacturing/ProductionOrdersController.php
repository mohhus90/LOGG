<?php
namespace App\Http\Controllers\Admin\Manufacturing;

use App\Http\Controllers\Controller;
use App\Models\{ProductionOrder, BillOfMaterial, Warehouse, Branche};
use App\Services\Manufacturing\ProductionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductionOrdersController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function index(Request $request)
    {
        $query = ProductionOrder::with(['item', 'bom'])->where('com_code', $this->comCode());
        if ($request->filled('status')) $query->where('status', $request->status);
        $data = $query->orderByDesc('id')->paginate(20);
        return view('admin.manufacturing.orders.index', compact('data'));
    }

    public function create()
    {
        $comCode    = $this->comCode();
        $boms       = BillOfMaterial::with('item')->where('com_code', $comCode)->where('is_active', true)->get();
        $warehouses = Warehouse::where('com_code', $comCode)->where('is_active', true)->orderBy('name')->get();
        $branches   = Branche::where('com_code', $comCode)->get();
        return view('admin.manufacturing.orders.create', compact('boms', 'warehouses', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bom_id'               => 'required|exists:bill_of_materials,id',
            'planned_quantity'     => 'required|numeric|min:0.001',
            'source_warehouse_id'  => 'required|exists:warehouses,id',
            'target_warehouse_id'  => 'required|exists:warehouses,id',
        ]);

        $order = ProductionService::createFromBom($this->comCode(), (int) $request->bom_id, (float) $request->planned_quantity, $request->all(), Auth::guard('admin')->id());

        return redirect()->route('production_orders.show', $order->id)->with('success', 'تم إنشاء أمر الإنتاج بنجاح');
    }

    public function show($id)
    {
        $order = ProductionOrder::with(['item', 'bom', 'sourceWarehouse', 'targetWarehouse', 'materials.item', 'receipts'])
            ->where('com_code', $this->comCode())->findOrFail($id);
        return view('admin.manufacturing.orders.show', compact('order'));
    }

    public function issueMaterials(Request $request, $id)
    {
        $order = ProductionOrder::where('com_code', $this->comCode())->findOrFail($id);
        if ($order->status === 'completed' || $order->status === 'cancelled') {
            return back()->with('error', 'لا يمكن صرف مواد لأمر مكتمل أو ملغي');
        }

        $lines = [];
        foreach ($request->input('materials', []) as $materialId => $qty) {
            if ((float) $qty > 0) $lines[] = ['material_id' => $materialId, 'quantity' => (float) $qty];
        }

        ProductionService::issueMaterials($order, $lines, Auth::guard('admin')->id());
        return back()->with('success', 'تم صرف المواد الخام للتشغيل');
    }

    public function receiveFinishedGoods(Request $request, $id)
    {
        $order = ProductionOrder::where('com_code', $this->comCode())->findOrFail($id);
        $request->validate(['quantity' => 'required|numeric|min:0.001']);

        if ($order->status === 'completed' || $order->status === 'cancelled') {
            return back()->with('error', 'لا يمكن استلام إنتاج لأمر مكتمل أو ملغي');
        }

        ProductionService::receiveFinishedGoods($order, (float) $request->quantity, Auth::guard('admin')->id());
        return back()->with('success', 'تم استلام الإنتاج التام بنجاح');
    }

    public function complete($id)
    {
        $order = ProductionOrder::where('com_code', $this->comCode())->findOrFail($id);
        ProductionService::complete($order);
        return back()->with('success', 'تم إغلاق أمر الإنتاج');
    }

    public function cancel($id)
    {
        $order = ProductionOrder::where('com_code', $this->comCode())->findOrFail($id);
        ProductionService::cancel($order);
        return back()->with('success', 'تم إلغاء أمر الإنتاج');
    }
}
