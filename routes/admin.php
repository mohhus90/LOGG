<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\AdminHomeController;
use App\Http\Controllers\Admin\auth\AdminLoginController;
use App\Http\Controllers\Admin\auth\AdminRegisterController;
use App\Http\Controllers\AdminPanelSettingController;
use App\Http\Controllers\Admin\branchesController;
use App\Http\Controllers\Admin\Finance_calendersController;
use App\Models\Admin;

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
Route::group(['prefix'=>'admin/dashboard',],function () {
    Route::get('/home',[AdminHomeController::class,'index'])->name('admin.dashboard.home')->middleware('auth:admin');
    Route::get('login',[AdminLoginController::class,'login'])->name('admin.dashboard.login')->middleware('guest:admin');
    Route::post('home',[AdminLoginController::class,'check'])->name('admin.dashboard.home');
    Route::get('register',[AdminRegisterController::class,'register'])->name('admin.dashboard.register');
    Route::post('store',[AdminRegisterController::class,'store'])->name('admin.dashboard.store');
    Route::post('logout',[AdminLoginController::class,'logout'])->name('admin.dashboard.logout');
    // الضبط العام
    Route::get('generalsetting',[AdminPanelSettingController::class,'index'])->name('generalsetting.index')->middleware('auth:admin');
    Route::get('generalsetting/edit',[AdminPanelSettingController::class,'edit'])->name('generalsetting.edit');
    Route::get('generalsetting/update',[AdminPanelSettingController::class,'update'])->name('generalsetting.update');
    // الفروع
    Route::get('branches',[branchesController::class,'index'])->name('branches.index')->middleware('auth:admin');
    Route::get('branches/edit',[branchesController::class,'edit'])->name('branches.edit');
    Route::get('branches/update',[branchesController::class,'update'])->name('branches.update');
    // بداية السنة المالية
    Route::resource('finance_calender',Finance_calendersController::class);


});
