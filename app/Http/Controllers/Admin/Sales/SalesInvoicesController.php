<?php
namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\{SalesInvoice, SalesInvoiceItem, SalesPayment, Customer, Item, ItemUnit, Branche, Warehouse, StockMovement};
use App\Services\StockService;
use App\Services\Accounting\JournalPostingService;
use App\Services\Sales\InvoicePostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

class SalesInvoicesController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    private function nextInvoiceNumber(): string
    {
        $last = SalesInvoice::where('com_code', $this->comCode())->whereYear('created_at', now()->year)->max('invoice_number');
        $num  = $last ? ((int) substr($last, -4)) + 1 : 1;
        return 'INV-'.now()->year.'-'.str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    public function index(Request $request)
    {
        $query = SalesInvoice::with('customer')->where('com_code', $this->comCode());
        if ($request->filled('customer_id'))    $query->where('customer_id', $request->customer_id);
        if ($request->filled('status'))         $query->where('status', $request->status);
        if ($request->filled('payment_status')) $query->where('payment_status', $request->payment_status);
        if ($request->filled('from'))           $query->whereDate('date', '>=', $request->from);
        if ($request->filled('to'))             $query->whereDate('date', '<=', $request->to);
        $data      = $query->orderByDesc('date')->paginate(20);
        $customers = Customer::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        $totals    = [
            'count'     => $data->total(),
            'total'     => SalesInvoice::where('com_code', $this->comCode())->where('status', 'issued')->sum('total'),
            'collected' => SalesInvoice::where('com_code', $this->comCode())->where('status', 'issued')->sum('paid_amount'),
            'remaining' => SalesInvoice::where('com_code', $this->comCode())->whereIn('payment_status', ['unpaid','partial'])->sum('remaining_amount'),
        ];
        return view('admin.sales.invoices.index', compact('data','customers','totals'));
    }

    public function create()
    {
        $customers  = Customer::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        $branches   = Branche::where('com_code', $this->comCode())->get();
        $units      = ItemUnit::where('com_code', $this->comCode())->where('is_active', true)->get();
        $items      = Item::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        $nextNumber = $this->nextInvoiceNumber();
        return view('admin.sales.invoices.create', compact('customers','branches','units','items','warehouses','nextNumber'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'        => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'items'       => 'required|array|min:1',
        ]);

        DB::transaction(function () use ($request) {
            $subtotal = 0;
            foreach ($request->items as $row) {
                $qty      = (float)($row['qty'] ?? 0);
                $price    = (float)($row['price'] ?? 0);
                $disc     = (float)($row['discount_percent'] ?? 0);
                $taxRate  = (float)($row['tax_rate'] ?? 0);
                $lineNet  = round($qty * $price * (1 - $disc / 100), 2);
                $lineTax  = round($lineNet * $taxRate / 100, 2);
                $subtotal += $lineNet;
            }
            $discountAmount  = (float)($request->discount_amount ?? 0);
            $taxableAmount   = $subtotal - $discountAmount;
            $headerTaxRate   = (float)($request->tax_rate ?? 14);
            $taxAmount       = round($taxableAmount * $headerTaxRate / 100, 2);
            $total           = $taxableAmount + $taxAmount;

            $warehouseId = $request->warehouse_id ?: Warehouse::defaultId($this->comCode());

            $invoice = SalesInvoice::create([
                'com_code'        => $this->comCode(),
                'invoice_number'  => $this->nextInvoiceNumber(),
                'date'            => $request->date,
                'due_date'        => $request->due_date,
                'customer_id'     => $request->customer_id,
                'branch_id'       => $request->branch_id,
                'warehouse_id'    => $warehouseId,
                'invoice_type'    => $request->invoice_type ?? 'cash',
                'subtotal'        => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_rate'        => $headerTaxRate,
                'tax_amount'      => $taxAmount,
                'total'           => $total,
                'paid_amount'     => 0,
                'remaining_amount'=> $total,
                'payment_status'  => 'unpaid',
                'status'          => 'issued',
                'notes'           => $request->notes,
                'created_by'      => Auth::guard('admin')->id(),
            ]);

            $totalCogs = 0.0;
            foreach ($request->items as $row) {
                $qty      = (float)($row['qty'] ?? 0);
                $price    = (float)($row['price'] ?? 0);
                $disc     = (float)($row['discount_percent'] ?? 0);
                $discAmt  = round($qty * $price * $disc / 100, 2);
                $taxRate  = (float)($row['tax_rate'] ?? 0);
                $lineNet  = round($qty * $price - $discAmt, 2);
                $taxAmt   = round($lineNet * $taxRate / 100, 2);
                SalesInvoiceItem::create([
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
                    $movement = StockService::adjustStock(
                        $this->comCode(), (int) $row['item_id'], $warehouseId, -$qty,
                        'sales_out', 'sales_invoice', $invoice->id, $price, $request->date,
                        null, Auth::guard('admin')->id()
                    );
                    $totalCogs += (float) $movement->total_cost;
                }
            }

            $this->postInvoiceJournal($invoice, $taxableAmount, $taxAmount, $totalCogs);
        });

        return redirect()->route('sales_invoices.index')->with('success', 'تم إنشاء الفاتورة بنجاح');
    }

    /** ترحيل قيد الإيراد/الضريبة + قيد تكلفة البضاعة المباعة لفاتورة بيع (Phase 3) */
    private function postInvoiceJournal(SalesInvoice $invoice, float $taxableAmount, float $taxAmount, float $totalCogs): void
    {
        InvoicePostingService::postInvoiceJournal($invoice, $taxableAmount, $taxAmount, $totalCogs, Auth::guard('admin')->id());
    }

    public function show($id)
    {
        $invoice = SalesInvoice::with(['customer','branch','order','items.item','items.unit','payments','createdBy'])
            ->where('com_code', $this->comCode())->findOrFail($id);
        return view('admin.sales.invoices.show', compact('invoice'));
    }

    public function edit($id)
    {
        $invoice   = SalesInvoice::with('items')->where('com_code', $this->comCode())->findOrFail($id);
        $customers = Customer::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        $branches  = Branche::where('com_code', $this->comCode())->get();
        $units     = ItemUnit::where('com_code', $this->comCode())->where('is_active', true)->get();
        $items     = Item::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        return view('admin.sales.invoices.edit', compact('invoice','customers','branches','units','items'));
    }

    public function update(Request $request, $id)
    {
        $request->validate(['date' => 'required|date', 'customer_id' => 'required']);
        $invoice = SalesInvoice::where('com_code', $this->comCode())->findOrFail($id);
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
                'date'            => $request->date,
                'due_date'        => $request->due_date,
                'customer_id'     => $request->customer_id,
                'branch_id'       => $request->branch_id,
                'invoice_type'    => $request->invoice_type ?? 'cash',
                'subtotal'        => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_rate'        => $headerTaxRate,
                'tax_amount'      => $taxAmount,
                'total'           => $total,
                'remaining_amount'=> max(0, $total - $paidAmount),
                'payment_status'  => $paidAmount >= $total ? 'paid' : ($paidAmount > 0 ? 'partial' : 'unpaid'),
                'notes'           => $request->notes,
            ]);

            // عكس حركات المخزون القديمة المرتبطة بهذه الفاتورة قبل إعادة بناء البنود
            if ($invoice->warehouse_id) {
                $oldMovements = StockMovement::where('reference_type', 'sales_invoice')->where('reference_id', $invoice->id)->get();
                foreach ($oldMovements as $mv) {
                    StockService::adjustStock(
                        $invoice->com_code, $mv->item_id, $mv->warehouse_id, $mv->quantity,
                        'sales_out_reversal', 'sales_invoice', $invoice->id, null, $request->date,
                        'عكس عند تعديل الفاتورة', Auth::guard('admin')->id()
                    );
                }
            }

            $invoice->items()->delete();
            $totalCogs = 0.0;
            foreach ($request->items ?? [] as $row) {
                $qty     = (float)($row['qty'] ?? 0);
                $price   = (float)($row['price'] ?? 0);
                $disc    = (float)($row['discount_percent'] ?? 0);
                $discAmt = round($qty * $price * $disc / 100, 2);
                $taxRate = (float)($row['tax_rate'] ?? 0);
                $lineNet = round($qty * $price - $discAmt, 2);
                $taxAmt  = round($lineNet * $taxRate / 100, 2);
                SalesInvoiceItem::create([
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

                if (!empty($row['item_id']) && $invoice->warehouse_id) {
                    $movement = StockService::adjustStock(
                        $invoice->com_code, (int) $row['item_id'], $invoice->warehouse_id, -$qty,
                        'sales_out', 'sales_invoice', $invoice->id, $price, $request->date,
                        null, Auth::guard('admin')->id()
                    );
                    $totalCogs += (float) $movement->total_cost;
                }
            }

            // عكس القيد المحاسبي القديم وترحيل قيد جديد بالأرقام المحدّثة
            JournalPostingService::reverseBySource($invoice->com_code, 'sales_invoice', $invoice->id, Auth::guard('admin')->id(), 'تعديل الفاتورة');
            $this->postInvoiceJournal($invoice, $taxableAmount, $taxAmount, $totalCogs);
        });
        return redirect()->route('sales_invoices.show', $id)->with('success', 'تم تعديل الفاتورة');
    }

    public function cancel($id)
    {
        $invoice = SalesInvoice::where('com_code', $this->comCode())->findOrFail($id);
        DB::transaction(function () use ($invoice) {
            $invoice->update(['status' => 'cancelled']);
            JournalPostingService::reverseBySource($invoice->com_code, 'sales_invoice', $invoice->id, Auth::guard('admin')->id(), 'إلغاء الفاتورة');
        });
        return back()->with('success', 'تم إلغاء الفاتورة');
    }

    public function print($id)
    {
        $invoice = SalesInvoice::with(['customer','branch','items.item','items.unit'])
            ->where('com_code', $this->comCode())->findOrFail($id);
        return view('admin.sales.invoices.print', compact('invoice'));
    }

    public function delete($id)
    {
        SalesInvoice::where('com_code', $this->comCode())->findOrFail($id)->delete();
        return redirect()->route('sales_invoices.index')->with('success', 'تم حذف الفاتورة');
    }
}
