<?php
namespace App\Http\Controllers\Admin\Treasury;

use App\Http\Controllers\Controller;
use App\Models\{TreasuryVoucher, CashBox, BankAccount, Customer, Supplier, Employee, ChartOfAccount, SalesInvoice};
use App\Services\Treasury\TreasuryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReceiptVouchersController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function index(Request $request)
    {
        $query = TreasuryVoucher::where('com_code', $this->comCode())->where('voucher_type', 'receipt');
        if ($request->filled('party_type')) $query->where('party_type', $request->party_type);
        if ($request->filled('from'))       $query->whereDate('date', '>=', $request->from);
        if ($request->filled('to'))         $query->whereDate('date', '<=', $request->to);
        $data = $query->orderByDesc('date')->orderByDesc('id')->paginate(20);
        return view('admin.treasury.receipts.index', compact('data'));
    }

    public function create()
    {
        $comCode   = $this->comCode();
        $cashBoxes = CashBox::where('com_code', $comCode)->where('is_active', true)->get();
        $banks     = BankAccount::where('com_code', $comCode)->where('is_active', true)->get();
        $customers = Customer::where('com_code', $comCode)->where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('com_code', $comCode)->where('is_active', true)->orderBy('name')->get();
        $employees = Employee::where('com_code', $comCode)->orderBy('employee_name_A')->get();
        $accounts  = ChartOfAccount::where('com_code', $comCode)->where('is_group', false)->orderBy('account_code')->get();
        return view('admin.treasury.receipts.create', compact('cashBoxes', 'banks', 'customers', 'suppliers', 'employees', 'accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'           => 'required|date',
            'payment_method' => 'required|in:cash,bank,cheque',
            'party_type'     => 'required|in:customer,supplier,employee,other',
            'amount'         => 'required|numeric|min:0.01',
        ]);

        try {
            $voucher = TreasuryService::createVoucher($this->comCode(), 'receipt', $request->all(), Auth::guard('admin')->id());
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        // ربط اختياري بفاتورة بيع لتحديث حالة السداد
        if ($request->filled('sales_invoice_id')) {
            $invoice = SalesInvoice::where('com_code', $this->comCode())->find($request->sales_invoice_id);
            if ($invoice) {
                \App\Models\SalesPayment::create([
                    'com_code'           => $this->comCode(),
                    'payment_number'     => 'PAY-'.$voucher->voucher_number,
                    'date'               => $voucher->date,
                    'customer_id'        => $invoice->customer_id,
                    'invoice_id'         => $invoice->id,
                    'amount'             => $voucher->amount,
                    'payment_method'     => $voucher->payment_method,
                    'treasury_voucher_id'=> $voucher->id,
                    'created_by'         => Auth::guard('admin')->id(),
                ]);
                $totalPaid = $invoice->payments()->sum('amount');
                $remaining = max(0, $invoice->total - $totalPaid);
                $invoice->update([
                    'paid_amount'      => $totalPaid,
                    'remaining_amount' => $remaining,
                    'payment_status'   => $remaining <= 0 ? 'paid' : ($totalPaid > 0 ? 'partial' : 'unpaid'),
                ]);
            }
        }

        return redirect()->route('treasury_receipts.show', $voucher->id)->with('success', 'تم تسجيل سند القبض بنجاح');
    }

    public function show($id)
    {
        $voucher = TreasuryVoucher::with(['cashBox', 'bankAccount', 'cheque', 'createdBy'])
            ->where('com_code', $this->comCode())->where('voucher_type', 'receipt')->findOrFail($id);
        return view('admin.treasury.receipts.show', compact('voucher'));
    }
}
