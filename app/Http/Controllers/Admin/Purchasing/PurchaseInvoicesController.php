<?php
namespace App\Http\Controllers\Admin\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\{PurchaseInvoice, PurchaseInvoiceItem, PurchasePayment, Supplier, Item, ItemUnit, Branche, Warehouse};
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

class PurchaseInvoicesController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    private function nextInvoiceNumber(): string
    {
        $last = PurchaseInvoice::where('com_code', $this->comCode())->whereYear('created_at', now()->year)->max('invoice_number');
        $num  = $last ? ((int) substr($last, -4)) + 1 : 1;
        return 'PINV-'.now()->year.'-'.str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    public function index(Request $request)
    {
        $query = PurchaseInvoice::with('supplier')->where('com_code', $this->comCode());
        if ($request->filled('supplier_id'))    $query->where('supplier_id', $request->supplier_id);
        if ($request->filled('status'))         $query->where('status', $request->status);
        if ($request->filled('payment_status')) $query->where('payment_status', $request->payment_status);
        if ($request->filled('from'))           $query->whereDate('date', '>=', $request->from);
        if ($request->filled('to'))             $query->whereDate('date', '<=', $request->to);
        $data      = $query->orderByDesc('date')->paginate(20);
        $suppliers = Supplier::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        $totals    = [
            'count'     => $data->total(),
            'total'     => PurchaseInvoice::where('com_code', $this->comCode())->where('status', 'received')->sum('total'),
            'paid'      => PurchaseInvoice::where('com_code', $this->comCode())->where('status', 'received')->sum('paid_amount'),
            'remaining' => PurchaseInvoice::where('com_code', $this->comCode())->whereIn('payment_status', ['unpaid','partial'])->sum('remaining_amount'),
        ];
        return view('admin.purchasing.invoices.index', compact('data','suppliers','totals'));
    }

    public function create()
    {
        $suppliers  = Supplier::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        $branches   = Branche::where('com_code', $this->comCode())->get();
        $units      = ItemUnit::where('com_code', $this->comCode())->where('is_active', true)->get();
        $items      = Item::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        $nextNumber = $this->nextInvoiceNumber();
        return view('admin.purchasing.invoices.create', compact('suppliers','branches','units','items','warehouses','nextNumber'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'        => 'required|date',
            'supplier_id' => 'required|exists:suppliers,id',
            'items'       => 'required|array|min:1',
        ]);

        DB::transaction(function () use ($request) {
            $subtotal = 0;
            foreach ($request->items as $row) {
                $qty     = (float)($row['qty'] ?? 0);
                $price   = (float)($row['price'] ?? 0);
                $disc    = (float)($row['discount_percent'] ?? 0);
                $subtotal += round($qty * $price * (1 - $disc / 100), 2);
            }
            $discountAmount  = (float)($request->discount_amount ?? 0);
            $taxableAmount   = $subtotal - $discountAmount;
            $headerTaxRate   = (float)($request->tax_rate ?? 14);
            $taxAmount       = round($taxableAmount * $headerTaxRate / 100, 2);
            $total           = $taxableAmount + $taxAmount;

            $warehouseId = $request->warehouse_id ?: Warehouse::defaultId($this->comCode());

            $invoice = PurchaseInvoice::create([
                'com_code'            => $this->comCode(),
                'invoice_number'      => $this->nextInvoiceNumber(),
                'date'                => $request->date,
                'due_date'            => $request->due_date,
                'supplier_id'         => $request->supplier_id,
                'branch_id'           => $request->branch_id,
                'warehouse_id'        => $warehouseId,
                'supplier_invoice_no' => $request->supplier_invoice_no,
                'invoice_type'        => $request->invoice_type ?? 'credit',
                'subtotal'            => $subtotal,
                'discount_amount'     => $discountAmount,
                'tax_rate'            => $headerTaxRate,
                'tax_amount'          => $taxAmount,
                'total'               => $total,
                'paid_amount'         => 0,
                'remaining_amount'    => $total,
                'payment_status'      => 'unpaid',
                'status'              => 'received',
                'notes'               => $request->notes,
                'created_by'          => Auth::guard('admin')->id(),
            ]);

            foreach ($request->items as $row) {
                $qty     = (float)($row['qty'] ?? 0);
                $price   = (float)($row['price'] ?? 0);
                $disc    = (float)($row['discount_percent'] ?? 0);
                $discAmt = round($qty * $price * $disc / 100, 2);
                $taxRate = (float)($row['tax_rate'] ?? 0);
                $lineNet = round($qty * $price - $discAmt, 2);
                $taxAmt  = round($lineNet * $taxRate / 100, 2);
                PurchaseInvoiceItem::create([
                    'invoice_id'       => $invoice->id,
                    'item_id'          => $row['item_id'] ?? null,
                    'description'      => $row['description'] ?? null,
                    'unit_id'          => $row['unit_id'] ?? null,
                    'quantity'         => $qty,
                    'unit_price'       => $price,
                    'discount_percent' => $disc,
                    'discount_amount'  => $discAmt,
                    'tax_rate'         => $taxRate,
                    'tax_amount'       => $taxAmt,
                    'total'            => $lineNet + $taxAmt,
                ]);

                if (!empty($row['item_id']) && $warehouseId) {
                    StockService::adjustStock(
                        $this->comCode(), (int) $row['item_id'], $warehouseId, $qty,
                        'purchase_in', 'purchase_invoice', $invoice->id, $price, $request->date,
                        null, Auth::guard('admin')->id()
                    );
                }
            }
        });

        return redirect()->route('purchase_invoices.index')->with('success', 'تم إنشاء فاتورة الشراء بنجاح');
    }

    public function show($id)
    {
        $invoice = PurchaseInvoice::with(['supplier','branch','order','items.item','items.unit','payments','createdBy'])
            ->where('com_code', $this->comCode())->findOrFail($id);
        return view('admin.purchasing.invoices.show', compact('invoice'));
    }

    public function edit($id)
    {
        $invoice   = PurchaseInvoice::with('items')->where('com_code', $this->comCode())->findOrFail($id);
        $suppliers = Supplier::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        $branches  = Branche::where('com_code', $this->comCode())->get();
        $units     = ItemUnit::where('com_code', $this->comCode())->where('is_active', true)->get();
        $items     = Item::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        return view('admin.purchasing.invoices.edit', compact('invoice','suppliers','branches','units','items'));
    }

    public function update(Request $request, $id)
    {
        $request->validate(['date' => 'required|date', 'supplier_id' => 'required']);
        $invoice = PurchaseInvoice::where('com_code', $this->comCode())->findOrFail($id);
        DB::transaction(function () use ($request, $invoice) {
            $subtotal = 0;
            foreach ($request->items ?? [] as $row) {
                $qty     = (float)($row['qty'] ?? 0);
                $price   = (float)($row['price'] ?? 0);
                $disc    = (float)($row['discount_percent'] ?? 0);
                $subtotal += round($qty * $price * (1 - $disc / 100), 2);
            }
            $discountAmount  = (float)($request->discount_amount ?? 0);
            $taxableAmount   = $subtotal - $discountAmount;
            $headerTaxRate   = (float)($request->tax_rate ?? 14);
            $taxAmount       = round($taxableAmount * $headerTaxRate / 100, 2);
            $total           = $taxableAmount + $taxAmount;
            $paidAmount      = $invoice->paid_amount;

            $invoice->update([
                'date'                => $request->date,
                'due_date'            => $request->due_date,
                'supplier_id'         => $request->supplier_id,
                'branch_id'           => $request->branch_id,
                'supplier_invoice_no' => $request->supplier_invoice_no,
                'invoice_type'        => $request->invoice_type ?? 'credit',
                'subtotal'            => $subtotal,
                'discount_amount'     => $discountAmount,
                'tax_rate'            => $headerTaxRate,
                'tax_amount'          => $taxAmount,
                'total'               => $total,
                'remaining_amount'    => max(0, $total - $paidAmount),
                'payment_status'      => $paidAmount >= $total ? 'paid' : ($paidAmount > 0 ? 'partial' : 'unpaid'),
                'notes'               => $request->notes,
            ]);

            $invoice->items()->delete();
            foreach ($request->items ?? [] as $row) {
                $qty     = (float)($row['qty'] ?? 0);
                $price   = (float)($row['price'] ?? 0);
                $disc    = (float)($row['discount_percent'] ?? 0);
                $discAmt = round($qty * $price * $disc / 100, 2);
                $taxRate = (float)($row['tax_rate'] ?? 0);
                $lineNet = round($qty * $price - $discAmt, 2);
                $taxAmt  = round($lineNet * $taxRate / 100, 2);
                PurchaseInvoiceItem::create([
                    'invoice_id'       => $invoice->id,
                    'item_id'          => $row['item_id'] ?? null,
                    'description'      => $row['description'] ?? null,
                    'unit_id'          => $row['unit_id'] ?? null,
                    'quantity'         => $qty,
                    'unit_price'       => $price,
                    'discount_percent' => $disc,
                    'discount_amount'  => $discAmt,
                    'tax_rate'         => $taxRate,
                    'tax_amount'       => $taxAmt,
                    'total'            => $lineNet + $taxAmt,
                ]);
            }
        });
        return redirect()->route('purchase_invoices.show', $id)->with('success', 'تم تعديل فاتورة الشراء');
    }

    public function cancel($id)
    {
        $invoice = PurchaseInvoice::where('com_code', $this->comCode())->findOrFail($id);
        $invoice->update(['status' => 'cancelled']);
        return back()->with('success', 'تم إلغاء الفاتورة');
    }

    public function print($id)
    {
        $invoice = PurchaseInvoice::with(['supplier','branch','items.item','items.unit'])
            ->where('com_code', $this->comCode())->findOrFail($id);
        return view('admin.purchasing.invoices.print', compact('invoice'));
    }

    public function delete($id)
    {
        PurchaseInvoice::where('com_code', $this->comCode())->findOrFail($id)->delete();
        return redirect()->route('purchase_invoices.index')->with('success', 'تم حذف فاتورة الشراء');
    }
}
