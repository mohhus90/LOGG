<?php
namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Branche;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomersController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function index(Request $request)
    {
        $query = Customer::where('com_code', $this->comCode());
        if ($request->filled('search'))    $query->where('name', 'like', '%'.$request->search.'%');
        if ($request->filled('type'))      $query->where('type', $request->type);
        if ($request->filled('is_active')) $query->where('is_active', $request->is_active);
        $data = $query->orderBy('name')->paginate(20);
        return view('admin.sales.customers.index', compact('data'));
    }

    public function create() { return view('admin.sales.customers.create'); }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:200']);
        Customer::create(array_merge(
            $request->only(['code','name','name_en','type','phone','phone2','email',
                            'address','city','governorate','tax_number','commercial_register',
                            'credit_limit','payment_terms','opening_balance','notes']),
            [
                'com_code'   => $this->comCode(),
                'is_active'  => $request->boolean('is_active', true),
                'created_by' => Auth::guard('admin')->id(),
            ]
        ));
        return redirect()->route('sales_customers.index')->with('success', 'تم إضافة العميل بنجاح');
    }

    public function show($id)
    {
        $customer = Customer::where('com_code', $this->comCode())
            ->with(['invoices' => fn($q) => $q->orderByDesc('date')->limit(10),
                    'payments' => fn($q) => $q->orderByDesc('date')->limit(10)])
            ->findOrFail($id);
        $totalInvoiced = $customer->invoices()->sum('total');
        $totalPaid     = $customer->payments()->sum('amount');
        $totalDebt     = $customer->invoices()->whereIn('payment_status', ['unpaid','partial'])->sum('remaining_amount');
        return view('admin.sales.customers.show', compact('customer','totalInvoiced','totalPaid','totalDebt'));
    }

    public function edit($id)
    {
        $customer = Customer::where('com_code', $this->comCode())->findOrFail($id);
        return view('admin.sales.customers.edit', compact('customer'));
    }

    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:200']);
        $customer = Customer::where('com_code', $this->comCode())->findOrFail($id);
        $customer->update(array_merge(
            $request->only(['code','name','name_en','type','phone','phone2','email',
                            'address','city','governorate','tax_number','commercial_register',
                            'credit_limit','payment_terms','opening_balance','notes']),
            ['is_active' => $request->boolean('is_active', true)]
        ));
        return redirect()->route('sales_customers.index')->with('success', 'تم تعديل العميل بنجاح');
    }

    public function delete($id)
    {
        Customer::where('com_code', $this->comCode())->findOrFail($id)->delete();
        return redirect()->route('sales_customers.index')->with('success', 'تم حذف العميل');
    }
}
