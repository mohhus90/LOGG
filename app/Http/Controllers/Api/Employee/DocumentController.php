<?php

namespace App\Http\Controllers\Api\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeeDocument;
use App\Models\EmployeeRequest;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $documents = EmployeeDocument::where('employee_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get(['id', 'doc_type', 'doc_original_name', 'created_at']);

        $documents->each(function (EmployeeDocument $doc) {
            $latest = $doc->latestAccessRequest();
            $doc->access_status = match (true) {
                $latest === null    => 'none',
                $latest->status===0 => 'pending',
                $latest->status===1 => 'approved',
                default             => 'none', // مرفوض/ملغي: يقدر يطلب تاني
            };
        });

        return response()->json($documents);
    }

    public function requestAccess(Request $request, int $id)
    {
        $employee = $request->user();
        $document = EmployeeDocument::where('employee_id', $employee->id)->findOrFail($id);

        if ($document->latestAccessRequest()?->status === 0) {
            return response()->json(['message' => 'يوجد طلب وصول قيد الانتظار لهذا المستند بالفعل'], 422);
        }

        $accessRequest = EmployeeRequest::create([
            'employee_id'  => $employee->id,
            'document_id'  => $document->id,
            'request_type' => 'document_download',
            'request_date' => now()->toDateString(),
            'start_date'   => now()->toDateString(),
            'days_count'   => 0,
            'reason'       => 'طلب الوصول لتنزيل مستند: ' . ($document->typeLabel ?? $document->doc_type),
            'status'       => 0,
            'com_code'     => $employee->com_code,
        ]);

        return response()->json($accessRequest, 201);
    }

    public function download(Request $request, int $id)
    {
        $document = EmployeeDocument::where('employee_id', $request->user()->id)->findOrFail($id);

        if (!$document->isApprovedForDownload()) {
            return response()->json(['message' => 'يجب طلب الوصول لهذا المستند والحصول على موافقة قبل التنزيل'], 403);
        }

        $path = public_path($document->doc_path);

        if (!file_exists($path)) {
            return response()->json(['message' => 'الملف غير موجود'], 404);
        }

        return response()->download($path, $document->doc_original_name);
    }
}
