<?php
namespace App\Http\Controllers\Admin\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\{PurchaseInvoice, PurchaseInvoiceItem, PurchasePayment, Supplier, Item, ItemUnit, Branche, Warehouse, StockMovement};
use App\Services\StockService;
use App\Services\Accounting\JournalPostingService;
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

            $inventoryAmount = 0.0;
            $expenseAmount   = 0.0;
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

                if (!empty($row['item_id'])) {
                    $itemType = Item::where('id', $row['item_id'])->value('type');
                    if ($itemType === 'service') {
                        $expenseAmount += $lineNet;
                    } else {
                        $inventoryAmount += $lineNet;
                    }
                }

                if (!empty($row['item_id']) && $warehouseId) {
                    StockService::adjustStock(
                        $this->comCode(), (int) $row['item_id'], $warehouseId, $qty,
                        'purchase_in', 'purchase_invoice', $invoice->id, $price, $request->date,
                        null, Auth::guard('admin')->id()
                    );
                }
            }

            $this->postInvoiceJournal($invoice, $inventoryAmount, $expenseAmount, $taxAmount);
        });

        return redirect()->route('purchase_invoices.index')->with('success', 'تم إنشاء فاتورة الشراء بنجاح');
    }

    /** ترحيل قيد استلام فاتورة الشراء (Phase 3): مخزون/مصروف + ضريبة مشتريات مقابل دائن المورد */
    private function postInvoiceJournal(PurchaseInvoice $invoice, float $inventoryAmount, float $expenseAmount, float $taxAmount): void
    {
        $comCode = $invoice->com_code;
        if (JournalPostingService::alreadyPosted($comCode, 'purchase_invoice', $invoice->id)) {
            return;
        }
        if ($inventoryAmount <= 0 && $expenseAmount <= 0 && $taxAmount <= 0) {
            return;
        }

        // خصم إجمالي على مستوى رأس الفاتورة (إن وُجد) يُخصم من المخزون أولاً ثم من المصروف
        $headerDiscount = (float) $invoice->discount_amount;
        if ($headerDiscount > 0) {
            $fromInventory   = min($inventoryAmount, $headerDiscount);
            $inventoryAmount -= $fromInventory;
            $expenseAmount    = max(0, $expenseAmount - ($headerDiscount - $fromInventory));
        }

        $lines = [];
        if ($inventoryAmount > 0) $lines[] = ['role' => 'INVENTORY', 'debit' => $inventoryAmount, 'credit' => 0];
        if ($expenseAmount > 0)   $lines[] = ['role' => 'EXPENSE',   'debit' => $expenseAmount, 'credit' => 0];
        if ($taxAmount > 0)       $lines[] = ['role' => 'VAT_INPUT', 'debit' => $taxAmount, 'credit' => 0];
        $lines[] = ['role' => 'AP_CONTROL', 'debit' => 0, 'credit' => $inventoryAmount + $expenseAmount + $taxAmount, 'party_type' => 'supplier', 'party_id' => $invoice->supplier_id];

        JournalPostingService::post('purchase_invoice_received', $comCode, $lines, [
            'source_module' => 'purchase_invoice',
            'source_id'     => $invoice->id,
            'entry_date'    => $invoice->date,
            'reference'     => $invoice->invoice_number,
            'description'   => 'فاتورة شراء '.$invoice->invoice_number,
            'created_by'    => Auth::guard('admin')->id(),
        ]);
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

            // عكس حركات المخزون القديمة المرتبطة بهذه الفاتورة قبل إعادة بناء البنود
            if ($invoice->warehouse_id) {
                $oldMovements = StockMovement::where('reference_type', 'purchase_invoice')->where('reference_id', $invoice->id)->get();
                foreach ($oldMovements as $mv) {
                    StockService::adjustStock(
                        $invoice->com_code, $mv->item_id, $mv->warehouse_id, -$mv->quantity,
                        'purchase_in_reversal', 'purchase_invoice', $invoice->id, null, $request->date,
                        'عكس عند تعديل فاتورة الشراء', Auth::guard('admin')->id()
                    );
                }
            }

            $invoice->items()->delete();
            $inventoryAmount = 0.0;
            $expenseAmount   = 0.0;
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

                if (!empty($row['item_id'])) {
                    $itemType = Item::where('id', $row['item_id'])->value('type');
                    if ($itemType === 'service') {
                        $expenseAmount += $lineNet;
                    } else {
                        $inventoryAmount += $lineNet;
                    }
                }

                if (!empty($row['item_id']) && $invoice->warehouse_id) {
                    StockService::adjustStock(
                        $invoice->com_code, (int) $row['item_id'], $invoice->warehouse_id, $qty,
                        'purchase_in', 'purchase_invoice', $invoice->id, $price, $request->date,
                        null, Auth::guard('admin')->id()
                    );
                }
            }

            JournalPostingService::reverseBySource($invoice->com_code, 'purchase_invoice', $invoice->id, Auth::guard('admin')->id(), 'تعديل الفاتورة');
            $this->postInvoiceJournal($invoice, $inventoryAmount, $expenseAmount, $invoice->tax_amount);
        });
        return redirect()->route('purchase_invoices.show', $id)->with('success', 'تم تعديل فاتورة الشراء');
    }

    public function cancel($id)
    {
        $invoice = PurchaseInvoice::where('com_code', $this->comCode())->findOrFail($id);
        DB::transaction(function () use ($invoice) {
            $invoice->update(['status' => 'cancelled']);
            JournalPostingService::reverseBySource($invoice->com_code, 'purchase_invoice', $invoice->id, Auth::guard('admin')->id(), 'إلغاء الفاتورة');
        });
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
