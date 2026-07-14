<?php

namespace App\Http\Controllers\Api\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeeDocument;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $documents = EmployeeDocument::where('employee_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get(['id', 'doc_type', 'doc_original_name', 'created_at']);

        return response()->json($documents);
    }

    public function download(Request $request, int $id)
    {
        $document = EmployeeDocument::where('employee_id', $request->user()->id)->findOrFail($id);

        $path = public_path($document->doc_path);

        if (!file_exists($path)) {
            return response()->json(['message' => 'الملف غير موجود'], 404);
        }

        return response()->download($path, $document->doc_original_name);
    }
}
