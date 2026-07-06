<?php
namespace App\Http\Controllers\Admin\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuppliersController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function index(Request $request)
    {
        $query = Supplier::where('com_code', $this->comCode());
        if ($request->filled('search'))    $query->where('name', 'like', '%'.$request->search.'%');
        if ($request->filled('type'))      $query->where('type', $request->type);
        if ($request->filled('is_active')) $query->where('is_active', $request->is_active);
        $data = $query->orderBy('name')->paginate(20);
        return view('admin.purchasing.suppliers.index', compact('data'));
    }

    public function create() { return view('admin.purchasing.suppliers.create'); }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:200']);
        Supplier::create(array_merge(
            $request->only(['code','name','name_en','type','phone','phone2','email',
                            'address','city','governorate','tax_number','commercial_register',
                            'payment_terms','opening_balance','notes']),
            [
                'com_code'   => $this->comCode(),
                'is_active'  => $request->boolean('is_active', true),
                'created_by' => Auth::guard('admin')->id(),
            ]
        ));
        return redirect()->route('suppliers.index')->with('success', 'تم إضافة المورد بنجاح');
    }

    public function show($id)
    {
        $supplier = Supplier::where('com_code', $this->comCode())
            ->with(['invoices' => fn($q) => $q->orderByDesc('date')->limit(10),
                    'payments' => fn($q) => $q->orderByDesc('date')->limit(10)])
            ->findOrFail($id);
        $totalInvoiced = $supplier->invoices()->sum('total');
        $totalPaid     = $supplier->payments()->sum('amount');
        $totalDebt     = $supplier->invoices()->whereIn('payment_status', ['unpaid','partial'])->sum('remaining_amount');
        return view('admin.purchasing.suppliers.show', compact('supplier','totalInvoiced','totalPaid','totalDebt'));
    }

    public function edit($id)
    {
        $supplier = Supplier::where('com_code', $this->comCode())->findOrFail($id);
        return view('admin.purchasing.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:200']);
        $supplier = Supplier::where('com_code', $this->comCode())->findOrFail($id);
        $supplier->update(array_merge(
            $request->only(['code','name','name_en','type','phone','phone2','email',
                            'address','city','governorate','tax_number','commercial_register',
                            'payment_terms','opening_balance','notes']),
            ['is_active' => $request->boolean('is_active', true)]
        ));
        return redirect()->route('suppliers.index')->with('success', 'تم تعديل المورد بنجاح');
    }

    public function delete($id)
    {
        Supplier::where('com_code', $this->comCode())->findOrFail($id)->delete();
        return redirect()->route('suppliers.index')->with('success', 'تم حذف المورد');
    }
}
