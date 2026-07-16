<?php

namespace App\Http\Controllers\Api\Employee;

use App\Http\Controllers\Controller;
use App\Models\Admin_panel_setting;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * قائمة الشركات لعرضها كاختيار (اسم بدل كود رقمي) في شاشة الدخول.
     */
    public function companies()
    {
        $companies = Admin_panel_setting::where('saysem_status', 1)
            ->orderBy('com_name')
            ->get(['com_code', 'com_name'])
            ->map(fn ($c) => ['com_code' => $c->com_code, 'name' => $c->com_name]);

        return response()->json(['data' => $companies]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login_username' => 'required|string',
            'login_password' => 'required|string',
            'com_code'       => 'required|integer',
            'device_name'    => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $employee = Employee::where('com_code', $request->com_code)
            ->where('login_username', $request->login_username)
            ->where('functional_status', 1)
            ->first();

        if (!$employee || !$employee->login_password_hash
            || !Hash::check($request->login_password, $employee->login_password_hash)) {
            return response()->json(['message' => 'بيانات الدخول غير صحيحة'], 401);
        }

        $token = $employee->createToken($request->device_name ?: 'employee-app')->plainTextToken;

        return response()->json([
            'token'    => $token,
            'employee' => [
                'id'                         => $employee->id,
                'name'                       => $employee->employee_name_A,
                'photo'                      => $employee->emp_photo,
                'department'                 => $employee->department?->dep_name,
                'job'                        => $employee->jobs_categories?->job_name,
                'location_tracking_enabled'  => (bool) $employee->location_tracking_enabled,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'تم تسجيل الخروج']);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password'      => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $employee = $request->user();

        if (!$employee->login_password_hash
            || !Hash::check($request->current_password, $employee->login_password_hash)) {
            return response()->json(['message' => 'كلمة المرور الحالية غير صحيحة'], 422);
        }

        $employee->login_password = $request->new_password;
        $employee->save();

        return response()->json(['message' => 'تم تغيير كلمة المرور بنجاح']);
    }
}
