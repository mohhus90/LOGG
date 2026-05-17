<?php
// FILE: routes/admin.php — النسخة الكاملة النهائية

use Illuminate\Support\Facades\Route;

// ── Auth ──
use App\Http\Controllers\Admin\AdminHomeController;
use App\Http\Controllers\Admin\auth\AdminLoginController;
use App\Http\Controllers\Admin\auth\AdminRegisterController;

// ── الأقسام القديمة ──
use App\Http\Controllers\AdminPanelSettingController;
use App\Http\Controllers\Admin\branchesController;
use App\Http\Controllers\Admin\Finance_calendersController;
use App\Http\Controllers\Admin\Shifts_typeController;
use App\Http\Controllers\Admin\DepartmentsController;
use App\Http\Controllers\Admin\Jobs_categoriesController;
use App\Http\Controllers\Admin\EmployeesConroller;
use App\Http\Controllers\Admin\Main_vacations_balanceController;

// ── الأقسام الجديدة (الإضافة الأولى) ──
use App\Http\Controllers\Admin\AdminPermissionsController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\AdvancesController;
use App\Http\Controllers\Admin\CommissionsController;
use App\Http\Controllers\Admin\DeductionsController;
use App\Http\Controllers\Admin\PayrollController;
use App\Http\Controllers\Admin\FingerprintDevicesController;

// ── الأقسام الجديدة (الإضافة الثانية) ──
use App\Http\Controllers\Admin\KpiController;
use App\Http\Controllers\Admin\EmployeeRequestsController;
use App\Http\Controllers\Admin\CommissionsV2Controller;
use App\Http\Controllers\Employee\EmployeePortalController;
use App\Http\Controllers\Admin\VacationsController;

define('paginate_counter', 20);

