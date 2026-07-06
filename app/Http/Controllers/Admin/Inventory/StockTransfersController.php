<?php
namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\{StockTransfer, StockTransferItem, Item, Warehouse};
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

class StockTransfersController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    private function nextNumber(): string
    {
        $last = StockTransfer::where('com_code', $this->comCode())->whereYear('created_at', now()->year)->max('transfer_number');
        $num  = $last ? ((int) substr($last, -4)) + 1 : 1;
        return 'TRF-'.now()->year.'-'.str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    public function index(Request $request)
    {
        $query = StockTransfer::with(['fromWarehouse', 'toWarehouse'])->where('com_code', $this->comCode());
        if ($request->filled('status')) $query->where('status', $request->status);
        $data       = $query->orderByDesc('date')->paginate(20);
        $warehouses = Warehouse::where('com_code', $this->comCode())->orderBy('name')->get();
        return view('admin.inventory.transfers.index', compact('data', 'warehouses'));
    }

    public function create()
    {
        $warehouses = Warehouse::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        $items      = Item::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        $nextNumber = $this->nextNumber();
        return view('admin.inventory.transfers.create', compact('warehouses', 'items', 'nextNumber'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'              => 'required|date',
            'from_warehouse_id' => 'required|exists:warehouses,id|different:to_warehouse_id',
            'to_warehouse_id'   => 'required|exists:warehouses,id',
            'items'             => 'required|array|min:1',
        ]);

        DB::transaction(function () use ($request) {
            $transfer = StockTransfer::create([
                'com_code'          => $this->comCode(),
                'transfer_number'   => $this->nextNumber(),
                'date'              => $request->date,
                'from_warehouse_id' => $request->from_warehouse_id,
                'to_warehouse_id'   => $request->to_warehouse_id,
                'status'            => 'draft',
                'notes'             => $request->notes,
                'created_by'        => Auth::guard('admin')->id(),
            ]);

            foreach ($request->items as $row) {
                if (empty($row['item_id']) || empty($row['qty'])) continue;
                StockTransferItem::create([
                    'transfer_id' => $transfer->id,
                    'item_id'     => $row['item_id'],
                    'quantity'    => (float) $row['qty'],
                ]);
            }
        });

        return redirect()->route('stock_transfers.index')->with('success', 'تم إنشاء طلب التحويل بنجاح');
    }

    public function show($id)
    {
        $transfer = StockTransfer::with(['fromWarehouse', 'toWarehouse', 'items.item', 'createdBy'])
            ->where('com_code', $this->comCode())->findOrFail($id);
        return view('admin.inventory.transfers.show', compact('transfer'));
    }

    public function complete($id)
    {
        $transfer = StockTransfer::with('items')->where('com_code', $this->comCode())->findOrFail($id);
        if ($transfer->status !== 'draft') {
            return back()->with('error', 'تم تنفيذ أو إلغاء هذا التحويل بالفعل');
        }

        DB::transaction(function () use ($transfer) {
            foreach ($transfer->items as $row) {
                StockService::adjustStock(
                    $transfer->com_code, $row->item_id, $transfer->from_warehouse_id, -$row->quantity,
                    'transfer_out', 'stock_transfer', $transfer->id, null, $transfer->date,
                    'تحويل إلى مخزن آخر', Auth::guard('admin')->id()
                );
                StockService::adjustStock(
                    $transfer->com_code, $row->item_id, $transfer->to_warehouse_id, $row->quantity,
                    'transfer_in', 'stock_transfer', $transfer->id, null, $transfer->date,
                    'تحويل من مخزن آخر', Auth::guard('admin')->id()
                );
            }
            $transfer->update(['status' => 'completed']);
        });

        return back()->with('success', 'تم تنفيذ التحويل وتحديث أرصدة المخزون');
    }

    public function cancel($id)
    {
        $transfer = StockTransfer::where('com_code', $this->comCode())->findOrFail($id);
        if ($transfer->status !== 'draft') {
            return back()->with('error', 'تم تنفيذ أو إلغاء هذا التحويل بالفعل');
        }
        $transfer->update(['status' => 'cancelled']);
        return back()->with('success', 'تم إلغاء التحويل');
    }

    public function delete($id)
    {
        $transfer = StockTransfer::where('com_code', $this->comCode())->findOrFail($id);
        if ($transfer->status === 'completed') {
            return back()->with('error', 'لا يمكن حذف تحويل منفذ');
        }
        $transfer->delete();
        return redirect()->route('stock_transfers.index')->with('success', 'تم حذف التحويل');
    }
}
