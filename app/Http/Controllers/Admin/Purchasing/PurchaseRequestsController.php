<?php
namespace App\Http\Controllers\Admin\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\{PurchaseRequest, PurchaseRequestItem, PurchaseOrder, PurchaseOrderItem, Item, ItemUnit, Branche};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseRequestsController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    private function nextNumber(): string
    {
        $last = PurchaseRequest::where('com_code', $this->comCode())
            ->whereYear('created_at', now()->year)->max('request_number');
        $num  = $last ? ((int) substr($last, -4)) + 1 : 1;
        return 'PR-'.now()->year.'-'.str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    public function index(Request $request)
    {
        $query = PurchaseRequest::where('com_code', $this->comCode());
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('from'))   $query->whereDate('date', '>=', $request->from);
        if ($request->filled('to'))     $query->whereDate('date', '<=', $request->to);
        $data = $query->orderByDesc('date')->paginate(20);
        return view('admin.purchasing.requests.index', compact('data'));
    }

    public function create()
    {
        $branches   = Branche::where('com_code', $this->comCode())->get();
        $units      = ItemUnit::where('com_code', $this->comCode())->where('is_active', true)->get();
        $items      = Item::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        $nextNumber = $this->nextNumber();
        return view('admin.purchasing.requests.create', compact('branches','units','items','nextNumber'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'        => 'required|date',
            'items'       => 'required|array|min:1',
            'items.*.qty' => 'required|numeric|min:0.001',
        ]);

        DB::transaction(function () use ($request) {
            $req = PurchaseRequest::create([
                'com_code'       => $this->comCode(),
                'request_number' => $this->nextNumber(),
                'date'           => $request->date,
                'needed_by_date' => $request->needed_by_date,
                'branch_id'      => $request->branch_id,
                'status'         => 'draft',
                'notes'          => $request->notes,
                'created_by'     => Auth::guard('admin')->id(),
            ]);

            foreach ($request->items as $row) {
                PurchaseRequestItem::create([
                    'request_id'  => $req->id,
                    'item_id'     => $row['item_id'] ?? null,
                    'description' => $row['description'] ?? null,
                    'unit_id'     => $row['unit_id'] ?? null,
                    'quantity'    => (float)($row['qty'] ?? 0),
                    'notes'       => $row['notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('purchase_requests.index')->with('success', 'تم إنشاء طلب الشراء بنجاح');
    }

    public function show($id)
    {
        $req = PurchaseRequest::with(['branch','items.item','items.unit','createdBy'])
            ->where('com_code', $this->comCode())->findOrFail($id);
        return view('admin.purchasing.requests.show', compact('req'));
    }

    public function edit($id)
    {
        $req      = PurchaseRequest::with('items')->where('com_code', $this->comCode())->findOrFail($id);
        $branches = Branche::where('com_code', $this->comCode())->get();
        $units    = ItemUnit::where('com_code', $this->comCode())->where('is_active', true)->get();
        $items    = Item::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        return view('admin.purchasing.requests.edit', compact('req','branches','units','items'));
    }

    public function update(Request $request, $id)
    {
        $request->validate(['date' => 'required|date']);
        $req = PurchaseRequest::where('com_code', $this->comCode())->findOrFail($id);

        DB::transaction(function () use ($request, $req) {
            $req->update([
                'date'           => $request->date,
                'needed_by_date' => $request->needed_by_date,
                'branch_id'      => $request->branch_id,
                'notes'          => $request->notes,
            ]);

            $req->items()->delete();
            foreach ($request->items ?? [] as $row) {
                PurchaseRequestItem::create([
                    'request_id'  => $req->id,
                    'item_id'     => $row['item_id'] ?? null,
                    'description' => $row['description'] ?? null,
                    'unit_id'     => $row['unit_id'] ?? null,
                    'quantity'    => (float)($row['qty'] ?? 0),
                    'notes'       => $row['notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('purchase_requests.show', $id)->with('success', 'تم تعديل طلب الشراء');
    }

    public function updateStatus(Request $request, $id)
    {
        $req = PurchaseRequest::where('com_code', $this->comCode())->findOrFail($id);
        $req->update(['status' => $request->status]);
        return back()->with('success', 'تم تغيير حالة الطلب');
    }

    public function convertToOrder($id)
    {
        $req = PurchaseRequest::with('items')->where('com_code', $this->comCode())->findOrFail($id);

        $lastOrder = PurchaseOrder::where('com_code', $this->comCode())->whereYear('created_at', now()->year)->max('order_number');
        $orderNum  = $lastOrder ? 'PO-'.now()->year.'-'.str_pad(((int) substr($lastOrder, -4)) + 1, 4, '0', STR_PAD_LEFT)
                                : 'PO-'.now()->year.'-0001';

        DB::transaction(function () use ($req, $orderNum) {
            $order = PurchaseOrder::create([
                'com_code'     => $this->comCode(),
                'order_number' => $orderNum,
                'date'         => today(),
                'branch_id'    => $req->branch_id,
                'request_id'   => $req->id,
                'status'       => 'draft',
                'created_by'   => Auth::guard('admin')->id(),
            ]);
            foreach ($req->items as $ri) {
                PurchaseOrderItem::create([
                    'order_id'    => $order->id,
                    'item_id'     => $ri->item_id,
                    'description' => $ri->description,
                    'unit_id'     => $ri->unit_id,
                    'quantity'    => $ri->quantity,
                ]);
            }
            $req->update(['status' => 'converted']);
        });

        return redirect()->route('purchase_orders.index')->with('success', 'تم تحويل طلب الشراء إلى أمر شراء');
    }

    public function delete($id)
    {
        PurchaseRequest::where('com_code', $this->comCode())->findOrFail($id)->delete();
        return redirect()->route('purchase_requests.index')->with('success', 'تم حذف طلب الشراء');
    }
}
