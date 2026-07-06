<?php
namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\{SalesQuotation, SalesQuotationItem, SalesOrder, SalesOrderItem, Customer, Item, ItemUnit, Branche};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesQuotationsController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    private function nextNumber(): string
    {
        $last = SalesQuotation::where('com_code', $this->comCode())
            ->whereYear('created_at', now()->year)->max('quote_number');
        $num  = $last ? ((int) substr($last, -4)) + 1 : 1;
        return 'QT-'.now()->year.'-'.str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    public function index(Request $request)
    {
        $query = SalesQuotation::with('customer')->where('com_code', $this->comCode());
        if ($request->filled('customer_id')) $query->where('customer_id', $request->customer_id);
        if ($request->filled('status'))      $query->where('status', $request->status);
        if ($request->filled('from'))        $query->whereDate('date', '>=', $request->from);
        if ($request->filled('to'))          $query->whereDate('date', '<=', $request->to);
        $data      = $query->orderByDesc('date')->paginate(20);
        $customers = Customer::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        return view('admin.sales.quotations.index', compact('data','customers'));
    }

    public function create()
    {
        $customers  = Customer::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        $branches   = Branche::where('com_code', $this->comCode())->get();
        $units      = ItemUnit::where('com_code', $this->comCode())->where('is_active', true)->get();
        $items      = Item::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        $nextNumber = $this->nextNumber();
        return view('admin.sales.quotations.create', compact('customers','branches','units','items','nextNumber'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'        => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'items'       => 'required|array|min:1',
            'items.*.qty' => 'required|numeric|min:0.001',
        ]);

        DB::transaction(function () use ($request) {
            $subtotal = 0;
            foreach ($request->items as $row) {
                $price    = (float)($row['price'] ?? 0);
                $qty      = (float)($row['qty'] ?? 0);
                $disc     = (float)($row['discount_percent'] ?? 0);
                $lineTotal = round($qty * $price * (1 - $disc / 100), 2);
                $subtotal += $lineTotal;
            }
            $discountAmount = $request->discount_type === 'percent'
                ? round($subtotal * ($request->discount_value / 100), 2)
                : (float)$request->discount_value;
            $taxableAmount = $subtotal - $discountAmount;
            $taxRate       = (float)($request->tax_rate ?? 14);
            $taxAmount     = round($taxableAmount * $taxRate / 100, 2);
            $total         = $taxableAmount + $taxAmount;

            $quote = SalesQuotation::create([
                'com_code'        => $this->comCode(),
                'quote_number'    => $this->nextNumber(),
                'date'            => $request->date,
                'valid_until'     => $request->valid_until,
                'customer_id'     => $request->customer_id,
                'branch_id'       => $request->branch_id,
                'subtotal'        => $subtotal,
                'discount_type'   => $request->discount_type ?? 'percent',
                'discount_value'  => $request->discount_value ?? 0,
                'discount_amount' => $discountAmount,
                'tax_rate'        => $taxRate,
                'tax_amount'      => $taxAmount,
                'total'           => $total,
                'status'          => 'draft',
                'notes'           => $request->notes,
                'terms'           => $request->terms,
                'created_by'      => Auth::guard('admin')->id(),
            ]);

            foreach ($request->items as $i => $row) {
                $price     = (float)($row['price'] ?? 0);
                $qty       = (float)($row['qty'] ?? 0);
                $disc      = (float)($row['discount_percent'] ?? 0);
                $discAmt   = round($qty * $price * $disc / 100, 2);
                $lineTotal = round($qty * $price - $discAmt, 2);
                SalesQuotationItem::create([
                    'quotation_id'     => $quote->id,
                    'item_id'          => $row['item_id'] ?? null,
                    'description'      => $row['description'] ?? null,
                    'unit_id'          => $row['unit_id'] ?? null,
                    'quantity'         => $qty,
                    'unit_price'       => $price,
                    'discount_percent' => $disc,
                    'discount_amount'  => $discAmt,
                    'total'            => $lineTotal,
                    'sort_order'       => $i,
                ]);
            }
        });

        return redirect()->route('sales_quotations.index')->with('success', 'تم إنشاء عرض السعر بنجاح');
    }

    public function show($id)
    {
        $quote = SalesQuotation::with(['customer','branch','items.item','items.unit','createdBy'])
            ->where('com_code', $this->comCode())->findOrFail($id);
        return view('admin.sales.quotations.show', compact('quote'));
    }

    public function edit($id)
    {
        $quote     = SalesQuotation::with('items')->where('com_code', $this->comCode())->findOrFail($id);
        $customers = Customer::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        $branches  = Branche::where('com_code', $this->comCode())->get();
        $units     = ItemUnit::where('com_code', $this->comCode())->where('is_active', true)->get();
        $items     = Item::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        return view('admin.sales.quotations.edit', compact('quote','customers','branches','units','items'));
    }

    public function update(Request $request, $id)
    {
        $request->validate(['date' => 'required|date', 'customer_id' => 'required']);
        $quote = SalesQuotation::where('com_code', $this->comCode())->findOrFail($id);

        DB::transaction(function () use ($request, $quote) {
            $subtotal = 0;
            foreach ($request->items ?? [] as $row) {
                $price    = (float)($row['price'] ?? 0);
                $qty      = (float)($row['qty'] ?? 0);
                $disc     = (float)($row['discount_percent'] ?? 0);
                $subtotal += round($qty * $price * (1 - $disc / 100), 2);
            }
            $discountAmount = $request->discount_type === 'percent'
                ? round($subtotal * ($request->discount_value / 100), 2)
                : (float)$request->discount_value;
            $taxableAmount = $subtotal - $discountAmount;
            $taxRate       = (float)($request->tax_rate ?? 14);
            $taxAmount     = round($taxableAmount * $taxRate / 100, 2);

            $quote->update([
                'date'            => $request->date,
                'valid_until'     => $request->valid_until,
                'customer_id'     => $request->customer_id,
                'branch_id'       => $request->branch_id,
                'subtotal'        => $subtotal,
                'discount_type'   => $request->discount_type ?? 'percent',
                'discount_value'  => $request->discount_value ?? 0,
                'discount_amount' => $discountAmount,
                'tax_rate'        => $taxRate,
                'tax_amount'      => $taxAmount,
                'total'           => $taxableAmount + $taxAmount,
                'notes'           => $request->notes,
                'terms'           => $request->terms,
            ]);

            $quote->items()->delete();
            foreach ($request->items ?? [] as $i => $row) {
                $price    = (float)($row['price'] ?? 0);
                $qty      = (float)($row['qty'] ?? 0);
                $disc     = (float)($row['discount_percent'] ?? 0);
                $discAmt  = round($qty * $price * $disc / 100, 2);
                SalesQuotationItem::create([
                    'quotation_id'     => $quote->id,
                    'item_id'          => $row['item_id'] ?? null,
                    'description'      => $row['description'] ?? null,
                    'unit_id'          => $row['unit_id'] ?? null,
                    'quantity'         => $qty,
                    'unit_price'       => $price,
                    'discount_percent' => $disc,
                    'discount_amount'  => $discAmt,
                    'total'            => round($qty * $price - $discAmt, 2),
                    'sort_order'       => $i,
                ]);
            }
        });

        return redirect()->route('sales_quotations.show', $id)->with('success', 'تم تعديل عرض السعر');
    }

    public function updateStatus(Request $request, $id)
    {
        $quote = SalesQuotation::where('com_code', $this->comCode())->findOrFail($id);
        $quote->update(['status' => $request->status]);
        return back()->with('success', 'تم تغيير حالة عرض السعر');
    }

    public function convertToOrder($id)
    {
        $quote = SalesQuotation::with('items')->where('com_code', $this->comCode())->findOrFail($id);

        $lastOrder  = SalesOrder::where('com_code', $this->comCode())->whereYear('created_at', now()->year)->max('order_number');
        $orderNum   = $lastOrder ? 'SO-'.now()->year.'-'.str_pad(((int) substr($lastOrder, -4)) + 1, 4, '0', STR_PAD_LEFT)
                                 : 'SO-'.now()->year.'-0001';

        DB::transaction(function () use ($quote, $orderNum) {
            $order = SalesOrder::create([
                'com_code'    => $this->comCode(),
                'order_number'=> $orderNum,
                'date'        => today(),
                'customer_id' => $quote->customer_id,
                'branch_id'   => $quote->branch_id,
                'quotation_id'=> $quote->id,
                'subtotal'    => $quote->subtotal,
                'discount_amount' => $quote->discount_amount,
                'tax_rate'    => $quote->tax_rate,
                'tax_amount'  => $quote->tax_amount,
                'total'       => $quote->total,
                'status'      => 'confirmed',
                'created_by'  => Auth::guard('admin')->id(),
            ]);
            foreach ($quote->items as $qi) {
                SalesOrderItem::create([
                    'order_id'         => $order->id,
                    'item_id'          => $qi->item_id,
                    'description'      => $qi->description,
                    'unit_id'          => $qi->unit_id,
                    'quantity'         => $qi->quantity,
                    'unit_price'       => $qi->unit_price,
                    'discount_percent' => $qi->discount_percent,
                    'discount_amount'  => $qi->discount_amount,
                    'total'            => $qi->total,
                ]);
            }
            $quote->update(['status' => 'accepted']);
        });

        return redirect()->route('sales_orders.index')->with('success', 'تم تحويل عرض السعر إلى أمر بيع');
    }

    public function print($id)
    {
        $quote = SalesQuotation::with(['customer','branch','items.item','items.unit'])
            ->where('com_code', $this->comCode())->findOrFail($id);
        return view('admin.sales.quotations.print', compact('quote'));
    }

    public function delete($id)
    {
        SalesQuotation::where('com_code', $this->comCode())->findOrFail($id)->delete();
        return redirect()->route('sales_quotations.index')->with('success', 'تم حذف عرض السعر');
    }
}
