<?php

namespace App\Http\Controllers\Api\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ResignationController extends Controller
{
    public function store(Request $request)
    {
        $employee = $request->user();

        $validator = Validator::make($request->all(), [
            'last_working_date' => 'required|date|after_or_equal:today',
            'reason'            => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $existing = EmployeeRequest::where('employee_id', $employee->id)
            ->where('request_type', 'resignation')
            ->where('status', 0)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'لديك طلب استقالة قيد الانتظار بالفعل'], 422);
        }

        $resignation = EmployeeRequest::create([
            'employee_id'  => $employee->id,
            'request_type' => 'resignation',
            'request_date' => now()->toDateString(),
            'start_date'   => $request->last_working_date,
            'days_count'   => 1,
            'reason'       => $request->reason,
            'status'       => 0,
            'com_code'     => $employee->com_code,
        ]);

        return response()->json($resignation, 201);
    }

    public function show(Request $request)
    {
        $resignation = EmployeeRequest::where('employee_id', $request->user()->id)
            ->where('request_type', 'resignation')
            ->orderByDesc('created_at')
            ->first();

        return response()->json($resignation);
    }
}
