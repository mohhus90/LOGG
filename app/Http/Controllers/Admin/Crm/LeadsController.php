<?php
namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Models\{CrmLead, Customer, CrmActivity};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

class LeadsController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function index(Request $request)
    {
        $query = CrmLead::where('com_code', $this->comCode());
        if ($request->filled('status')) $query->where('status', $request->status);
        $data = $query->orderByDesc('id')->paginate(20);
        return view('admin.crm.leads.index', compact('data'));
    }

    public function create()
    {
        return view('admin.crm.leads.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:150']);

        CrmLead::create([
            'com_code'   => $this->comCode(),
            'name'       => $request->name,
            'phone'      => $request->phone,
            'email'      => $request->email,
            'source'     => $request->source,
            'status'     => $request->status ?? 'new',
            'notes'      => $request->notes,
            'created_by' => Auth::guard('admin')->id(),
        ]);

        return redirect()->route('crm_leads.index')->with('success', 'تم إضافة العميل المحتمل بنجاح');
    }

    public function show($id)
    {
        $lead = CrmLead::with(['opportunities', 'convertedCustomer'])->where('com_code', $this->comCode())->findOrFail($id);
        $activities = $lead->activities()->get();
        return view('admin.crm.leads.show', compact('lead', 'activities'));
    }

    public function edit($id)
    {
        $lead = CrmLead::where('com_code', $this->comCode())->findOrFail($id);
        return view('admin.crm.leads.edit', compact('lead'));
    }

    public function update(Request $request, $id)
    {
        $lead = CrmLead::where('com_code', $this->comCode())->findOrFail($id);
        $request->validate(['name' => 'required|string|max:150', 'status' => 'required|in:new,contacted,qualified,converted,lost']);

        $lead->update([
            'name'   => $request->name,
            'phone'  => $request->phone,
            'email'  => $request->email,
            'source' => $request->source,
            'status' => $request->status,
            'notes'  => $request->notes,
        ]);

        return redirect()->route('crm_leads.show', $id)->with('success', 'تم تعديل بيانات العميل المحتمل');
    }

    /** يحوّل العميل المحتمل إلى عميل فعلي في موديول المبيعات دون تكرار الإدخال */
    public function convertToCustomer($id)
    {
        $lead = CrmLead::where('com_code', $this->comCode())->findOrFail($id);
        if ($lead->status === 'converted') {
            return back()->with('error', 'تم تحويل هذا العميل المحتمل بالفعل');
        }

        $customer = DB::transaction(function () use ($lead) {
            $customer = Customer::create([
                'com_code'  => $this->comCode(),
                'name'      => $lead->name,
                'phone'     => $lead->phone,
                'email'     => $lead->email,
                'type'      => 'individual',
                'is_active' => true,
                'created_by'=> Auth::guard('admin')->id(),
            ]);
            $lead->update(['status' => 'converted', 'converted_customer_id' => $customer->id]);
            return $customer;
        });

        return redirect()->route('sales_customers.show', $customer->id)->with('success', 'تم تحويل العميل المحتمل إلى عميل بنجاح');
    }

    public function delete($id)
    {
        CrmLead::where('com_code', $this->comCode())->findOrFail($id)->delete();
        return redirect()->route('crm_leads.index')->with('success', 'تم حذف العميل المحتمل');
    }
}
