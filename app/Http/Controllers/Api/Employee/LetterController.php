<?php

namespace App\Http\Controllers\Api\Employee;

use App\Http\Controllers\Controller;
use App\Models\Admin_panel_setting;
use App\Models\EmployeeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class LetterController extends Controller
{
    private function latestRequest(int $employeeId): ?EmployeeRequest
    {
        return EmployeeRequest::where('employee_id', $employeeId)
            ->where('request_type', 'salary_certificate')
            ->orderByDesc('created_at')
            ->first();
    }

    public function status(Request $request)
    {
        $latest = $this->latestRequest($request->user()->id);

        $status = match (true) {
            !$latest           => 'none',
            $latest->status===0 => 'pending',
            $latest->status===1 => 'approved',
            default             => 'none',
        };

        return response()->json(['access_status' => $status, 'reason' => $latest?->reason]);
    }

    public function requestAccess(Request $request)
    {
        $employee = $request->user();

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        if ($this->latestRequest($employee->id)?->status === 0) {
            return response()->json(['message' => 'يوجد طلب شهادة راتب قيد الانتظار بالفعل'], 422);
        }

        $accessRequest = EmployeeRequest::create([
            'employee_id'  => $employee->id,
            'request_type' => 'salary_certificate',
            'request_date' => now()->toDateString(),
            'start_date'   => now()->toDateString(),
            'days_count'   => 0,
            'reason'       => $request->reason,
            'status'       => 0,
            'com_code'     => $employee->com_code,
        ]);

        return response()->json($accessRequest, 201);
    }

    public function salaryCertificate(Request $request)
    {
        $employee = $request->user();

        if ($this->latestRequest($employee->id)?->status !== 1) {
            return response()->json(['message' => 'يجب طلب شهادة الراتب وذكر السبب والحصول على موافقة قبل التنزيل'], 403);
        }

        $company = Admin_panel_setting::where('com_code', $employee->com_code)->first();

        $pdf = Pdf::loadView('pdf.salary_certificate', compact('employee', 'company'));

        return $pdf->download('salary-certificate-' . $employee->id . '.pdf');
    }
}
