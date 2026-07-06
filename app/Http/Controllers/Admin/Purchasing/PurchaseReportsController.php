<?php
namespace App\Http\Controllers\Admin\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\{PurchaseInvoice, PurchasePayment, PurchaseOrder, Supplier};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseReportsController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function index()
    {
        $cc = $this->comCode();
        $stats = [
            'total_invoiced'  => PurchaseInvoice::where('com_code', $cc)->where('status', 'received')->sum('total'),
            'total_paid'      => PurchasePayment::where('com_code', $cc)->sum('amount'),
            'total_debt'      => PurchaseInvoice::where('com_code', $cc)->whereIn('payment_status', ['unpaid','partial'])->sum('remaining_amount'),
            'invoices_count'  => PurchaseInvoice::where('com_code', $cc)->where('status', 'received')->count(),
            'orders_count'    => PurchaseOrder::where('com_code', $cc)->whereNotIn('status', ['cancelled'])->count(),
            'suppliers_count' => Supplier::where('com_code', $cc)->where('is_active', true)->count(),
        ];

        $monthlyPurchases = PurchaseInvoice::where('com_code', $cc)
            ->where('status', 'received')
            ->where('date', '>=', now()->subMonths(6))
            ->selectRaw('YEAR(date) yr, MONTH(date) mn, SUM(total) total_amount, COUNT(*) cnt')
            ->groupBy('yr', 'mn')
            ->orderBy('yr')->orderBy('mn')
            ->get();

        $topSuppliers = Supplier::where('com_code', $cc)
            ->withSum(['invoices as total_purchases' => fn($q) => $q->where('status', 'received')], 'total')
            ->orderByDesc('total_purchases')
            ->limit(5)->get();

        return view('admin.purchasing.reports.index', compact('stats','monthlyPurchases','topSuppliers'));
    }

    public function summary(Request $request)
    {
        $query = PurchaseInvoice::with('supplier')->where('com_code', $this->comCode())->where('status', 'received');
        if ($request->filled('from')) $query->whereDate('date', '>=', $request->from);
        if ($request->filled('to'))   $query->whereDate('date', '<=', $request->to);
        $data   = $query->orderByDesc('date')->paginate(30);
        $totals = [
            'subtotal'  => $data->sum('subtotal'),
            'discount'  => $data->sum('discount_amount'),
            'tax'       => $data->sum('tax_amount'),
            'total'     => $data->sum('total'),
            'paid'      => $data->sum('paid_amount'),
            'remaining' => $data->sum('remaining_amount'),
        ];
        return view('admin.purchasing.reports.summary', compact('data','totals'));
    }

    public function bySupplier(Request $request)
    {
        $query = Supplier::where('com_code', $this->comCode())
            ->withSum(['invoices as total_invoiced' => fn($q) => $q->where('status','received')], 'total')
            ->withSum(['invoices as total_paid'], 'paid_amount')
            ->withSum(['invoices as total_remaining' => fn($q) => $q->whereIn('payment_status',['unpaid','partial'])], 'remaining_amount')
            ->withCount(['invoices as invoice_count' => fn($q) => $q->where('status','received')]);
        if ($request->filled('search')) $query->where('name', 'like', '%'.$request->search.'%');
        $data = $query->orderByDesc('total_invoiced')->paginate(20);
        return view('admin.purchasing.reports.by_supplier', compact('data'));
    }

    public function byItem(Request $request)
    {
        $query = DB::table('purchase_invoice_items as pii')
            ->join('items as i', 'pii.item_id', '=', 'i.id')
            ->join('purchase_invoices as pi', 'pii.invoice_id', '=', 'pi.id')
            ->where('pi.com_code', $this->comCode())
            ->where('pi.status', 'received')
            ->selectRaw('i.id, i.name, i.code, SUM(pii.quantity) total_qty, SUM(pii.total) total_amount, COUNT(DISTINCT pii.invoice_id) invoice_count')
            ->groupBy('i.id', 'i.name', 'i.code');
        if ($request->filled('from')) $query->whereDate('pi.date', '>=', $request->from);
        if ($request->filled('to'))   $query->whereDate('pi.date', '<=', $request->to);
        $data = $query->orderByDesc('total_amount')->paginate(20);
        return view('admin.purchasing.reports.by_item', compact('data'));
    }

    public function debt(Request $request)
    {
        $query = Supplier::where('com_code', $this->comCode())
            ->whereHas('invoices', fn($q) => $q->whereIn('payment_status', ['unpaid','partial']))
            ->with(['invoices' => fn($q) => $q->whereIn('payment_status', ['unpaid','partial'])->orderBy('date')]);
        if ($request->filled('search')) $query->where('name', 'like', '%'.$request->search.'%');
        $data = $query->orderBy('name')->paginate(20);
        $totalDebt = PurchaseInvoice::where('com_code', $this->comCode())
            ->whereIn('payment_status', ['unpaid','partial'])->sum('remaining_amount');
        return view('admin.purchasing.reports.debt', compact('data','totalDebt'));
    }
}
