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
use App\Http\Controllers\Admin\Main_vacations_balanceController;
use App\Http\Controllers\Admin\AdminPermissionsController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\AdvancesController;
use App\Http\Controllers\Admin\CommissionsController;
use App\Http\Controllers\Admin\DeductionsController;
use App\Http\Controllers\Admin\PayrollController;

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

define('paginate_counter', 20);
Route::group(['prefix' => 'admin/dashboard',], function () {
    Route::get('/home', [AdminHomeController::class, 'index'])->name('admin.dashboard.home')->middleware('auth:admin');
    Route::get('login', [AdminLoginController::class, 'login'])->name('admin.dashboard.login')->middleware('guest:admin');
    Route::post('home', [AdminLoginController::class, 'check'])->name('admin.dashboard.home');
    Route::get('register', [AdminRegisterController::class, 'register'])->name('admin.dashboard.register');
    Route::post('store', [AdminRegisterController::class, 'store'])->name('admin.dashboard.store');
    Route::post('logout', [AdminLoginController::class, 'logout'])->name('admin.dashboard.logout');
    // الضبط العام
    Route::get('generalsetting', [AdminPanelSettingController::class, 'index'])->name('generalsetting.index')->middleware('auth:admin');
    Route::get('generalsetting/create', [AdminPanelSettingController::class, 'create'])->name('generalsetting.create');
    Route::post('generalsetting/store', [AdminPanelSettingController::class, 'store'])->name('generalsetting.store');
    Route::get('generalsetting/edit', [AdminPanelSettingController::class, 'edit'])->name('generalsetting.edit');
    Route::get('generalsetting/update', [AdminPanelSettingController::class, 'update'])->name('generalsetting.update');
    // الفروع
    Route::get('branches', [branchesController::class, 'index'])->name('branches.index')->middleware('auth:admin');
    Route::get('branches/{id}/edit', [branchesController::class, 'edit'])->name('branches.edit');
    Route::get('branches/create', [branchesController::class, 'create'])->name('branches.create');
    Route::post('branches/store', [branchesController::class, 'store'])->name('branches.store');
    Route::post('branches/update/{id}', [branchesController::class, 'update'])->name('branches.update');
    Route::get('branches/delete/{id}', [branchesController::class, 'delete'])->name('branches.delete');
    // الشيفتات
    Route::get('shifts', [Shifts_typeController::class, 'index'])->name('shifts.index')->middleware('auth:admin');
    Route::get('shifts/{id}/edit', [Shifts_typeController::class, 'edit'])->name('shifts.edit');
    Route::get('shifts/create', [Shifts_typeController::class, 'create'])->name('shifts.create');
    Route::post('shifts/store', [Shifts_typeController::class, 'store'])->name('shifts.store');
    Route::post('shifts/update/{id}', [Shifts_typeController::class, 'update'])->name('shifts.update');
    Route::get('shifts/delete/{id}', [Shifts_typeController::class, 'delete'])->name('shifts.delete');
    // Route::post('shifts/ajaxsearch',[Shifts_typeController::class,'ajaxsearch'])->name('shifts.ajaxsearch');
    Route::match(['get', 'post'], 'shifts/ajaxsearch', [Shifts_typeController::class, 'ajaxsearch'])->name('shifts.ajaxsearch');
    // الوظائف
    Route::get('jobs_categories', [Jobs_categoriesController::class, 'index'])->name('jobs_categories.index')->middleware('auth:admin');
    Route::get('jobs_categories/{id}/edit', [Jobs_categoriesController::class, 'edit'])->name('jobs_categories.edit');
    Route::get('jobs_categories/create', [Jobs_categoriesController::class, 'create'])->name('jobs_categories.create');
    Route::post('jobs_categories/store', [Jobs_categoriesController::class, 'store'])->name('jobs_categories.store');
    Route::post('jobs_categories/update/{id}', [Jobs_categoriesController::class, 'update'])->name('jobs_categories.update');
    Route::get('jobs_categories/delete/{id}', [Jobs_categoriesController::class, 'delete'])->name('jobs_categories.delete');
    // Route::post('jobs_categories/ajaxsearch',[Jobs_categoriesController::class,'ajaxsearch'])->name('jobs_categories.ajaxsearch');
    Route::match(['get', 'post'], 'jobs_categories/ajaxsearch', [Jobs_categoriesController::class, 'ajaxsearch'])->name('jobs_categories.ajaxsearch');
    // الادارات
    Route::get('departs', [DepartmentsController::class, 'index'])->name('departs.index')->middleware('auth:admin');
    Route::get('departs/{id}/edit', [DepartmentsController::class, 'edit'])->name('departs.edit');
    Route::get('departs/create', [DepartmentsController::class, 'create'])->name('departs.create');
    Route::post('departs/store', [DepartmentsController::class, 'store'])->name('departs.store');
    Route::post('departs/update/{id}', [DepartmentsController::class, 'update'])->name('departs.update');
    Route::get('departs/delete/{id}', [DepartmentsController::class, 'delete'])->name('departs.delete');
    // انواع الوظائف
    Route::get('jobs_categores', [Jobs_categoriesController::class, 'index'])->name('jobs_categores.index')->middleware('auth:admin');
    Route::get('jobs_categores/{id}/edit', [Jobs_categoriesController::class, 'edit'])->name('jobs_categores.edit');
    Route::get('jobs_categores/create', [Jobs_categoriesController::class, 'create'])->name('jobs_categores.create');
    Route::post('jobs_categores/store', [Jobs_categoriesController::class, 'store'])->name('jobs_categores.store');
    Route::post('jobs_categores/update/{id}', [Jobs_categoriesController::class, 'update'])->name('jobs_categores.update');
    Route::get('jobs_categores/delete/{id}', [Jobs_categoriesController::class, 'delete'])->name('jobs_categores.delete');
    Route::match(['get', 'post'], 'jobs_categores/ajaxsearch', [Jobs_categoriesController::class, 'ajaxsearch'])->name('jobs_categores.ajaxsearch');
    // الموظفين
    Route::get('employees', [EmployeesConroller::class, 'index'])->name('employees.index')->middleware('auth:admin');
    // Route::get('/employees',[EmployeesConroller::class, 'index'])->name('employees.index')->middleware('auth:admin');
    Route::get('employees/show/{id}', [EmployeesConroller::class, 'show'])->name('employees.show')->middleware('auth:admin');
    Route::get('employees/{id}/edit', [EmployeesConroller::class, 'edit'])->name('employees.edit')->middleware('auth:admin');
    Route::get('employees/create', [EmployeesConroller::class, 'create'])->name('employees.create')->middleware('auth:admin');
    Route::post('employees/store', [EmployeesConroller::class, 'store'])->name('employees.store')->middleware('auth:admin');
    Route::post('employees/update/{id}', [EmployeesConroller::class, 'update'])->name('employees.update')->middleware('auth:admin');
    Route::get('employees/delete/{id}', [EmployeesConroller::class, 'delete'])->name('employees.delete')->middleware('auth:admin');
    Route::get('employees/uploadexcel', [EmployeesConroller::class, 'uploadexcel'])->name('employees.uploadexcel')->middleware('auth:admin');
    Route::post('employees/douploadexcel', [EmployeesConroller::class, 'douploadexcel'])->name('employees.douploadexcel')->middleware('auth:admin');
    // Route::match(['get', 'post'], 'employees/ajaxsearch', [EmployeesConroller::class, 'ajaxsearch'])->name('employees.ajaxsearch')->middleware('auth:admin');
    Route::get('/employees', [EmployeesConroller::class, 'index'])->name('employees.index')->middleware('auth:admin');
    Route::get('employees/export/', [EmployeesConroller::class, 'export'])->name('employees.export');
    // بداية السنة المالية
    Route::get('finance_calender/delete/{id}', [Finance_calendersController::class, 'delete'])->name('finance_calender.delete');
    Route::post('finance_calender/show_year_monthes', [Finance_calendersController::class, 'show_year_monthes'])->name('finance_calender.show_year_monthes');
    Route::put('finance_calender/updatee/{id}', [Finance_calendersController::class, 'updatee'])->name('finance_calender.updatee');
    Route::resource('finance_calender', Finance_calendersController::class)->middleware('auth:admin');

    // الرصيد السنوى
    Route::get('Main_vacations_balance', [Main_vacations_balanceController::class, 'index'])->name('Main_vacations_balance.index')->middleware('auth:admin');
    Route::get('Main_vacations_balance/show/{id}', [Main_vacations_balanceController::class, 'show'])->name('Main_vacations_balance.show')->middleware('auth:admin');

    // ====================================
// صلاحيات المستخدمين (سوبر أدمن فقط)
// ====================================
Route::get('permissions', [AdminPermissionsController::class, 'index'])
    ->name('admin.permissions.index')->middleware('auth:admin');
Route::get('permissions/{id}/edit', [AdminPermissionsController::class, 'edit'])
    ->name('admin.permissions.edit')->middleware('auth:admin');
Route::put('permissions/{id}', [AdminPermissionsController::class, 'update'])
    ->name('admin.permissions.update')->middleware('auth:admin');
 
// ====================================
// الحضور والانصراف
// ====================================
Route::middleware(['auth:admin', 'admin.permission:attendance,can_read'])->group(function () {
    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('attendance/employee/{id}/summary', [AttendanceController::class, 'employeeSummary'])
        ->name('attendance.employee_summary');
});
Route::middleware(['auth:admin', 'admin.permission:attendance,can_create'])->group(function () {
    Route::get('attendance/create',   [AttendanceController::class, 'create'])->name('attendance.create');
    Route::post('attendance/store',   [AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('attendance/bulk',     [AttendanceController::class, 'bulkCreate'])->name('attendance.bulk_create');
    Route::post('attendance/bulk',    [AttendanceController::class, 'bulkStore'])->name('attendance.bulk_store');
});
Route::middleware(['auth:admin', 'admin.permission:attendance,can_update'])->group(function () {
    Route::get('attendance/{id}/edit',    [AttendanceController::class, 'edit'])->name('attendance.edit');
    Route::post('attendance/update/{id}', [AttendanceController::class, 'update'])->name('attendance.update');
});
Route::get('attendance/delete/{id}', [AttendanceController::class, 'delete'])
    ->name('attendance.delete')->middleware(['auth:admin', 'admin.permission:attendance,can_delete']);
 
// ====================================
// السلف
// ====================================
Route::middleware(['auth:admin', 'admin.permission:advances,can_read'])->group(function () {
    Route::get('advances', [AdvancesController::class, 'index'])->name('advances.index');
});
Route::middleware(['auth:admin', 'admin.permission:advances,can_create'])->group(function () {
    Route::get('advances/create',  [AdvancesController::class, 'create'])->name('advances.create');
    Route::post('advances/store',  [AdvancesController::class, 'store'])->name('advances.store');
});
Route::middleware(['auth:admin', 'admin.permission:advances,can_update'])->group(function () {
    Route::get('advances/{id}/edit',    [AdvancesController::class, 'edit'])->name('advances.edit');
    Route::post('advances/update/{id}', [AdvancesController::class, 'update'])->name('advances.update');
});
Route::get('advances/delete/{id}', [AdvancesController::class, 'delete'])
    ->name('advances.delete')->middleware(['auth:admin', 'admin.permission:advances,can_delete']);
 
// ====================================
// العمولات
// ====================================
Route::middleware(['auth:admin', 'admin.permission:commissions,can_read'])->group(function () {
    Route::get('commissions', [CommissionsController::class, 'index'])->name('commissions.index');
});
Route::middleware(['auth:admin', 'admin.permission:commissions,can_create'])->group(function () {
    Route::get('commissions/create',  [CommissionsController::class, 'create'])->name('commissions.create');
    Route::post('commissions/store',  [CommissionsController::class, 'store'])->name('commissions.store');
});
Route::middleware(['auth:admin', 'admin.permission:commissions,can_update'])->group(function () {
    Route::get('commissions/{id}/edit',    [CommissionsController::class, 'edit'])->name('commissions.edit');
    Route::post('commissions/update/{id}', [CommissionsController::class, 'update'])->name('commissions.update');
});
Route::get('commissions/delete/{id}', [CommissionsController::class, 'delete'])
    ->name('commissions.delete')->middleware(['auth:admin', 'admin.permission:commissions,can_delete']);
 
// ====================================
// الخصومات
// ====================================
Route::middleware(['auth:admin', 'admin.permission:deductions,can_read'])->group(function () {
    Route::get('deductions', [DeductionsController::class, 'index'])->name('deductions.index');
});
Route::middleware(['auth:admin', 'admin.permission:deductions,can_create'])->group(function () {
    Route::get('deductions/create',  [DeductionsController::class, 'create'])->name('deductions.create');
    Route::post('deductions/store',  [DeductionsController::class, 'store'])->name('deductions.store');
});
Route::middleware(['auth:admin', 'admin.permission:deductions,can_update'])->group(function () {
    Route::get('deductions/{id}/edit',    [DeductionsController::class, 'edit'])->name('deductions.edit');
    Route::post('deductions/update/{id}', [DeductionsController::class, 'update'])->name('deductions.update');
});
Route::get('deductions/delete/{id}', [DeductionsController::class, 'delete'])
    ->name('deductions.delete')->middleware(['auth:admin', 'admin.permission:deductions,can_delete']);
 
// ====================================
// مسير الرواتب
// ====================================
Route::middleware(['auth:admin', 'admin.permission:payroll,can_read'])->group(function () {
    Route::get('payroll',          [PayrollController::class, 'index'])->name('payroll.index');
    Route::get('payroll/{id}',     [PayrollController::class, 'show'])->name('payroll.show');
});
Route::middleware(['auth:admin', 'admin.permission:payroll,can_create'])->group(function () {
    Route::get('payroll/create',                [PayrollController::class, 'create'])->name('payroll.create');
    Route::post('payroll/calculate_single',     [PayrollController::class, 'calculateSingle'])->name('payroll.calculate_single');
    Route::post('payroll/calculate_bulk',       [PayrollController::class, 'calculateBulk'])->name('payroll.calculate_bulk');
});
Route::get('payroll/approve/{id}', [PayrollController::class, 'approve'])
    ->name('payroll.approve')->middleware(['auth:admin', 'admin.permission:payroll,can_update']);
Route::get('payroll/delete/{id}',  [PayrollController::class, 'delete'])
    ->name('payroll.delete')->middleware(['auth:admin', 'admin.permission:payroll,can_delete']);
});
