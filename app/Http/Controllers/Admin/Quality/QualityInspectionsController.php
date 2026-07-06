<?php
namespace App\Http\Controllers\Admin\Quality;

use App\Http\Controllers\Controller;
use App\Models\{QualityInspection, QualityInspectionItem, QualityChecklist, ProductionOrder, PurchaseInvoice};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

class QualityInspectionsController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    private function nextInspectionNumber(): string
    {
        $last = QualityInspection::where('com_code', $this->comCode())->whereYear('created_at', now()->year)->max('inspection_number');
        $num  = $last ? ((int) substr($last, -4)) + 1 : 1;
        return 'QC-'.now()->year.'-'.str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    public function index(Request $request)
    {
        $query = QualityInspection::with('checklist')->where('com_code', $this->comCode());
        if ($request->filled('source_type')) $query->where('source_type', $request->source_type);
        if ($request->filled('overall_result')) $query->where('overall_result', $request->overall_result);
        $data = $query->orderByDesc('date')->paginate(20);
        return view('admin.quality.inspections.index', compact('data'));
    }

    public function create(Request $request)
    {
        $comCode    = $this->comCode();
        $checklists = QualityChecklist::where('com_code', $comCode)->where('is_active', true)->get();
        $productionOrders = ProductionOrder::with('item')->where('com_code', $comCode)
            ->whereIn('status', ['in_progress', 'completed'])->orderByDesc('id')->limit(50)->get();
        $purchaseInvoices = PurchaseInvoice::with('supplier')->where('com_code', $comCode)
            ->orderByDesc('date')->limit(50)->get();
        $nextNumber = $this->nextInspectionNumber();
        return view('admin.quality.inspections.create', compact('checklists', 'productionOrders', 'purchaseInvoices', 'nextNumber'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'checklist_id' => 'required|exists:quality_checklists,id',
            'source_type'  => 'required|in:production_order,purchase_invoice',
            'source_id'    => 'required|integer',
            'date'         => 'required|date',
            'items'        => 'required|array|min:1',
        ]);

        $checklist = QualityChecklist::where('com_code', $this->comCode())->findOrFail($request->checklist_id);

        $inspection = DB::transaction(function () use ($request, $checklist) {
            $results = collect($request->items)->pluck('result');
            $overall = $results->contains('fail') ? ($results->every(fn ($r) => $r === 'fail') ? 'fail' : 'conditional') : 'pass';

            $inspection = QualityInspection::create([
                'com_code'       => $this->comCode(),
                'inspection_number' => $this->nextInspectionNumber(),
                'checklist_id'   => $checklist->id,
                'source_type'    => $request->source_type,
                'source_id'      => $request->source_id,
                'inspector_id'   => Auth::guard('admin')->id(),
                'date'           => $request->date,
                'overall_result' => $overall,
                'notes'          => $request->notes,
                'created_by'     => Auth::guard('admin')->id(),
            ]);

            foreach ($request->items as $row) {
                if (empty($row['checklist_item_id'])) continue;
                QualityInspectionItem::create([
                    'inspection_id'      => $inspection->id,
                    'checklist_item_id'  => $row['checklist_item_id'],
                    'result'             => $row['result'] ?? 'na',
                    'notes'              => $row['notes'] ?? null,
                ]);
            }

            return $inspection;
        });

        return redirect()->route('quality_inspections.show', $inspection->id)->with('success', 'تم تسجيل الفحص بنجاح');
    }

    public function show($id)
    {
        $inspection = QualityInspection::with(['checklist', 'items.checklistItem', 'inspector'])
            ->where('com_code', $this->comCode())->findOrFail($id);
        return view('admin.quality.inspections.show', compact('inspection'));
    }

    public function delete($id)
    {
        QualityInspection::where('com_code', $this->comCode())->findOrFail($id)->delete();
        return redirect()->route('quality_inspections.index')->with('success', 'تم حذف الفحص');
    }
}
