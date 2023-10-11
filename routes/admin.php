<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\AdminHomeController;
use App\Http\Controllers\Admin\auth\AdminLoginController;
use App\Http\Controllers\Admin\auth\AdminRegisterController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/admin/dashboard/home',[AdminHomeController::class,'index'])->name('admin.dashboard.home');

Route::get('admin/dashboard/login',[AdminLoginController::class,'login'])->name('admin.dashboard.login');
Route::post('admin/dashboard/check',[AdminCheckController::class,'check'])->name('admin.dashboard.check');
Route::get('admin/dashboard/register',[AdminRegisterController::class,'register'])->name('admin.dashboard.register');
Route::post('admin/dashboard/store',[AdminRegisterController::class,'store'])->name('admin.dashboard.store');
