<?php
namespace App\Http\Controllers\Admin\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\{PurchaseOrder, PurchaseOrderItem, PurchaseInvoice, PurchaseInvoiceItem, Supplier, Item, ItemUnit, Branche};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

class PurchaseOrdersController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    private function nextOrderNumber(): string
    {
        $last = PurchaseOrder::where('com_code', $this->comCode())->whereYear('created_at', now()->year)->max('order_number');
        $num  = $last ? ((int) substr($last, -4)) + 1 : 1;
        return 'PO-'.now()->year.'-'.str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    public function index(Request $request)
    {
        $query = PurchaseOrder::with('supplier')->where('com_code', $this->comCode());
        if ($request->filled('supplier_id')) $query->where('supplier_id', $request->supplier_id);
        if ($request->filled('status'))      $query->where('status', $request->status);
        if ($request->filled('from'))        $query->whereDate('date', '>=', $request->from);
        if ($request->filled('to'))          $query->whereDate('date', '<=', $request->to);
        $data      = $query->orderByDesc('date')->paginate(20);
        $suppliers = Supplier::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        return view('admin.purchasing.orders.index', compact('data','suppliers'));
    }

    public function create()
    {
        $suppliers  = Supplier::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        $branches   = Branche::where('com_code', $this->comCode())->get();
        $units      = ItemUnit::where('com_code', $this->comCode())->where('is_active', true)->get();
        $items      = Item::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        $nextNumber = $this->nextOrderNumber();
        return view('admin.purchasing.orders.create', compact('suppliers','branches','units','items','nextNumber'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'        => 'required|date',
            'supplier_id' => 'required|exists:suppliers,id',
            'items'       => 'required|array|min:1',
        ]);

        DB::transaction(function () use ($request) {
            [$subtotal, $discountAmount, $taxAmount, $total] = $this->calcTotals($request);

            $order = PurchaseOrder::create([
                'com_code'        => $this->comCode(),
                'order_number'    => $this->nextOrderNumber(),
                'date'            => $request->date,
                'expected_date'   => $request->expected_date,
                'supplier_id'     => $request->supplier_id,
                'branch_id'       => $request->branch_id,
                'subtotal'        => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_rate'        => (float)($request->tax_rate ?? 14),
                'tax_amount'      => $taxAmount,
                'total'           => $total,
                'status'          => $request->status ?? 'confirmed',
                'delivery_address'=> $request->delivery_address,
                'notes'           => $request->notes,
                'created_by'      => Auth::guard('admin')->id(),
            ]);

            $this->saveItems($order->id, $request->items);
        });

        return redirect()->route('purchase_orders.index')->with('success', 'تم إنشاء أمر الشراء بنجاح');
    }

    public function show($id)
    {
        $order = PurchaseOrder::with(['supplier','branch','items.item','items.unit','invoices','createdBy'])
            ->where('com_code', $this->comCode())->findOrFail($id);
        return view('admin.purchasing.orders.show', compact('order'));
    }

    public function edit($id)
    {
        $order     = PurchaseOrder::with('items')->where('com_code', $this->comCode())->findOrFail($id);
        $suppliers = Supplier::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        $branches  = Branche::where('com_code', $this->comCode())->get();
        $units     = ItemUnit::where('com_code', $this->comCode())->where('is_active', true)->get();
        $items     = Item::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        return view('admin.purchasing.orders.edit', compact('order','suppliers','branches','units','items'));
    }

    public function update(Request $request, $id)
    {
        $request->validate(['date' => 'required|date', 'supplier_id' => 'required']);
        $order = PurchaseOrder::where('com_code', $this->comCode())->findOrFail($id);
        DB::transaction(function () use ($request, $order) {
            [$subtotal, $discountAmount, $taxAmount, $total] = $this->calcTotals($request);
            $order->update([
                'date'            => $request->date,
                'expected_date'   => $request->expected_date,
                'supplier_id'     => $request->supplier_id,
                'branch_id'       => $request->branch_id,
                'subtotal'        => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_rate'        => (float)($request->tax_rate ?? 14),
                'tax_amount'      => $taxAmount,
                'total'           => $total,
                'delivery_address'=> $request->delivery_address,
                'notes'           => $request->notes,
            ]);
            $order->items()->delete();
            $this->saveItems($order->id, $request->items ?? []);
        });
        return redirect()->route('purchase_orders.show', $id)->with('success', 'تم تعديل أمر الشراء');
    }

    public function updateStatus(Request $request, $id)
    {
        $order = PurchaseOrder::where('com_code', $this->comCode())->findOrFail($id);
        $order->update(['status' => $request->status]);
        return back()->with('success', 'تم تغيير حالة الأمر');
    }

    public function createInvoice($id)
    {
        $order = PurchaseOrder::with('items')->where('com_code', $this->comCode())->findOrFail($id);
        $last  = PurchaseInvoice::where('com_code', $this->comCode())->whereYear('created_at', now()->year)->max('invoice_number');
        $num   = $last ? ((int) substr($last, -4)) + 1 : 1;
        $invNo = 'PINV-'.now()->year.'-'.str_pad($num, 4, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($order, $invNo) {
            $invoice = PurchaseInvoice::create([
                'com_code'        => $this->comCode(),
                'invoice_number'  => $invNo,
                'date'            => today(),
                'supplier_id'     => $order->supplier_id,
                'branch_id'       => $order->branch_id,
                'order_id'        => $order->id,
                'invoice_type'    => 'credit',
                'subtotal'        => $order->subtotal,
                'discount_amount' => $order->discount_amount,
                'tax_rate'        => $order->tax_rate,
                'tax_amount'      => $order->tax_amount,
                'total'           => $order->total,
                'remaining_amount'=> $order->total,
                'payment_status'  => 'unpaid',
                'status'          => 'received',
                'created_by'      => Auth::guard('admin')->id(),
            ]);
            foreach ($order->items as $oi) {
                PurchaseInvoiceItem::create([
                    'invoice_id'       => $invoice->id,
                    'item_id'          => $oi->item_id,
                    'description'      => $oi->description,
                    'unit_id'          => $oi->unit_id,
                    'quantity'         => $oi->quantity,
                    'unit_price'       => $oi->unit_price,
                    'discount_percent' => $oi->discount_percent,
                    'discount_amount'  => $oi->discount_amount,
                    'total'            => $oi->total,
                ]);
            }
            $order->update(['status' => 'received']);
        });

        return redirect()->route('purchase_invoices.index')->with('success', 'تم إنشاء فاتورة الشراء من أمر الشراء');
    }

    public function print($id)
    {
        $order = PurchaseOrder::with(['supplier','branch','items.item','items.unit'])
            ->where('com_code', $this->comCode())->findOrFail($id);
        return view('admin.purchasing.orders.print', compact('order'));
    }

    public function delete($id)
    {
        PurchaseOrder::where('com_code', $this->comCode())->findOrFail($id)->delete();
        return redirect()->route('purchase_orders.index')->with('success', 'تم حذف أمر الشراء');
    }

    private function calcTotals(Request $request): array
    {
        $subtotal = 0;
        foreach ($request->items ?? [] as $row) {
            $subtotal += round((float)($row['qty'] ?? 0) * (float)($row['price'] ?? 0) * (1 - (float)($row['discount_percent'] ?? 0) / 100), 2);
        }
        $discountAmount = (float)($request->discount_amount ?? 0);
        $taxableAmount  = $subtotal - $discountAmount;
        $taxRate        = (float)($request->tax_rate ?? 14);
        $taxAmount      = round($taxableAmount * $taxRate / 100, 2);
        return [$subtotal, $discountAmount, $taxAmount, $taxableAmount + $taxAmount];
    }

    private function saveItems(int $orderId, array $items): void
    {
        foreach ($items as $row) {
            $qty      = (float)($row['qty'] ?? 0);
            $price    = (float)($row['price'] ?? 0);
            $disc     = (float)($row['discount_percent'] ?? 0);
            $discAmt  = round($qty * $price * $disc / 100, 2);
            PurchaseOrderItem::create([
                'order_id'         => $orderId,
                'item_id'          => $row['item_id'] ?? null,
                'description'      => $row['description'] ?? null,
                'unit_id'          => $row['unit_id'] ?? null,
                'quantity'         => $qty,
                'unit_price'       => $price,
                'discount_percent' => $disc,
                'discount_amount'  => $discAmt,
                'total'            => round($qty * $price - $discAmt, 2),
            ]);
        }
    }
}
