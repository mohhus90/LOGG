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

Route::get('/admin/dashboard/home',[AdminHomeController::class,'index'])->name('admin.dashboard.home')->middleware('auth:admin');

Route::get('admin/dashboard/login',[AdminLoginController::class,'login'])->name('admin.dashboard.login')->middleware('guest:admin');
Route::post('admin/dashboard/home',[AdminLoginController::class,'check'])->name('admin.dashboard.home');
Route::get('admin/dashboard/register',[AdminRegisterController::class,'register'])->name('admin.dashboard.register');
Route::post('admin/dashboard/store',[AdminRegisterController::class,'store'])->name('admin.dashboard.store');

Route::post('admin/dashboard/logout',[AdminLoginController::class,'logout'])->name('admin.dashboard.logout');