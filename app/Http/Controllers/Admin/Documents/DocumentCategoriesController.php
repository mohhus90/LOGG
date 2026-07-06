<?php
namespace App\Http\Controllers\Admin\Documents;

use App\Http\Controllers\Controller;
use App\Models\DocumentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentCategoriesController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function index()
    {
        $data = DocumentCategory::withCount('documents')->where('com_code', $this->comCode())->orderBy('name')->get();
        return view('admin.documents.categories.index', compact('data'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:150']);
        DocumentCategory::create([
            'com_code'  => $this->comCode(),
            'name'      => $request->name,
            'is_active' => true,
        ]);
        return redirect()->route('document_categories.index')->with('success', 'تم إضافة الفئة بنجاح');
    }

    public function update(Request $request, $id)
    {
        $category = DocumentCategory::where('com_code', $this->comCode())->findOrFail($id);
        $request->validate(['name' => 'required|string|max:150']);
        $category->update(['name' => $request->name, 'is_active' => (bool) $request->is_active]);
        return redirect()->route('document_categories.index')->with('success', 'تم تعديل الفئة');
    }

    public function delete($id)
    {
        $category = DocumentCategory::where('com_code', $this->comCode())->findOrFail($id);
        if ($category->documents()->exists()) {
            return back()->with('error', 'لا يمكن حذف فئة بها وثائق مسجلة');
        }
        $category->delete();
        return redirect()->route('document_categories.index')->with('success', 'تم حذف الفئة');
    }
}
