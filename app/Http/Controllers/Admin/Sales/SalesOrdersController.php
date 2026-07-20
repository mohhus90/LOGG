<?php
namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\{SalesOrder, SalesOrderItem, SalesInvoice, SalesInvoiceItem, Customer, Item, ItemUnit, Branche};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

class SalesOrdersController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    private function nextOrderNumber(): string
    {
        $last = SalesOrder::where('com_code', $this->comCode())->whereYear('created_at', now()->year)->max('order_number');
        $num  = $last ? ((int) substr($last, -4)) + 1 : 1;
        return 'SO-'.now()->year.'-'.str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    public function index(Request $request)
    {
        $query = SalesOrder::with('customer')->where('com_code', $this->comCode());
        if ($request->filled('customer_id')) $query->where('customer_id', $request->customer_id);
        if ($request->filled('status'))      $query->where('status', $request->status);
        if ($request->filled('from'))        $query->whereDate('date', '>=', $request->from);
        if ($request->filled('to'))          $query->whereDate('date', '<=', $request->to);
        $data      = $query->orderByDesc('date')->paginate(20);
        $customers = Customer::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        return view('admin.sales.orders.index', compact('data','customers'));
    }

    public function create()
    {
        $customers  = Customer::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        $branches   = Branche::where('com_code', $this->comCode())->get();
        $units      = ItemUnit::where('com_code', $this->comCode())->where('is_active', true)->get();
        $items      = Item::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        $nextNumber = $this->nextOrderNumber();
        return view('admin.sales.orders.create', compact('customers','branches','units','items','nextNumber'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'        => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'items'       => 'required|array|min:1',
        ]);

        DB::transaction(function () use ($request) {
            [$subtotal, $discountAmount, $taxAmount, $total] = $this->calcTotals($request);

            $order = SalesOrder::create([
                'com_code'        => $this->comCode(),
                'order_number'    => $this->nextOrderNumber(),
                'date'            => $request->date,
                'delivery_date'   => $request->delivery_date,
                'customer_id'     => $request->customer_id,
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

        return redirect()->route('sales_orders.index')->with('success', 'تم إنشاء أمر البيع بنجاح');
    }

    public function show($id)
    {
        $order = SalesOrder::with(['customer','branch','items.item','items.unit','invoices','createdBy'])
            ->where('com_code', $this->comCode())->findOrFail($id);
        return view('admin.sales.orders.show', compact('order'));
    }

    public function edit($id)
    {
        $order     = SalesOrder::with('items')->where('com_code', $this->comCode())->findOrFail($id);
        $customers = Customer::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        $branches  = Branche::where('com_code', $this->comCode())->get();
        $units     = ItemUnit::where('com_code', $this->comCode())->where('is_active', true)->get();
        $items     = Item::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        return view('admin.sales.orders.edit', compact('order','customers','branches','units','items'));
    }

    public function update(Request $request, $id)
    {
        $request->validate(['date' => 'required|date', 'customer_id' => 'required']);
        $order = SalesOrder::where('com_code', $this->comCode())->findOrFail($id);
        DB::transaction(function () use ($request, $order) {
            [$subtotal, $discountAmount, $taxAmount, $total] = $this->calcTotals($request);
            $order->update([
                'date'            => $request->date,
                'delivery_date'   => $request->delivery_date,
                'customer_id'     => $request->customer_id,
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
        return redirect()->route('sales_orders.show', $id)->with('success', 'تم تعديل أمر البيع');
    }

    public function updateStatus(Request $request, $id)
    {
        $order = SalesOrder::where('com_code', $this->comCode())->findOrFail($id);
        $order->update(['status' => $request->status]);
        return back()->with('success', 'تم تغيير حالة الأمر');
    }

    public function updateShipping(Request $request, $id)
    {
        $request->validate(['cod_status' => 'nullable|in:none,pending,collected,returned']);
        $order = SalesOrder::where('com_code', $this->comCode())->findOrFail($id);
        $order->update([
            'shipping_company' => $request->shipping_company,
            'waybill_number'   => $request->waybill_number,
            'cod_status'       => $request->cod_status ?? 'none',
            'cod_amount'       => $request->cod_amount,
            'cod_collected_at' => $request->cod_status === 'collected' && $order->cod_status !== 'collected'
                ? now() : $order->cod_collected_at,
        ]);
        return back()->with('success', 'تم حفظ بيانات الشحن والتحصيل');
    }

    public function createInvoice($id)
    {
        $order = SalesOrder::with('items')->where('com_code', $this->comCode())->findOrFail($id);
        $last  = SalesInvoice::where('com_code', $this->comCode())->whereYear('created_at', now()->year)->max('invoice_number');
        $num   = $last ? ((int) substr($last, -4)) + 1 : 1;
        $invNo = 'INV-'.now()->year.'-'.str_pad($num, 4, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($order, $invNo) {
            $invoice = SalesInvoice::create([
                'com_code'        => $this->comCode(),
                'invoice_number'  => $invNo,
                'date'            => today(),
                'customer_id'     => $order->customer_id,
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
                'status'          => 'issued',
                'created_by'      => Auth::guard('admin')->id(),
            ]);
            foreach ($order->items as $oi) {
                SalesInvoiceItem::create([
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
            $order->update(['status' => 'delivered']);
        });

        return redirect()->route('sales_invoices.index')->with('success', 'تم إنشاء الفاتورة من أمر البيع');
    }

    public function print($id)
    {
        $order = SalesOrder::with(['customer','branch','items.item','items.unit'])
            ->where('com_code', $this->comCode())->findOrFail($id);
        return view('admin.sales.orders.print', compact('order'));
    }

    public function delete($id)
    {
        SalesOrder::where('com_code', $this->comCode())->findOrFail($id)->delete();
        return redirect()->route('sales_orders.index')->with('success', 'تم حذف أمر البيع');
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
            SalesOrderItem::create([
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
