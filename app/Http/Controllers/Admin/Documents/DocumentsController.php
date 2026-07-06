<?php
namespace App\Http\Controllers\Admin\Documents;

use App\Http\Controllers\Controller;
use App\Models\{Document, DocumentCategory};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentsController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function index(Request $request)
    {
        $query = Document::with('category')->where('com_code', $this->comCode());
        if ($request->filled('category_id')) $query->where('category_id', $request->category_id);
        if ($request->filled('status'))      $query->where('status', $request->status);
        $data       = $query->orderByDesc('id')->paginate(20);
        $categories = DocumentCategory::where('com_code', $this->comCode())->where('is_active', true)->get();
        return view('admin.documents.documents.index', compact('data', 'categories'));
    }

    public function create()
    {
        $categories = DocumentCategory::where('com_code', $this->comCode())->where('is_active', true)->get();
        return view('admin.documents.documents.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:200',
            'file'  => ['required', 'file', 'max:20480', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx'],
        ], [
            'file.mimes' => 'يجب أن يكون الملف من نوع PDF، صورة، Word أو Excel',
            'file.max'   => 'حجم الملف يجب ألا يتجاوز 20 ميجابايت',
        ]);

        $comCode  = $this->comCode();
        $file     = $request->file('file');
        $origName = $file->getClientOriginalName();
        $ext      = $file->getClientOriginalExtension();
        $fileName = 'doc_'.$comCode.'_'.time().'_'.uniqid().'.'.$ext;
        $destPath = public_path('assets/admin/documents');

        if (!is_dir($destPath)) mkdir($destPath, 0755, true);
        $file->move($destPath, $fileName);

        Document::create([
            'com_code'            => $comCode,
            'category_id'         => $request->category_id ?: null,
            'title'               => $request->title,
            'file_path'           => 'assets/admin/documents/'.$fileName,
            'file_original_name'  => $origName,
            'linked_type'         => $request->linked_type ?: null,
            'linked_id'           => $request->linked_id ?: null,
            'version'             => 1,
            'status'              => 'draft',
            'uploaded_by'         => Auth::guard('admin')->id(),
        ]);

        return redirect()->route('documents.index')->with('success', 'تم رفع الوثيقة بنجاح');
    }

    public function show($id)
    {
        $document = Document::with(['category', 'uploadedBy', 'approver'])->where('com_code', $this->comCode())->findOrFail($id);
        return view('admin.documents.documents.show', compact('document'));
    }

    public function download($id)
    {
        $document = Document::where('com_code', $this->comCode())->findOrFail($id);
        $path = public_path($document->file_path);
        if (!file_exists($path)) {
            return back()->with('error', 'الملف غير موجود');
        }
        return response()->download($path, $document->file_original_name);
    }

    public function approve($id)
    {
        $document = Document::where('com_code', $this->comCode())->findOrFail($id);
        $document->update(['status' => 'approved', 'approved_by' => Auth::guard('admin')->id(), 'approved_at' => now()]);
        return back()->with('success', 'تم اعتماد الوثيقة');
    }

    public function reject($id)
    {
        $document = Document::where('com_code', $this->comCode())->findOrFail($id);
        $document->update(['status' => 'rejected', 'approved_by' => Auth::guard('admin')->id(), 'approved_at' => now()]);
        return back()->with('success', 'تم رفض الوثيقة');
    }

    public function delete($id)
    {
        $document = Document::where('com_code', $this->comCode())->findOrFail($id);
        $path = public_path($document->file_path);
        if (file_exists($path)) @unlink($path);
        $document->delete();
        return redirect()->route('documents.index')->with('success', 'تم حذف الوثيقة');
    }
}
