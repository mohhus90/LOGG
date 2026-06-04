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
