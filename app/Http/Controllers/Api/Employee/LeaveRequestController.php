<?php

namespace App\Http\Controllers\Api\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeeRequest;
use App\Models\EmployeeVacationBalance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LeaveRequestController extends Controller
{
    private const VACATION_TYPES = ['annual_vacation', 'casual_vacation'];
    private const PERMISSION_TYPES = ['late_permission', 'early_leave', 'mission'];

    public function balance(Request $request)
    {
        $employee = $request->user();

        $balance = EmployeeVacationBalance::where('employee_id', $employee->id)
            ->where('year', now()->year)
            ->first();

        $pendingRequests = EmployeeRequest::where('employee_id', $employee->id)
            ->where('status', 0)
            ->count();

        $approvedRequests = EmployeeRequest::where('employee_id', $employee->id)
            ->where('status', 1)
            ->whereMonth('start_date', now()->month)
            ->count();

        return response()->json([
            'balance'           => $balance,
            'pending_requests'  => $pendingRequests,
            'approved_requests' => $approvedRequests,
        ]);
    }

    public function index(Request $request)
    {
        $requests = EmployeeRequest::where('employee_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(30);

        return response()->json($requests);
    }

    public function store(Request $request)
    {
        $employee = $request->user();

        $validator = Validator::make($request->all(), [
            'request_type' => 'required|in:' . implode(',', [...self::VACATION_TYPES, ...self::PERMISSION_TYPES]),
            'start_date'   => 'required|date',
            'end_date'     => 'required_if:request_type,annual_vacation,casual_vacation|nullable|date|after_or_equal:start_date',
            'time_from'    => 'required_if:request_type,late_permission,early_leave|nullable|date_format:H:i',
            'time_to'      => 'required_if:request_type,late_permission,early_leave|nullable|date_format:H:i',
            'reason'       => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $isVacation = in_array($request->request_type, self::VACATION_TYPES);
        $daysCount  = 1;

        if ($isVacation) {
            $daysCount = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date)) + 1;

            $balance = EmployeeVacationBalance::where('employee_id', $employee->id)
                ->where('year', Carbon::parse($request->start_date)->year)
                ->first();

            $remaining = $request->request_type === 'annual_vacation'
                ? ($balance->annual_remaining ?? 0)
                : ($balance->casual_remaining ?? 0);

            if ($remaining < $daysCount) {
                return response()->json(['message' => 'رصيد الإجازة غير كافٍ لعدد الأيام المطلوب'], 422);
            }
        }

        $leaveRequest = EmployeeRequest::create([
            'employee_id'  => $employee->id,
            'request_type' => $request->request_type,
            'request_date' => now()->toDateString(),
            'start_date'   => $request->start_date,
            'end_date'     => $request->end_date,
            'time_from'    => $request->time_from,
            'time_to'      => $request->time_to,
            'days_count'   => $daysCount,
            'reason'       => $request->reason,
            'status'       => 0,
            'com_code'     => $employee->com_code,
        ]);

        return response()->json($leaveRequest, 201);
    }

    public function cancel(Request $request, int $id)
    {
        $leaveRequest = EmployeeRequest::where('employee_id', $request->user()->id)->findOrFail($id);

        if ($leaveRequest->status !== 0) {
            return response()->json(['message' => 'لا يمكن إلغاء طلب تمت معالجته مسبقاً'], 422);
        }

        $leaveRequest->update(['status' => 3]);

        return response()->json(['message' => 'تم إلغاء الطلب']);
    }
}
