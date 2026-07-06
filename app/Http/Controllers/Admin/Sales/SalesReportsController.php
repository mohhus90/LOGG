<?php
namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\{SalesInvoice, SalesInvoiceItem, SalesPayment, SalesOrder, Customer, Item};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesReportsController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function index()
    {
        $cc = $this->comCode();
        $stats = [
            'total_invoiced'  => SalesInvoice::where('com_code', $cc)->where('status', 'issued')->sum('total'),
            'total_collected' => SalesPayment::where('com_code', $cc)->sum('amount'),
            'total_debt'      => SalesInvoice::where('com_code', $cc)->whereIn('payment_status', ['unpaid','partial'])->sum('remaining_amount'),
            'invoices_count'  => SalesInvoice::where('com_code', $cc)->where('status', 'issued')->count(),
            'orders_count'    => SalesOrder::where('com_code', $cc)->whereNotIn('status', ['cancelled'])->count(),
            'customers_count' => Customer::where('com_code', $cc)->where('is_active', true)->count(),
        ];

        // مبيعات آخر 6 أشهر
        $monthlySales = SalesInvoice::where('com_code', $cc)
            ->where('status', 'issued')
            ->where('date', '>=', now()->subMonths(6))
            ->selectRaw('YEAR(date) yr, MONTH(date) mn, SUM(total) total_amount, COUNT(*) cnt')
            ->groupBy('yr', 'mn')
            ->orderBy('yr')->orderBy('mn')
            ->get();

        // أفضل 5 عملاء
        $topCustomers = Customer::where('com_code', $cc)
            ->withSum(['invoices as total_sales' => fn($q) => $q->where('status', 'issued')], 'total')
            ->orderByDesc('total_sales')
            ->limit(5)->get();

        return view('admin.sales.reports.index', compact('stats','monthlySales','topCustomers'));
    }

    public function summary(Request $request)
    {
        $query = SalesInvoice::with('customer')->where('com_code', $this->comCode())->where('status', 'issued');
        if ($request->filled('from')) $query->whereDate('date', '>=', $request->from);
        if ($request->filled('to'))   $query->whereDate('date', '<=', $request->to);
        $data   = $query->orderByDesc('date')->paginate(30);
        $totals = [
            'subtotal'  => $data->sum('subtotal'),
            'discount'  => $data->sum('discount_amount'),
            'tax'       => $data->sum('tax_amount'),
            'total'     => $data->sum('total'),
            'collected' => $data->sum('paid_amount'),
            'remaining' => $data->sum('remaining_amount'),
        ];
        return view('admin.sales.reports.summary', compact('data','totals'));
    }

    public function byCustomer(Request $request)
    {
        $query = Customer::where('com_code', $this->comCode())
            ->withSum(['invoices as total_invoiced' => fn($q) => $q->where('status','issued')], 'total')
            ->withSum(['invoices as total_collected'], 'paid_amount')
            ->withSum(['invoices as total_remaining' => fn($q) => $q->whereIn('payment_status',['unpaid','partial'])], 'remaining_amount')
            ->withCount(['invoices as invoice_count' => fn($q) => $q->where('status','issued')]);
        if ($request->filled('search')) $query->where('name', 'like', '%'.$request->search.'%');
        $data = $query->orderByDesc('total_invoiced')->paginate(20);
        return view('admin.sales.reports.by_customer', compact('data'));
    }

    public function byItem(Request $request)
    {
        $query = DB::table('sales_invoice_items as sii')
            ->join('items as i', 'sii.item_id', '=', 'i.id')
            ->join('sales_invoices as si', 'sii.invoice_id', '=', 'si.id')
            ->where('si.com_code', $this->comCode())
            ->where('si.status', 'issued')
            ->selectRaw('i.id, i.name, i.code, SUM(sii.quantity) total_qty, SUM(sii.total) total_amount, COUNT(DISTINCT sii.invoice_id) invoice_count')
            ->groupBy('i.id', 'i.name', 'i.code');
        if ($request->filled('from')) $query->whereDate('si.date', '>=', $request->from);
        if ($request->filled('to'))   $query->whereDate('si.date', '<=', $request->to);
        $data = $query->orderByDesc('total_amount')->paginate(20);
        return view('admin.sales.reports.by_item', compact('data'));
    }

    public function debt(Request $request)
    {
        $query = Customer::where('com_code', $this->comCode())
            ->whereHas('invoices', fn($q) => $q->whereIn('payment_status', ['unpaid','partial']))
            ->with(['invoices' => fn($q) => $q->whereIn('payment_status', ['unpaid','partial'])->orderBy('date')]);
        if ($request->filled('search')) $query->where('name', 'like', '%'.$request->search.'%');
        $data = $query->orderBy('name')->paginate(20);
        $totalDebt = SalesInvoice::where('com_code', $this->comCode())
            ->whereIn('payment_status', ['unpaid','partial'])->sum('remaining_amount');
        return view('admin.sales.reports.debt', compact('data','totalDebt'));
    }
}