Route::group(['prefix' => 'admin/dashboard'], function () {

    // ─────────────────────────────────────────────
    //  AUTH
    // ─────────────────────────────────────────────
    Route::get('login',    [AdminLoginController::class,    'login'])->name('admin.dashboard.login')->middleware('guest:admin');
    Route::post('home',    [AdminLoginController::class,    'check'])->name('admin.dashboard.home');
    Route::post('logout',  [AdminLoginController::class,    'logout'])->name('admin.dashboard.logout');
    Route::get('register', [AdminRegisterController::class, 'register'])->name('admin.dashboard.register');
    Route::post('store',   [AdminRegisterController::class, 'store'])->name('admin.dashboard.store');
    Route::get('/home',    [AdminHomeController::class,     'index'])->name('admin.dashboard.home.page')->middleware('auth:admin');

    // ─────────────────────────────────────────────
    //  الضبط العام — general_settings
    // ─────────────────────────────────────────────
    Route::middleware(['auth:admin', 'admin.permission:general_settings,can_read'])->group(function () {
        Route::get('generalsetting',       [AdminPanelSettingController::class, 'index'])->name('generalsetting.index');
        Route::get('generalsetting/edit',  [AdminPanelSettingController::class, 'edit'])->name('generalsetting.edit');
    });
    Route::middleware(['auth:admin', 'admin.permission:general_settings,can_create'])->group(function () {
        Route::get('generalsetting/create',  [AdminPanelSettingController::class, 'create'])->name('generalsetting.create');
        Route::post('generalsetting/store',  [AdminPanelSettingController::class, 'store'])->name('generalsetting.store');
    });
    // Route::middleware(['auth:admin','admin.permission:general_settings,can_update'])->group(function () {
    //     Route::get('generalsetting/update',  [AdminPanelSettingController::class,'update'])->name('generalsetting.update');
    // });
    // التعديل هنا: تم تغيير GET إلى PUT ليتوافق مع معايير Laravel ومع الفورم في الـ Blade
    Route::middleware(['auth:admin', 'admin.permission:general_settings,can_update'])->group(function () {
        Route::put('generalsetting/update',  [AdminPanelSettingController::class, 'update'])->name('generalsetting.update');
    });
    // ─────────────────────────────────────────────
    //  الفروع — branches
    // ─────────────────────────────────────────────
    Route::middleware(['auth:admin', 'admin.permission:branches,can_read'])->group(function () {
        Route::get('branches', [branchesController::class, 'index'])->name('branches.index');
    });
    Route::middleware(['auth:admin', 'admin.permission:branches,can_create'])->group(function () {
        Route::get('branches/create',  [branchesController::class, 'create'])->name('branches.create');
        Route::post('branches/store',  [branchesController::class, 'store'])->name('branches.store');
    });
    Route::middleware(['auth:admin', 'admin.permission:branches,can_update'])->group(function () {
        Route::get('branches/{id}/edit',    [branchesController::class, 'edit'])->name('branches.edit');
        Route::post('branches/update/{id}', [branchesController::class, 'update'])->name('branches.update');
    });
    Route::get('branches/delete/{id}', [branchesController::class, 'delete'])
        ->name('branches.delete')->middleware(['auth:admin', 'admin.permission:branches,can_delete']);

    // ─────────────────────────────────────────────
    //  الشيفتات — shifts
    // ─────────────────────────────────────────────
    Route::middleware(['auth:admin', 'admin.permission:shifts,can_read'])->group(function () {
        Route::get('shifts', [Shifts_typeController::class, 'index'])->name('shifts.index');
        Route::match(['get', 'post'], 'shifts/ajaxsearch', [Shifts_typeController::class, 'ajaxsearch'])->name('shifts.ajaxsearch');
    });
    Route::middleware(['auth:admin', 'admin.permission:shifts,can_create'])->group(function () {
        Route::get('shifts/create',  [Shifts_typeController::class, 'create'])->name('shifts.create');
        Route::post('shifts/store',  [Shifts_typeController::class, 'store'])->name('shifts.store');
    });
    Route::middleware(['auth:admin', 'admin.permission:shifts,can_update'])->group(function () {
        Route::get('shifts/{id}/edit',    [Shifts_typeController::class, 'edit'])->name('shifts.edit');
        Route::post('shifts/update/{id}', [Shifts_typeController::class, 'update'])->name('shifts.update');
    });
    Route::get('shifts/delete/{id}', [Shifts_typeController::class, 'delete'])
        ->name('shifts.delete')->middleware(['auth:admin', 'admin.permission:shifts,can_delete']);

    // ─────────────────────────────────────────────
    //  الإدارات — departments
    // ─────────────────────────────────────────────
    Route::middleware(['auth:admin', 'admin.permission:departments,can_read'])->group(function () {
        Route::get('departs', [DepartmentsController::class, 'index'])->name('departs.index');
    });
    Route::middleware(['auth:admin', 'admin.permission:departments,can_create'])->group(function () {
        Route::get('departs/create',  [DepartmentsController::class, 'create'])->name('departs.create');
        Route::post('departs/store',  [DepartmentsController::class, 'store'])->name('departs.store');
    });
    Route::middleware(['auth:admin', 'admin.permission:departments,can_update'])->group(function () {
        Route::get('departs/{id}/edit',    [DepartmentsController::class, 'edit'])->name('departs.edit');
        Route::post('departs/update/{id}', [DepartmentsController::class, 'update'])->name('departs.update');
    });
    Route::get('departs/delete/{id}', [DepartmentsController::class, 'delete'])
        ->name('departs.delete')->middleware(['auth:admin', 'admin.permission:departments,can_delete']);

    // ─────────────────────────────────────────────
    //  الوظائف — jobs_categories
    // ─────────────────────────────────────────────
    Route::middleware(['auth:admin', 'admin.permission:jobs_categories,can_read'])->group(function () {
        Route::get('jobs_categories', [Jobs_categoriesController::class, 'index'])->name('jobs_categories.index');
        Route::match(['get', 'post'], 'jobs_categories/ajaxsearch', [Jobs_categoriesController::class, 'ajaxsearch'])->name('jobs_categories.ajaxsearch');
        Route::get('jobs_categores', [Jobs_categoriesController::class, 'index'])->name('jobs_categores.index');
        Route::match(['get', 'post'], 'jobs_categores/ajaxsearch', [Jobs_categoriesController::class, 'ajaxsearch'])->name('jobs_categores.ajaxsearch');
    });
    Route::middleware(['auth:admin', 'admin.permission:jobs_categories,can_create'])->group(function () {
        Route::get('jobs_categories/create',  [Jobs_categoriesController::class, 'create'])->name('jobs_categories.create');
        Route::post('jobs_categories/store',  [Jobs_categoriesController::class, 'store'])->name('jobs_categories.store');
        Route::get('jobs_categores/create',   [Jobs_categoriesController::class, 'create'])->name('jobs_categores.create');
        Route::post('jobs_categores/store',   [Jobs_categoriesController::class, 'store'])->name('jobs_categores.store');
    });
    Route::middleware(['auth:admin', 'admin.permission:jobs_categories,can_update'])->group(function () {
        Route::get('jobs_categories/{id}/edit',    [Jobs_categoriesController::class, 'edit'])->name('jobs_categories.edit');
        Route::post('jobs_categories/update/{id}', [Jobs_categoriesController::class, 'update'])->name('jobs_categories.update');
        Route::get('jobs_categores/{id}/edit',     [Jobs_categoriesController::class, 'edit'])->name('jobs_categores.edit');
        Route::post('jobs_categores/update/{id}',  [Jobs_categoriesController::class, 'update'])->name('jobs_categores.update');
    });
    Route::get('jobs_categories/delete/{id}', [Jobs_categoriesController::class, 'delete'])
        ->name('jobs_categories.delete')->middleware(['auth:admin', 'admin.permission:jobs_categories,can_delete']);
    Route::get('jobs_categores/delete/{id}', [Jobs_categoriesController::class, 'delete'])
        ->name('jobs_categores.delete')->middleware(['auth:admin', 'admin.permission:jobs_categories,can_delete']);

    // ─────────────────────────────────────────────
    //  الموظفون — employees
    // ─────────────────────────────────────────────
    Route::middleware(['auth:admin', 'admin.permission:employees,can_read'])->group(function () {
        Route::get('employees',           [EmployeesConroller::class, 'index'])->name('employees.index');
        Route::get('employees/show/{id}', [EmployeesConroller::class, 'show'])->name('employees.show');
        Route::get('employees/export',    [EmployeesConroller::class, 'export'])->name('employees.export');
    });
    Route::middleware(['auth:admin', 'admin.permission:employees,can_create'])->group(function () {
        Route::get('employees/create',          [EmployeesConroller::class, 'create'])->name('employees.create');
        Route::post('employees/store',          [EmployeesConroller::class, 'store'])->name('employees.store');
        Route::get('employees/uploadexcel',     [EmployeesConroller::class, 'uploadexcel'])->name('employees.uploadexcel');
        Route::post('employees/douploadexcel',  [EmployeesConroller::class, 'douploadexcel'])->name('employees.douploadexcel');
    });
    Route::middleware(['auth:admin', 'admin.permission:employees,can_update'])->group(function () {
        Route::get('employees/{id}/edit',    [EmployeesConroller::class, 'edit'])->name('employees.edit');
        Route::post('employees/update/{id}', [EmployeesConroller::class, 'update'])->name('employees.update');
    });
    Route::get('employees/delete/{id}', [EmployeesConroller::class, 'delete'])
        ->name('employees.delete')->middleware(['auth:admin', 'admin.permission:employees,can_delete']);

    // ─────────────────────────────────────────────
    //  السنوات المالية — finance_calender
    // ─────────────────────────────────────────────
    Route::middleware(['auth:admin', 'admin.permission:finance_calender,can_read'])->group(function () {
        Route::get('finance_calender',        [Finance_calendersController::class, 'index'])->name('finance_calender.index');
        Route::get('finance_calender/{id}',   [Finance_calendersController::class, 'show'])->name('finance_calender.show');
        Route::post('finance_calender/show_year_monthes', [Finance_calendersController::class, 'show_year_monthes'])->name('finance_calender.show_year_monthes');
    });
    Route::middleware(['auth:admin', 'admin.permission:finance_calender,can_create'])->group(function () {
        Route::get('finance_calender/create',  [Finance_calendersController::class, 'create'])->name('finance_calender.create');
        Route::post('finance_calender',        [Finance_calendersController::class, 'store'])->name('finance_calender.store');
    });
    Route::middleware(['auth:admin', 'admin.permission:finance_calender,can_update'])->group(function () {
        Route::get('finance_calender/{id}/edit',   [Finance_calendersController::class, 'edit'])->name('finance_calender.edit');
        Route::put('finance_calender/{id}',        [Finance_calendersController::class, 'update'])->name('finance_calender.update');
        Route::put('finance_calender/updatee/{id}', [Finance_calendersController::class, 'updatee'])->name('finance_calender.updatee');
    });
    Route::get('finance_calender/delete/{id}', [Finance_calendersController::class, 'delete'])
        ->name('finance_calender.delete')->middleware(['auth:admin', 'admin.permission:finance_calender,can_delete']);

    // الرصيد السنوي
    Route::middleware(['auth:admin', 'admin.permission:vacations_balance,can_read'])->group(function () {
        Route::get('Main_vacations_balance',          [Main_vacations_balanceController::class, 'index'])->name('Main_vacations_balance.index');
        Route::get('Main_vacations_balance/show/{id}', [Main_vacations_balanceController::class, 'show'])->name('Main_vacations_balance.show');
    });

    // ─────────────────────────────────────────────
    //  الحضور والانصراف + أجهزة البصمة
    // ─────────────────────────────────────────────
    Route::middleware(['auth:admin', 'admin.permission:attendance,can_read'])->group(function () {
        Route::get('attendance',                       [AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('attendance/employee/{id}/summary', [AttendanceController::class, 'employeeSummary'])->name('attendance.employee_summary');
        Route::get('fingerprint_devices',              [FingerprintDevicesController::class, 'index'])->name('fingerprint_devices.index');
        Route::get('fingerprint_devices/{id}/logs',    [FingerprintDevicesController::class, 'logs'])->name('fingerprint_devices.logs');
        Route::get('fingerprint_devices/{id}/test',    [FingerprintDevicesController::class, 'testConnection'])->name('fingerprint_devices.test');
    });
    Route::middleware(['auth:admin', 'admin.permission:attendance,can_create'])->group(function () {
        Route::get('attendance/create',          [AttendanceController::class, 'create'])->name('attendance.create');
        Route::post('attendance/store',          [AttendanceController::class, 'store'])->name('attendance.store');
        Route::get('attendance/bulk',            [AttendanceController::class, 'bulkCreate'])->name('attendance.bulk_create');
        Route::post('attendance/bulk',           [AttendanceController::class, 'bulkStore'])->name('attendance.bulk_store');
        Route::get('attendance/excel-import',    [AttendanceController::class, 'excelImportForm'])->name('attendance.excel_import_form');
        Route::post('attendance/excel-import',   [AttendanceController::class, 'excelImport'])->name('attendance.excel_import');
        Route::get('attendance/excel-template',  [AttendanceController::class, 'excelTemplate'])->name('attendance.excel_template');
        Route::get('fingerprint_devices/create',        [FingerprintDevicesController::class, 'create'])->name('fingerprint_devices.create');
        Route::post('fingerprint_devices/store',        [FingerprintDevicesController::class, 'store'])->name('fingerprint_devices.store');
        Route::post('fingerprint_devices/{id}/sync',    [FingerprintDevicesController::class, 'sync'])->name('fingerprint_devices.sync');
        Route::post('fingerprint_devices/sync-all',     [FingerprintDevicesController::class, 'syncAll'])->name('fingerprint_devices.sync_all');
        Route::post('fingerprint_devices/process-logs', [FingerprintDevicesController::class, 'processLogs'])->name('fingerprint_devices.process_logs');
    });
    Route::middleware(['auth:admin', 'admin.permission:attendance,can_update'])->group(function () {
        Route::get('attendance/{id}/edit',      [AttendanceController::class, 'edit'])->name('attendance.edit');
        Route::post('attendance/update/{id}',   [AttendanceController::class, 'update'])->name('attendance.update');
        Route::get('fingerprint_devices/{id}/edit', [FingerprintDevicesController::class, 'edit'])->name('fingerprint_devices.edit');
        Route::put('fingerprint_devices/{id}',     [FingerprintDevicesController::class, 'update'])->name('fingerprint_devices.update');
    });
    Route::get('attendance/delete/{id}', [AttendanceController::class, 'delete'])
        ->name('attendance.delete')->middleware(['auth:admin', 'admin.permission:attendance,can_delete']);
    Route::get('fingerprint_devices/{id}/delete', [FingerprintDevicesController::class, 'delete'])
        ->name('fingerprint_devices.delete')->middleware(['auth:admin', 'admin.permission:attendance,can_delete']);

    // ─────────────────────────────────────────────
    //  طلبات الموظفين
    // ─────────────────────────────────────────────
    Route::middleware(['auth:admin', 'admin.permission:attendance,can_update'])->group(function () {
        Route::get('employee_requests',                  [EmployeeRequestsController::class, 'index'])->name('employee_requests.index');
        Route::post('employee_requests/{id}/approve',    [EmployeeRequestsController::class, 'approve'])->name('employee_requests.approve');
        Route::post('employee_requests/{id}/reject',     [EmployeeRequestsController::class, 'reject'])->name('employee_requests.reject');
    });

    // ─────────────────────────────────────────────
    //  السلف — advances
    // ─────────────────────────────────────────────
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

    // ─────────────────────────────────────────────
    //  العمولات v1 (فردية) + v2 (مرنة بالقواعد)
    // ─────────────────────────────────────────────
    Route::middleware(['auth:admin', 'admin.permission:commissions,can_read'])->group(function () {
        Route::get('commissions', [CommissionsController::class, 'index'])->name('commissions.index');
        Route::get('commissions_v2/rules',    [CommissionsV2Controller::class, 'rules'])->name('commissions_v2.rules');
        Route::get('commissions_v2/sales',    [CommissionsV2Controller::class, 'sales'])->name('commissions_v2.sales');
        Route::get('commissions_v2/calculate', [CommissionsV2Controller::class, 'calculate'])->name('commissions_v2.calculate');
    });
    Route::middleware(['auth:admin', 'admin.permission:commissions,can_create'])->group(function () {
        Route::get('commissions/create',  [CommissionsController::class, 'create'])->name('commissions.create');
        Route::post('commissions/store',  [CommissionsController::class, 'store'])->name('commissions.store');
        Route::get('commissions_v2/rules/create', [CommissionsV2Controller::class, 'createRule'])->name('commissions_v2.create_rule');
        Route::post('commissions_v2/rules/store', [CommissionsV2Controller::class, 'storeRule'])->name('commissions_v2.store_rule');
        Route::post('commissions_v2/sales/save',  [CommissionsV2Controller::class, 'saveSales'])->name('commissions_v2.save_sales');
        Route::post('commissions_v2/confirm',     [CommissionsV2Controller::class, 'confirmCalculate'])->name('commissions_v2.confirm');
    });
    Route::middleware(['auth:admin', 'admin.permission:commissions,can_update'])->group(function () {
        Route::get('commissions/{id}/edit',    [CommissionsController::class, 'edit'])->name('commissions.edit');
        Route::post('commissions/update/{id}', [CommissionsController::class, 'update'])->name('commissions.update');
    });
    Route::get('commissions/delete/{id}', [CommissionsController::class, 'delete'])
        ->name('commissions.delete')->middleware(['auth:admin', 'admin.permission:commissions,can_delete']);
    Route::get('commissions_v2/rules/delete/{id}', [CommissionsV2Controller::class, 'deleteRule'])
        ->name('commissions_v2.delete_rule')->middleware(['auth:admin', 'admin.permission:commissions,can_delete']);

    // ─────────────────────────────────────────────
    //  الخصومات — deductions
    // ─────────────────────────────────────────────
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

    // ─────────────────────────────────────────────
    //  مؤشرات الأداء KPIs
    // ─────────────────────────────────────────────
    Route::middleware(['auth:admin', 'admin.permission:attendance,can_read'])->group(function () {
        Route::get('kpi/definitions',   [KpiController::class, 'definitions'])->name('kpi.definitions');
        Route::get('kpi/scores',        [KpiController::class, 'scores'])->name('kpi.scores');
        Route::get('kpi/report',        [KpiController::class, 'report'])->name('kpi.report');
    });
    Route::middleware(['auth:admin', 'admin.permission:attendance,can_create'])->group(function () {
        Route::get('kpi/definitions/create',    [KpiController::class, 'createDefinition'])->name('kpi.create_definition');
        Route::post('kpi/definitions/store',    [KpiController::class, 'storeDefinition'])->name('kpi.store_definition');
        Route::post('kpi/scores/save',          [KpiController::class, 'saveScores'])->name('kpi.save_scores');
    });
    Route::middleware(['auth:admin', 'admin.permission:attendance,can_update'])->group(function () {
        Route::get('kpi/definitions/{id}/edit', [KpiController::class, 'editDefinition'])->name('kpi.edit_definition');
        Route::put('kpi/definitions/{id}',      [KpiController::class, 'updateDefinition'])->name('kpi.update_definition');
    });
    Route::get('kpi/definitions/{id}/delete', [KpiController::class, 'deleteDefinition'])
        ->name('kpi.delete_definition')->middleware(['auth:admin', 'admin.permission:attendance,can_delete']);

    // ─────────────────────────────────────────────
    //  مسير الرواتب — payroll
    // ─────────────────────────────────────────────
    Route::middleware(['auth:admin', 'admin.permission:payroll,can_read'])->group(function () {
        Route::get('payroll',      [PayrollController::class, 'index'])->name('payroll.index');
        Route::get('payroll/{id}', [PayrollController::class, 'show'])->name('payroll.show');
    });
    Route::middleware(['auth:admin', 'admin.permission:payroll,can_create'])->group(function () {
        Route::get('payroll/create',            [PayrollController::class, 'create'])->name('payroll.create');
        Route::post('payroll/calculate_single', [PayrollController::class, 'calculateSingle'])->name('payroll.calculate_single');
        Route::post('payroll/calculate_bulk',   [PayrollController::class, 'calculateBulk'])->name('payroll.calculate_bulk');
    });
    Route::get('payroll/approve/{id}', [PayrollController::class, 'approve'])
        ->name('payroll.approve')->middleware(['auth:admin', 'admin.permission:payroll,can_update']);
    Route::get('payroll/delete/{id}', [PayrollController::class, 'delete'])
        ->name('payroll.delete')->middleware(['auth:admin', 'admin.permission:payroll,can_delete']);

    // ─────────────────────────────────────────────
    //  صلاحيات المستخدمين — سوبر أدمن فقط
    // ─────────────────────────────────────────────
    Route::middleware('auth:admin')->group(function () {
        Route::get('permissions',           [AdminPermissionsController::class, 'index'])->name('admin.permissions.index');
        Route::get('permissions/{id}/edit', [AdminPermissionsController::class, 'edit'])->name('admin.permissions.edit');
        Route::put('permissions/{id}',      [AdminPermissionsController::class, 'update'])->name('admin.permissions.update');
    });
});

// ─────────────────────────────────────────────
//  بوابة الموظفين — مسار مستقل
// ─────────────────────────────────────────────
Route::group(['prefix' => 'employee'], function () {
    Route::get('login',     [EmployeePortalController::class, 'loginForm'])->name('employee.login');
    Route::post('login',    [EmployeePortalController::class, 'loginCheck'])->name('employee.login.check');
    Route::get('logout',    [EmployeePortalController::class, 'logout'])->name('employee.logout');
    Route::get('dashboard', [EmployeePortalController::class, 'dashboard'])->name('employee.dashboard');
    Route::post('request',  [EmployeePortalController::class, 'storeRequest'])->name('employee.request.store');
});

// ─────────────────────────────────────────────
//  أرصدة الإجازات السنوية
// ─────────────────────────────────────────────
Route::middleware(['auth:admin', 'admin.permission:vacations_balance,can_read'])->group(function () {
    Route::get('vacations',          [VacationsController::class, 'index'])->name('vacations.index');
});
Route::middleware(['auth:admin', 'admin.permission:vacations_balance,can_create'])->group(function () {
    Route::post('vacations/bulk',    [VacationsController::class, 'createBulk'])->name('vacations.create_bulk');
    Route::post('vacations/accrual', [VacationsController::class, 'runMonthlyAccrual'])->name('vacations.monthly_accrual');
});
Route::middleware(['auth:admin', 'admin.permission:vacations_balance,can_update'])->group(function () {
    Route::get('vacations/{empId}/{year}/edit',    [VacationsController::class, 'edit'])->name('vacations.edit');
    Route::post('vacations/{empId}/{year}/update', [VacationsController::class, 'update'])->name('vacations.update');
});
