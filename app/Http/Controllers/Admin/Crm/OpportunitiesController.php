<?php
namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Models\{CrmOpportunity, CrmLead, Customer};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OpportunitiesController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    /** لوحة Kanban بسيطة: الفرص مجمّعة حسب المرحلة */
    public function index()
    {
        $comCode = $this->comCode();
        $stages  = CrmOpportunity::stageOptions();
        $board   = [];
        foreach (array_keys($stages) as $stage) {
            $board[$stage] = CrmOpportunity::with(['lead', 'customer'])->where('com_code', $comCode)->where('stage', $stage)->orderByDesc('id')->get();
        }
        return view('admin.crm.opportunities.index', compact('board', 'stages'));
    }

    public function create(Request $request)
    {
        $comCode   = $this->comCode();
        $leads     = CrmLead::where('com_code', $comCode)->where('status', '!=', 'converted')->orderBy('name')->get();
        $customers = Customer::where('com_code', $comCode)->where('is_active', true)->orderBy('name')->get();
        $selectedLead = $request->filled('lead_id') ? CrmLead::where('com_code', $comCode)->find($request->lead_id) : null;
        return view('admin.crm.opportunities.create', compact('leads', 'customers', 'selectedLead'));
    }

    public function store(Request $request)
    {
        $request->validate(['title' => 'required|string|max:150']);

        CrmOpportunity::create([
            'com_code'             => $this->comCode(),
            'title'                => $request->title,
            'lead_id'              => $request->lead_id ?: null,
            'customer_id'          => $request->customer_id ?: null,
            'stage'                => $request->stage ?? 'prospecting',
            'value'                => $request->value ?? 0,
            'expected_close_date'  => $request->expected_close_date,
            'notes'                => $request->notes,
            'created_by'           => Auth::guard('admin')->id(),
        ]);

        return redirect()->route('crm_opportunities.index')->with('success', 'تم إضافة الفرصة البيعية بنجاح');
    }

    public function show($id)
    {
        $opportunity = CrmOpportunity::with(['lead', 'customer'])->where('com_code', $this->comCode())->findOrFail($id);
        $activities  = $opportunity->activities()->get();
        return view('admin.crm.opportunities.show', compact('opportunity', 'activities'));
    }

    public function updateStage(Request $request, $id)
    {
        $opportunity = CrmOpportunity::where('com_code', $this->comCode())->findOrFail($id);
        $request->validate(['stage' => 'required|in:prospecting,proposal,negotiation,won,lost']);
        $opportunity->update(['stage' => $request->stage]);
        return back()->with('success', 'تم تحديث مرحلة الفرصة');
    }

    public function delete($id)
    {
        CrmOpportunity::where('com_code', $this->comCode())->findOrFail($id)->delete();
        return redirect()->route('crm_opportunities.index')->with('success', 'تم حذف الفرصة البيعية');
    }
}
