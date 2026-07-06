<?php
namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\{SalesPayment, SalesInvoice, Customer, Branche};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

class SalesPaymentsController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    private function nextPaymentNumber(): string
    {
        $last = SalesPayment::where('com_code', $this->comCode())->whereYear('created_at', now()->year)->max('payment_number');
        $num  = $last ? ((int) substr($last, -4)) + 1 : 1;
        return 'PAY-'.now()->year.'-'.str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    public function index(Request $request)
    {
        $query = SalesPayment::with(['customer','invoice'])->where('com_code', $this->comCode());
        if ($request->filled('customer_id'))     $query->where('customer_id', $request->customer_id);
        if ($request->filled('payment_method'))  $query->where('payment_method', $request->payment_method);
        if ($request->filled('from'))            $query->whereDate('date', '>=', $request->from);
        if ($request->filled('to'))              $query->whereDate('date', '<=', $request->to);
        $data      = $query->orderByDesc('date')->paginate(20);
        $customers = Customer::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        $totalAmount = SalesPayment::where('com_code', $this->comCode())->sum('amount');
        return view('admin.sales.payments.index', compact('data','customers','totalAmount'));
    }

    public function create(Request $request)
    {
        $customers    = Customer::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        $branches     = Branche::where('com_code', $this->comCode())->get();
        $invoices     = collect();
        $nextNumber   = $this->nextPaymentNumber();
        $selectedInv  = null;

        if ($request->filled('invoice_id')) {
            $selectedInv = SalesInvoice::with('customer')->where('com_code', $this->comCode())->find($request->invoice_id);
            if ($selectedInv) {
                $invoices = SalesInvoice::where('com_code', $this->comCode())
                    ->where('customer_id', $selectedInv->customer_id)
                    ->whereIn('payment_status', ['unpaid','partial'])
                    ->get();
            }
        }

        return view('admin.sales.payments.create', compact('customers','branches','nextNumber','invoices','selectedInv'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'           => 'required|date',
            'customer_id'    => 'required|exists:customers,id',
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank,cheque,pos',
        ]);

        DB::transaction(function () use ($request) {
            $payment = SalesPayment::create([
                'com_code'         => $this->comCode(),
                'payment_number'   => $this->nextPaymentNumber(),
                'date'             => $request->date,
                'customer_id'      => $request->customer_id,
                'invoice_id'       => $request->invoice_id ?: null,
                'branch_id'        => $request->branch_id,
                'amount'           => $request->amount,
                'payment_method'   => $request->payment_method,
                'bank_name'        => $request->bank_name,
                'cheque_number'    => $request->cheque_number,
                'cheque_date'      => $request->cheque_date,
                'reference_number' => $request->reference_number,
                'notes'            => $request->notes,
                'created_by'       => Auth::guard('admin')->id(),
            ]);

            // تحديث حالة السداد للفاتورة
            if ($payment->invoice_id) {
                $invoice = SalesInvoice::find($payment->invoice_id);
                if ($invoice) {
                    $totalPaid      = $invoice->payments()->sum('amount');
                    $remaining      = max(0, $invoice->total - $totalPaid);
                    $paymentStatus  = $remaining <= 0 ? 'paid' : ($totalPaid > 0 ? 'partial' : 'unpaid');
                    $invoice->update([
                        'paid_amount'     => $totalPaid,
                        'remaining_amount'=> $remaining,
                        'payment_status'  => $paymentStatus,
                    ]);
                }
            }
        });

        return redirect()->route('sales_payments.index')->with('success', 'تم تسجيل الدفعة بنجاح');
    }

    public function show($id)
    {
        $payment = SalesPayment::with(['customer','invoice','branch'])
            ->where('com_code', $this->comCode())->findOrFail($id);
        return view('admin.sales.payments.show', compact('payment'));
    }

    public function delete($id)
    {
        DB::transaction(function () use ($id) {
            $payment = SalesPayment::where('com_code', $this->comCode())->findOrFail($id);
            $invoiceId = $payment->invoice_id;
            $payment->delete();

            // إعادة حساب الفاتورة
            if ($invoiceId) {
                $invoice = SalesInvoice::find($invoiceId);
                if ($invoice) {
                    $totalPaid = $invoice->payments()->sum('amount');
                    $remaining = max(0, $invoice->total - $totalPaid);
                    $invoice->update([
                        'paid_amount'     => $totalPaid,
                        'remaining_amount'=> $remaining,
                        'payment_status'  => $remaining <= 0 ? 'paid' : ($totalPaid > 0 ? 'partial' : 'unpaid'),
                    ]);
                }
            }
        });
        return redirect()->route('sales_payments.index')->with('success', 'تم حذف الدفعة');
    }
}
