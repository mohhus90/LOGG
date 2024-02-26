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
use App\Http\Controllers\Admin\DepartmentsController;
use App\Http\Controllers\Admin\Jobs_categoriesController;
use App\Http\Controllers\Admin\EmployeesConroller;


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
define('paginate_counter',11);
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
    // Route::post('shifts/ajaxsearch',[Shifts_typeController::class,'ajaxsearch'])->name('shifts.ajaxsearch');
    Route::match(['get', 'post'], 'shifts/ajaxsearch', [Shifts_typeController::class, 'ajaxsearch'])->name('shifts.ajaxsearch');
    // الوظائف
    Route::get('jobs_categories',[Jobs_categoriesController::class,'index'])->name('jobs_categories.index')->middleware('auth:admin');
    Route::get('jobs_categories/{id}/edit',[Jobs_categoriesController::class,'edit'])->name('jobs_categories.edit');
    Route::get('jobs_categories/create',[Jobs_categoriesController::class,'create'])->name('jobs_categories.create');
    Route::post('jobs_categories/store',[Jobs_categoriesController::class,'store'])->name('jobs_categories.store');
    Route::post('jobs_categories/update/{id}',[Jobs_categoriesController::class,'update'])->name('jobs_categories.update');
    Route::get('jobs_categories/delete/{id}',[Jobs_categoriesController::class,'delete'])->name('jobs_categories.delete');
    // Route::post('jobs_categories/ajaxsearch',[Jobs_categoriesController::class,'ajaxsearch'])->name('jobs_categories.ajaxsearch');
    Route::match(['get', 'post'], 'jobs_categories/ajaxsearch', [Jobs_categoriesController::class, 'ajaxsearch'])->name('jobs_categories.ajaxsearch');
    // الادارات
    Route::get('departs',[DepartmentsController::class,'index'])->name('departs.index')->middleware('auth:admin');
    Route::get('departs/{id}/edit',[DepartmentsController::class,'edit'])->name('departs.edit');
    Route::get('departs/create',[DepartmentsController::class,'create'])->name('departs.create');
    Route::post('departs/store',[DepartmentsController::class,'store'])->name('departs.store');
    Route::post('departs/update/{id}',[DepartmentsController::class,'update'])->name('departs.update');
    Route::get('departs/delete/{id}',[DepartmentsController::class,'delete'])->name('departs.delete');
     // انواع الوظائف
     Route::get('jobs_categores',[Jobs_categoriesController::class,'index'])->name('jobs_categores.index')->middleware('auth:admin');
     Route::get('jobs_categores/{id}/edit',[Jobs_categoriesController::class,'edit'])->name('jobs_categores.edit');
     Route::get('jobs_categores/create',[Jobs_categoriesController::class,'create'])->name('jobs_categores.create');
     Route::post('jobs_categores/store',[Jobs_categoriesController::class,'store'])->name('jobs_categores.store');
     Route::post('jobs_categores/update/{id}',[Jobs_categoriesController::class,'update'])->name('jobs_categores.update');
     Route::get('jobs_categores/delete/{id}',[Jobs_categoriesController::class,'delete'])->name('jobs_categores.delete');
     Route::match(['get', 'post'], 'jobs_categores/ajaxsearch', [Jobs_categoriesController::class, 'ajaxsearch'])->name('jobs_categores.ajaxsearch');
    // الموظفين
    Route::get('employees',[EmployeesConroller::class,'index'])->name('employees.index')->middleware('auth:admin');
    Route::get('employees/{id}/edit',[EmployeesConroller::class,'edit'])->name('employees.edit');
    Route::get('employees/create',[EmployeesConroller::class,'create'])->name('employees.create');
    Route::post('employees/store',[EmployeesConroller::class,'store'])->name('employees.store');
    Route::post('employees/update/{id}',[EmployeesConroller::class,'update'])->name('employees.update');
    Route::get('employees/delete/{id}',[EmployeesConroller::class,'delete'])->name('employees.delete');
    Route::match(['get', 'post'], 'employees/ajaxsearch', [EmployeesConroller::class, 'ajaxsearch'])->name('employees.ajaxsearch');
     // بداية السنة المالية
    Route::get('finance_calender/delete/{id}',[Finance_calendersController::class,'delete'])->name('finance_calender.delete');
    Route::post('finance_calender/show_year_monthes',[Finance_calendersController::class,'show_year_monthes'])->name('finance_calender.show_year_monthes');
    Route::get('finance_calender/updatee/{id}',[Finance_calendersController::class,'updatee'])->name('finance_calender.updatee');
    
    Route::resource('finance_calender',Finance_calendersController::class)->middleware('auth:admin');
    


});
