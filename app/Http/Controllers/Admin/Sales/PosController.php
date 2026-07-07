<?php
namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\{Customer, PosRegister, PosSession, SalesInvoice, SalesInvoiceItem, SalesPayment};
use App\Services\StockService;
use App\Services\Accounting\JournalPostingService;
use App\Services\Treasury\TreasuryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

/**
 * نقطة البيع (POS) بجلسات كاشير. يُعيد استخدام نفس منطق حساب السطور/الترحيل
 * المحاسبي المستخدم فى SalesInvoicesController (نفس event types، بدون قواعد
 * ترحيل جديدة)، ونفس TreasuryService المستخدم فى سندات القبض العادية — البيع
 * هنا دائمًا نقدي وبالكامل فورًا (لا آجل، لا سداد جزئي).
 */
class PosController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    private function nextInvoiceNumber(): string
    {
        $last = SalesInvoice::where('com_code', $this->comCode())->whereYear('created_at', now()->year)->max('invoice_number');
        $num  = $last ? ((int) substr($last, -4)) + 1 : 1;
        return 'INV-'.now()->year.'-'.str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    private function nextPaymentNumber(): string
    {
        $last = SalesPayment::where('com_code', $this->comCode())->whereYear('created_at', now()->year)->max('payment_number');
        $num  = $last ? ((int) substr($last, -4)) + 1 : 1;
        return 'PAY-'.now()->year.'-'.str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    private function walkInCustomer(): Customer
    {
        return Customer::firstOrCreate(
            ['com_code' => $this->comCode(), 'name' => 'عميل نقدي (POS)'],
            ['type' => 'individual', 'is_active' => true, 'created_by' => Auth::guard('admin')->id()]
        );
    }

    // ─────────────────────────────────────────────
    // اختيار الكاشير
    // ─────────────────────────────────────────────
    public function selectRegister()
    {
        $registers = PosRegister::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();

        if ($registers->count() === 1) {
            return redirect()->route('pos.terminal', $registers->first()->id);
        }

        return view('admin.sales.pos.select_register', compact('registers'));
    }

    // ─────────────────────────────────────────────
    // شاشة الكاشير الرئيسية
    // ─────────────────────────────────────────────
    public function terminal($registerId)
    {
        $register = PosRegister::where('com_code', $this->comCode())->findOrFail($registerId);
        $session  = PosSession::where('register_id', $register->id)->where('status', 'open')->first();
        $customers = Customer::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        $walkIn    = $this->walkInCustomer();

        return view('admin.sales.pos.terminal', compact('register', 'session', 'customers', 'walkIn'));
    }

    public function openSession(Request $request, $registerId)
    {
        $request->validate(['opening_amount' => 'required|numeric|min:0']);

        $register = PosRegister::where('com_code', $this->comCode())->findOrFail($registerId);

        if (PosSession::where('register_id', $register->id)->where('status', 'open')->exists()) {
            return back()->with('error', 'يوجد جلسة مفتوحة بالفعل على هذا الكاشير');
        }

        PosSession::create([
            'com_code'       => $this->comCode(),
            'register_id'    => $register->id,
            'opened_by'      => Auth::guard('admin')->id(),
            'opening_amount' => $request->opening_amount,
            'status'         => 'open',
            'opened_at'      => now(),
        ]);

        return redirect()->route('pos.terminal', $register->id)->with('success', 'تم فتح الجلسة بنجاح');
    }

    // ─────────────────────────────────────────────
    // تنفيذ عملية بيع كاملة (checkout)
    // ─────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'register_id' => 'required|exists:pos_registers,id',
            'items'       => 'required|array|min:1',
        ]);

        $comCode  = $this->comCode();
        $register = PosRegister::where('com_code', $comCode)->findOrFail($request->register_id);
        $session  = PosSession::where('register_id', $register->id)->where('status', 'open')->first();

        if (!$session) {
            return response()->json(['success' => false, 'message' => 'لا توجد جلسة كاشير مفتوحة'], 422);
        }

        $customerId = $request->customer_id ?: $this->walkInCustomer()->id;

        $invoice = DB::transaction(function () use ($request, $comCode, $register, $session, $customerId) {
            $subtotal = 0;
            foreach ($request->items as $row) {
                $qty     = (float)($row['qty'] ?? 0);
                $price   = (float)($row['price'] ?? 0);
                $disc    = (float)($row['discount_percent'] ?? 0);
                $subtotal += round($qty * $price * (1 - $disc / 100), 2);
            }
            $discountAmount = (float)($request->discount_amount ?? 0);
            $taxableAmount  = $subtotal - $discountAmount;
            $headerTaxRate  = (float)($request->tax_rate ?? 14);
            $taxAmount      = round($taxableAmount * $headerTaxRate / 100, 2);
            $total          = $taxableAmount + $taxAmount;

            $invoice = SalesInvoice::create([
                'com_code'         => $comCode,
                'invoice_number'   => $this->nextInvoiceNumber(),
                'date'             => now()->toDateString(),
                'customer_id'      => $customerId,
                'branch_id'        => $register->branch_id,
                'warehouse_id'     => $register->warehouse_id,
                'pos_session_id'   => $session->id,
                'invoice_type'     => 'cash',
                'subtotal'         => $subtotal,
                'discount_amount'  => $discountAmount,
                'tax_rate'         => $headerTaxRate,
                'tax_amount'       => $taxAmount,
                'total'            => $total,
                'paid_amount'      => 0,
                'remaining_amount' => $total,
                'payment_status'   => 'unpaid',
                'status'           => 'issued',
                'created_by'       => Auth::guard('admin')->id(),
            ]);

            $totalCogs = 0.0;
            foreach ($request->items as $row) {
                $qty     = (float)($row['qty'] ?? 0);
                $price   = (float)($row['price'] ?? 0);
                $disc    = (float)($row['discount_percent'] ?? 0);
                $discAmt = round($qty * $price * $disc / 100, 2);
                $taxRate = (float)($row['tax_rate'] ?? $headerTaxRate);
                $lineNet = round($qty * $price - $discAmt, 2);
                $taxAmt  = round($lineNet * $taxRate / 100, 2);

                SalesInvoiceItem::create([
                    'invoice_id'       => $invoice->id,
                    'item_id'          => $row['item_id'],
                    'unit_id'          => $row['unit_id'] ?? null,
                    'quantity'         => $qty,
                    'unit_price'       => $price,
                    'discount_percent' => $disc,
                    'discount_amount'  => $discAmt,
                    'tax_rate'         => $taxRate,
                    'tax_amount'       => $taxAmt,
                    'total'            => $lineNet + $taxAmt,
                ]);

                $movement = StockService::adjustStock(
                    $comCode, (int) $row['item_id'], $register->warehouse_id, -$qty,
                    'sales_out', 'sales_invoice', $invoice->id, $price, now()->toDateString(),
                    'بيع POS - جلسة #'.$session->id, Auth::guard('admin')->id()
                );
                $totalCogs += (float) $movement->total_cost;
            }

            $this->postInvoiceJournal($invoice, $taxableAmount, $taxAmount, $totalCogs);
            $this->collectFullPayment($invoice, $register, $customerId);

            return $invoice;
        });

        return response()->json([
            'success'    => true,
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'total'      => $invoice->total,
            'print_url'  => route('sales_invoices.print', $invoice->id),
        ]);
    }

    /** نفس أدوار الترحيل المستخدمة فى فاتورة البيع العادية — بدون قواعد ترحيل جديدة */
    private function postInvoiceJournal(SalesInvoice $invoice, float $taxableAmount, float $taxAmount, float $totalCogs): void
    {
        $comCode = $invoice->com_code;

        JournalPostingService::post('sales_invoice_issued', $comCode, [
            ['role' => 'AR_CONTROL',    'debit' => $invoice->total, 'credit' => 0, 'party_type' => 'customer', 'party_id' => $invoice->customer_id],
            ['role' => 'SALES_REVENUE', 'debit' => 0, 'credit' => $taxableAmount],
            ['role' => 'VAT_OUTPUT',    'debit' => 0, 'credit' => $taxAmount],
        ], [
            'source_module' => 'sales_invoice',
            'source_id'     => $invoice->id,
            'entry_date'    => $invoice->date,
            'reference'     => $invoice->invoice_number,
            'description'   => 'بيع POS '.$invoice->invoice_number,
            'created_by'    => Auth::guard('admin')->id(),
        ]);

        if ($totalCogs > 0) {
            JournalPostingService::post('sales_invoice_cogs', $comCode, [
                ['role' => 'COGS',      'debit' => $totalCogs, 'credit' => 0],
                ['role' => 'INVENTORY', 'debit' => 0, 'credit' => $totalCogs],
            ], [
                'source_module' => 'sales_invoice',
                'source_id'     => $invoice->id,
                'entry_date'    => $invoice->date,
                'reference'     => $invoice->invoice_number,
                'description'   => 'تكلفة البضاعة المباعة - بيع POS '.$invoice->invoice_number,
                'created_by'    => Auth::guard('admin')->id(),
            ]);
        }
    }

    /** تحصيل فوري بالكامل: سند قبض نقدي عبر الخزينة + دفعة مرتبطة به تُغلق الفاتورة */
    private function collectFullPayment(SalesInvoice $invoice, PosRegister $register, int $customerId): void
    {
        $voucher = TreasuryService::createVoucher($invoice->com_code, 'receipt', [
            'date'             => $invoice->date,
            'payment_method'   => 'cash',
            'cash_box_id'      => $register->cash_box_id,
            'party_type'       => 'customer',
            'party_id'         => $customerId,
            'amount'           => $invoice->total,
            'linked_type'      => 'sales_invoice',
            'linked_id'        => $invoice->id,
            'reference_number' => $invoice->invoice_number,
            'notes'            => 'تحصيل بيع POS',
        ], Auth::guard('admin')->id());

        SalesPayment::create([
            'com_code'            => $invoice->com_code,
            'payment_number'      => $this->nextPaymentNumber(),
            'date'                => $invoice->date,
            'customer_id'         => $customerId,
            'invoice_id'          => $invoice->id,
            'branch_id'           => $invoice->branch_id,
            'amount'              => $invoice->total,
            'payment_method'      => 'cash',
            'treasury_voucher_id' => $voucher->id,
            'reference_number'    => $invoice->invoice_number,
            'notes'               => 'تحصيل بيع POS',
            'created_by'          => Auth::guard('admin')->id(),
        ]);

        $invoice->update([
            'paid_amount'      => $invoice->total,
            'remaining_amount' => 0,
            'payment_status'   => 'paid',
        ]);
    }

    // ─────────────────────────────────────────────
    // جلسات الكاشير
    // ─────────────────────────────────────────────
    public function sessionsIndex(Request $request)
    {
        $query = PosSession::with(['register', 'openedBy'])->where('com_code', $this->comCode());
        if ($request->filled('status')) $query->where('status', $request->status);
        $sessions = $query->orderByDesc('id')->paginate(20);
        return view('admin.sales.pos.sessions_index', compact('sessions'));
    }

    public function sessionShow($id)
    {
        $session = PosSession::with(['register', 'openedBy', 'invoices.customer'])
            ->where('com_code', $this->comCode())->findOrFail($id);
        return view('admin.sales.pos.sessions_show', compact('session'));
    }

    public function closeSessionForm($id)
    {
        $session = PosSession::where('com_code', $this->comCode())->where('status', 'open')->findOrFail($id);
        return view('admin.sales.pos.close_session', compact('session'));
    }

    public function closeSession(Request $request, $id)
    {
        $request->validate(['counted_closing_amount' => 'required|numeric|min:0']);

        $session = PosSession::where('com_code', $this->comCode())->where('status', 'open')->findOrFail($id);
        $expected = $session->opening_amount + $session->sales_total;

        $session->update([
            'expected_closing_amount' => $expected,
            'counted_closing_amount'  => $request->counted_closing_amount,
            'difference'              => $request->counted_closing_amount - $expected,
            'status'                  => 'closed',
            'closed_at'               => now(),
        ]);

        return redirect()->route('pos_sessions.show', $session->id)->with('success', 'تم إغلاق الجلسة بنجاح');
    }
}
