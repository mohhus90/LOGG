<?php

namespace App\Http\Controllers\Api\Employee;

use App\Http\Controllers\Controller;
use App\Models\Admin_panel_setting;
use App\Models\Attendance;
use App\Models\Employee;
use App\Services\AttendanceCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    private AttendanceCalculationService $calc;

    public function __construct(AttendanceCalculationService $calc)
    {
        $this->calc = $calc;
    }

    public function today(Request $request)
    {
        $employee = $request->user();

        $attendance = Attendance::with(['shift'])
            ->where('employee_id', $employee->id)
            ->where('attendance_date', now()->toDateString())
            ->first();

        return response()->json(['attendance' => $attendance]);
    }

    public function history(Request $request)
    {
        $employee = $request->user();

        $query = Attendance::with(['shift'])->where('employee_id', $employee->id);

        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('attendance_date', $request->month)
                  ->whereYear('attendance_date', $request->year);
        }

        $data = $query->orderByDesc('attendance_date')->paginate(30);

        return response()->json($data);
    }

    public function checkIn(Request $request)
    {
        $employee = $request->user();

        $error = $this->validateGps($request, $employee);
        if ($error) return $error;

        $today = now()->toDateString();

        $attendance = Attendance::firstOrNew([
            'employee_id'     => $employee->id,
            'attendance_date' => $today,
        ]);

        if ($attendance->exists && $attendance->check_in_time) {
            return response()->json(['message' => 'تم تسجيل الحضور مسبقاً اليوم'], 422);
        }

        $geofenceError = $this->checkGeofence($request, $employee);
        if ($geofenceError) return $geofenceError;

        $attendance->shift_id        = $employee->shifts_types_id;
        $attendance->check_in_time   = now()->format('H:i:s');
        $attendance->check_in_lat    = $request->latitude;
        $attendance->check_in_lng    = $request->longitude;
        $attendance->source          = 'mobile_app';
        $attendance->device_verified = (bool) $request->boolean('device_verified');
        $attendance->status          = 1;
        $attendance->com_code        = $employee->com_code;
        $attendance->save();

        return response()->json(['message' => 'تم تسجيل الحضور', 'attendance' => $attendance]);
    }

    public function checkOut(Request $request)
    {
        $employee = $request->user();

        $error = $this->validateGps($request, $employee);
        if ($error) return $error;

        $today = now()->toDateString();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('attendance_date', $today)
            ->first();

        if (!$attendance || !$attendance->check_in_time) {
            return response()->json(['message' => 'لم يتم تسجيل حضور اليوم بعد'], 422);
        }

        if ($attendance->check_out_time) {
            return response()->json(['message' => 'تم تسجيل الانصراف مسبقاً اليوم'], 422);
        }

        $geofenceError = $this->checkGeofence($request, $employee);
        if ($geofenceError) return $geofenceError;

        $attendance->check_out_time  = now()->format('H:i:s');
        $attendance->check_out_lat   = $request->latitude;
        $attendance->check_out_lng   = $request->longitude;
        $attendance->device_verified = $attendance->device_verified || (bool) $request->boolean('device_verified');

        $settings = Admin_panel_setting::where('com_code', $employee->com_code)->first();
        if ($settings) {
            $this->calc->apply($attendance, $employee, $settings, $today);
        }

        $attendance->save();

        return response()->json(['message' => 'تم تسجيل الانصراف', 'attendance' => $attendance]);
    }

    private function validateGps(Request $request, Employee $employee)
    {
        $rules = ['device_verified' => 'nullable|boolean'];

        if ($employee->location_tracking_enabled) {
            $rules['latitude']  = 'required|numeric|between:-90,90';
            $rules['longitude'] = 'required|numeric|between:-180,180';
        } else {
            $rules['latitude']  = 'nullable|numeric|between:-90,90';
            $rules['longitude'] = 'nullable|numeric|between:-180,180';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        return null;
    }

    private function checkGeofence(Request $request, Employee $employee)
    {
        if (!$request->filled('latitude') || !$request->filled('longitude')) {
            return null;
        }

        $branch = $employee->branches;
        if (!$branch || !$branch->latitude || !$branch->longitude || !$branch->geofence_radius_m) {
            return null;
        }

        $distance = $this->haversineMeters(
            (float) $request->latitude,
            (float) $request->longitude,
            (float) $branch->latitude,
            (float) $branch->longitude
        );

        if ($distance > $branch->geofence_radius_m) {
            return response()->json([
                'message' => 'أنت خارج نطاق الفرع المسموح به لتسجيل الحضور',
            ], 422);
        }

        return null;
    }

    private function haversineMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
