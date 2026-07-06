<?php
namespace App\Http\Controllers\Admin\Quality;

use App\Http\Controllers\Controller;
use App\Models\{QualityChecklist, QualityChecklistItem};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

class QualityChecklistsController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function index()
    {
        $data = QualityChecklist::withCount('items')->where('com_code', $this->comCode())->orderBy('name')->get();
        return view('admin.quality.checklists.index', compact('data'));
    }

    public function create()
    {
        return view('admin.quality.checklists.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:150',
            'applies_to' => 'required|in:production,purchase,both',
            'items'      => 'required|array|min:1',
        ]);

        DB::transaction(function () use ($request) {
            $checklist = QualityChecklist::create([
                'com_code'   => $this->comCode(),
                'name'       => $request->name,
                'applies_to' => $request->applies_to,
                'is_active'  => (bool) $request->is_active,
            ]);

            foreach ($request->items as $criterion) {
                if (trim((string) $criterion) === '') continue;
                QualityChecklistItem::create(['checklist_id' => $checklist->id, 'criterion' => $criterion]);
            }
        });

        return redirect()->route('quality_checklists.index')->with('success', 'تم إنشاء قالب الفحص بنجاح');
    }

    public function show($id)
    {
        $checklist = QualityChecklist::with('items')->where('com_code', $this->comCode())->findOrFail($id);
        return view('admin.quality.checklists.show', compact('checklist'));
    }

    public function edit($id)
    {
        $checklist = QualityChecklist::with('items')->where('com_code', $this->comCode())->findOrFail($id);
        return view('admin.quality.checklists.edit', compact('checklist'));
    }

    public function update(Request $request, $id)
    {
        $checklist = QualityChecklist::where('com_code', $this->comCode())->findOrFail($id);
        $request->validate(['name' => 'required|string|max:150', 'applies_to' => 'required|in:production,purchase,both']);

        DB::transaction(function () use ($request, $checklist) {
            $checklist->update([
                'name'       => $request->name,
                'applies_to' => $request->applies_to,
                'is_active'  => (bool) $request->is_active,
            ]);

            $checklist->items()->delete();
            foreach ($request->items ?? [] as $criterion) {
                if (trim((string) $criterion) === '') continue;
                QualityChecklistItem::create(['checklist_id' => $checklist->id, 'criterion' => $criterion]);
            }
        });

        return redirect()->route('quality_checklists.index')->with('success', 'تم تعديل قالب الفحص');
    }

    public function delete($id)
    {
        $checklist = QualityChecklist::where('com_code', $this->comCode())->findOrFail($id);
        if (\App\Models\QualityInspection::where('checklist_id', $checklist->id)->exists()) {
            return back()->with('error', 'لا يمكن حذف قالب فحص تم استخدامه بالفعل - عطّله بدلاً من ذلك');
        }
        $checklist->delete();
        return redirect()->route('quality_checklists.index')->with('success', 'تم حذف قالب الفحص');
    }
}
