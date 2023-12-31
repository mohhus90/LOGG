<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\AdminHomeController;
use App\Http\Controllers\Admin\auth\AdminLoginController;
use App\Http\Controllers\Admin\auth\AdminRegisterController;
use App\Http\Controllers\AdminPanelSettingController;
use App\Http\Controllers\Admin\branchesController;
use App\Http\Controllers\Admin\Finance_calendersController;
use App\Http\Controllers\Admin\Shifts_typeController;
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
define('paginate_counter',1);
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
    Route::get('branches/{id}/edit',[branchesController::class,'edit'])->name('branches.edit');
    Route::get('branches/create',[branchesController::class,'create'])->name('branches.create');
    Route::post('branches/store',[branchesController::class,'store'])->name('branches.store');
    Route::post('branches/update/{id}',[branchesController::class,'update'])->name('branches.update');
    Route::get('branches/delete/{id}',[branchesController::class,'delete'])->name('branches.delete');
    // الشيفتات
    Route::get('shifts',[Shifts_typeController::class,'index'])->name('shifts.index')->middleware('auth:admin');
    Route::get('shifts/{id}/edit',[Shifts_typeController::class,'edit'])->name('shifts.edit');
    Route::get('shifts/create',[Shifts_typeController::class,'create'])->name('shifts.create');
    Route::post('shifts/store',[Shifts_typeController::class,'store'])->name('shifts.store');
    Route::post('shifts/update/{id}',[Shifts_typeController::class,'update'])->name('shifts.update');
    Route::get('shifts/delete/{id}',[Shifts_typeController::class,'delete'])->name('shifts.delete');
    Route::post('shifts/ajaxsearch',[Shifts_typeController::class,'ajaxsearch'])->name('shifts.ajaxsearch');

    // بداية السنة المالية
    Route::get('finance_calender/delete/{id}',[Finance_calendersController::class,'delete'])->name('finance_calender.delete');
    Route::post('finance_calender/show_year_monthes',[Finance_calendersController::class,'show_year_monthes'])->name('finance_calender.show_year_monthes');
    Route::get('finance_calender/updatee/{id}',[Finance_calendersController::class,'updatee'])->name('finance_calender.updatee');
    
    Route::resource('finance_calender',Finance_calendersController::class)->middleware('auth:admin');
    


});
