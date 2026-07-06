<?php
namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\{StockAdjustment, StockAdjustmentItem, Item, Warehouse};
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

class StockAdjustmentsController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    private function nextNumber(): string
    {
        $last = StockAdjustment::where('com_code', $this->comCode())->whereYear('created_at', now()->year)->max('adjustment_number');
        $num  = $last ? ((int) substr($last, -4)) + 1 : 1;
        return 'ADJ-'.now()->year.'-'.str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    public function index(Request $request)
    {
        $query = StockAdjustment::with('warehouse')->where('com_code', $this->comCode());
        if ($request->filled('warehouse_id')) $query->where('warehouse_id', $request->warehouse_id);
        if ($request->filled('status'))       $query->where('status', $request->status);
        $data       = $query->orderByDesc('date')->paginate(20);
        $warehouses = Warehouse::where('com_code', $this->comCode())->orderBy('name')->get();
        return view('admin.inventory.adjustments.index', compact('data', 'warehouses'));
    }

    public function create()
    {
        $warehouses = Warehouse::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        $items      = Item::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        $nextNumber = $this->nextNumber();
        return view('admin.inventory.adjustments.create', compact('warehouses', 'items', 'nextNumber'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'         => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'type'         => 'required|in:increase,decrease',
            'items'        => 'required|array|min:1',
        ]);

        DB::transaction(function () use ($request) {
            $adjustment = StockAdjustment::create([
                'com_code'          => $this->comCode(),
                'adjustment_number' => $this->nextNumber(),
                'date'              => $request->date,
                'warehouse_id'      => $request->warehouse_id,
                'type'              => $request->type,
                'reason'            => $request->reason,
                'status'            => 'draft',
                'notes'             => $request->notes,
                'created_by'        => Auth::guard('admin')->id(),
            ]);

            foreach ($request->items as $row) {
                if (empty($row['item_id']) || empty($row['qty'])) continue;
                StockAdjustmentItem::create([
                    'adjustment_id' => $adjustment->id,
                    'item_id'       => $row['item_id'],
                    'quantity'      => (float) $row['qty'],
                    'unit_cost'     => $row['unit_cost'] ?? null,
                    'notes'         => $row['notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('stock_adjustments.index')->with('success', 'تم إنشاء تسوية المخزون بنجاح');
    }

    public function show($id)
    {
        $adjustment = StockAdjustment::with(['warehouse', 'items.item', 'createdBy'])
            ->where('com_code', $this->comCode())->findOrFail($id);
        return view('admin.inventory.adjustments.show', compact('adjustment'));
    }

    public function approve($id)
    {
        $adjustment = StockAdjustment::with('items')->where('com_code', $this->comCode())->findOrFail($id);

        if ($adjustment->status !== 'draft') {
            return back()->with('error', 'تم البت في هذه التسوية بالفعل');
        }

        DB::transaction(function () use ($adjustment) {
            $sign = $adjustment->type === 'increase' ? 1 : -1;
            $movementType = $adjustment->type === 'increase' ? 'adjustment_in' : 'adjustment_out';

            foreach ($adjustment->items as $row) {
                StockService::adjustStock(
                    $adjustment->com_code,
                    $row->item_id,
                    $adjustment->warehouse_id,
                    $sign * $row->quantity,
                    $movementType,
                    'stock_adjustment',
                    $adjustment->id,
                    $row->unit_cost,
                    $adjustment->date,
                    $adjustment->reason,
                    Auth::guard('admin')->id()
                );
            }

            $adjustment->update(['status' => 'approved']);
        });

        return back()->with('success', 'تم اعتماد التسوية وتحديث أرصدة المخزون');
    }

    public function reject($id)
    {
        $adjustment = StockAdjustment::where('com_code', $this->comCode())->findOrFail($id);
        if ($adjustment->status !== 'draft') {
            return back()->with('error', 'تم البت في هذه التسوية بالفعل');
        }
        $adjustment->update(['status' => 'rejected']);
        return back()->with('success', 'تم رفض التسوية');
    }

    public function delete($id)
    {
        $adjustment = StockAdjustment::where('com_code', $this->comCode())->findOrFail($id);
        if ($adjustment->status === 'approved') {
            return back()->with('error', 'لا يمكن حذف تسوية معتمدة');
        }
        $adjustment->delete();
        return redirect()->route('stock_adjustments.index')->with('success', 'تم حذف التسوية');
    }
}
