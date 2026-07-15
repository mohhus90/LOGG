<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Branch Agent — يستقبل بيانات البصمة من الفروع البعيدة
Route::post('/fingerprint-agent/push', [\App\Http\Controllers\Api\BranchAgentController::class, 'push'])
    ->name('api.fingerprint_agent.push');

// Employee Self-Service — تطبيق الموظف (موبايل وويب)
use App\Http\Controllers\Api\Employee\AttendanceController as EmployeeAttendanceController;
use App\Http\Controllers\Api\Employee\AuthController as EmployeeAuthController;
use App\Http\Controllers\Api\Employee\DocumentController as EmployeeDocumentController;
use App\Http\Controllers\Api\Employee\LeaveRequestController;
use App\Http\Controllers\Api\Employee\LetterController;
use App\Http\Controllers\Api\Employee\PayslipController;
use App\Http\Controllers\Api\Employee\ResignationController;

Route::prefix('employee')->group(function () {
    Route::post('/login', [EmployeeAuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'employee.token'])->group(function () {
        Route::post('/logout', [EmployeeAuthController::class, 'logout']);
        Route::get('/me', fn (Request $request) => response()->json($request->user()));

        Route::get('/attendance/today', [EmployeeAttendanceController::class, 'today']);
        Route::get('/attendance/history', [EmployeeAttendanceController::class, 'history']);
        Route::post('/attendance/check-in', [EmployeeAttendanceController::class, 'checkIn']);
        Route::post('/attendance/check-out', [EmployeeAttendanceController::class, 'checkOut']);

        Route::get('/payslips', [PayslipController::class, 'index']);
        Route::get('/payslips/{id}', [PayslipController::class, 'show']);
        Route::get('/payslips/{id}/pdf', [PayslipController::class, 'pdf']);

        Route::get('/letters/salary-certificate/status', [LetterController::class, 'status']);
        Route::post('/letters/salary-certificate/request-access', [LetterController::class, 'requestAccess']);
        Route::get('/letters/salary-certificate', [LetterController::class, 'salaryCertificate']);

        Route::get('/documents', [EmployeeDocumentController::class, 'index']);
        Route::post('/documents/{id}/request-access', [EmployeeDocumentController::class, 'requestAccess']);
        Route::get('/documents/{id}/download', [EmployeeDocumentController::class, 'download']);

        Route::get('/leave-requests/balance', [LeaveRequestController::class, 'balance']);
        Route::get('/leave-requests', [LeaveRequestController::class, 'index']);
        Route::post('/leave-requests', [LeaveRequestController::class, 'store']);
        Route::post('/leave-requests/{id}/cancel', [LeaveRequestController::class, 'cancel']);

        Route::post('/resignation', [ResignationController::class, 'store']);
        Route::get('/resignation', [ResignationController::class, 'show']);
    });
});
