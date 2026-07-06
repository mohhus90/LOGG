<?php
namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\{SalesReturn, SalesReturnItem, SalesInvoice, Customer, ItemUnit, Branche, Warehouse};
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

class SalesReturnsController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    private function nextReturnNumber(): string
    {
        $last = SalesReturn::where('com_code', $this->comCode())->whereYear('created_at', now()->year)->max('return_number');
        $num  = $last ? ((int) substr($last, -4)) + 1 : 1;
        return 'RTN-'.now()->year.'-'.str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    public function index(Request $request)
    {
        $query = SalesReturn::with('customer')->where('com_code', $this->comCode());
        if ($request->filled('customer_id')) $query->where('customer_id', $request->customer_id);
        if ($request->filled('status'))      $query->where('status', $request->status);
        if ($request->filled('from'))        $query->whereDate('date', '>=', $request->from);
        if ($request->filled('to'))          $query->whereDate('date', '<=', $request->to);
        $data      = $query->orderByDesc('date')->paginate(20);
        $customers = Customer::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        return view('admin.sales.returns.index', compact('data','customers'));
    }

    public function create(Request $request)
    {
        $customers  = Customer::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        $branches   = Branche::where('com_code', $this->comCode())->get();
        $units      = ItemUnit::where('com_code', $this->comCode())->where('is_active', true)->get();
        $warehouses = Warehouse::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        $nextNumber = $this->nextReturnNumber();
        $invoice    = $request->invoice_id
            ? SalesInvoice::with('items.item')->where('com_code', $this->comCode())->find($request->invoice_id)
            : null;
        return view('admin.sales.returns.create', compact('customers','branches','units','nextNumber','invoice'));
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
                $subtotal += round((float)($row['qty'] ?? 0) * (float)($row['price'] ?? 0), 2);
            }
            $taxAmount = round($subtotal * 14 / 100, 2);

            $ret = SalesReturn::create([
                'com_code'    => $this->comCode(),
                'return_number'=> $this->nextReturnNumber(),
                'date'        => $request->date,
                'customer_id' => $request->customer_id,
                'invoice_id'  => $request->invoice_id ?: null,
                'branch_id'   => $request->branch_id,
                'warehouse_id'=> $request->warehouse_id ?: Warehouse::defaultId($this->comCode()),
                'reason'      => $request->reason,
                'subtotal'    => $subtotal,
                'tax_amount'  => $taxAmount,
                'total'       => $subtotal + $taxAmount,
                'status'      => 'draft',
                'notes'       => $request->notes,
                'created_by'  => Auth::guard('admin')->id(),
            ]);

            foreach ($request->items as $row) {
                $qty   = (float)($row['qty'] ?? 0);
                $price = (float)($row['price'] ?? 0);
                SalesReturnItem::create([
                    'return_id'   => $ret->id,
                    'item_id'     => $row['item_id'] ?? null,
                    'description' => $row['description'] ?? null,
                    'unit_id'     => $row['unit_id'] ?? null,
                    'quantity'    => $qty,
                    'unit_price'  => $price,
                    'total'       => round($qty * $price, 2),
                ]);
            }
        });

        return redirect()->route('sales_returns.index')->with('success', 'تم إنشاء مرتجع البيع بنجاح');
    }

    public function show($id)
    {
        $ret = SalesReturn::with(['customer','invoice','branch','items.item','items.unit'])
            ->where('com_code', $this->comCode())->findOrFail($id);
        return view('admin.sales.returns.show', compact('ret'));
    }

    public function approve($id)
    {
        $ret = SalesReturn::with('items')->where('com_code', $this->comCode())->findOrFail($id);
        if ($ret->status !== 'draft') {
            return back()->with('error', 'تم البت في هذا المرتجع بالفعل');
        }

        DB::transaction(function () use ($ret) {
            if ($ret->warehouse_id) {
                foreach ($ret->items as $row) {
                    if (!$row->item_id) continue;
                    StockService::adjustStock(
                        $ret->com_code, $row->item_id, $ret->warehouse_id, $row->quantity,
                        'sales_return_in', 'sales_return', $ret->id, $row->unit_price, $ret->date,
                        null, Auth::guard('admin')->id()
                    );
                }
            }
            $ret->update(['status' => 'approved']);
        });

        return back()->with('success', 'تم اعتماد المرتجع وتحديث أرصدة المخزون');
    }

    public function reject($id)
    {
        SalesReturn::where('com_code', $this->comCode())->findOrFail($id)->update(['status' => 'rejected']);
        return back()->with('success', 'تم رفض المرتجع');
    }

    public function delete($id)
    {
        SalesReturn::where('com_code', $this->comCode())->findOrFail($id)->delete();
        return redirect()->route('sales_returns.index')->with('success', 'تم حذف المرتجع');
    }
}
