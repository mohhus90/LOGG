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
use App\Http\Controllers\Admin\Finance_cln_periodsController;
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
use App\Http\Controllers\Admin\OrgLevelsController;
use App\Http\Controllers\Admin\MaintenanceController;
use App\Http\Controllers\Admin\TaxController;
use App\Http\Controllers\Admin\EtaFreeZoneController;
use App\Http\Controllers\Admin\ClientsController;
use App\Http\Controllers\Admin\SmsController;

defined('paginate_counter') || define('paginate_counter', 20);

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
    Route::middleware(['auth:admin', 'admin.permission:general_settings,can_update'])->group(function () {
        Route::post('generalsetting/update', [AdminPanelSettingController::class, 'update'])->name('generalsetting.update');
    });
    Route::middleware(['auth:admin'])->group(function () {
        Route::post('sms/test',    [AdminPanelSettingController::class, 'testSms'])->name('sms.test');
    });
    // ─────────────────────────────────────────────
    //  SMS — إرسال رسائل جماعية
    // ─────────────────────────────────────────────
    Route::middleware(['auth:admin'])->group(function () {
        Route::get('sms/compose',  [SmsController::class, 'compose'])->name('sms.compose');
        Route::get('sms/filter',   [SmsController::class, 'filterEmployees'])->name('sms.filter');
        Route::post('sms/send',    [SmsController::class, 'send'])->name('sms.send');
    });
    // ─────────────────────────────────────────────
    //  العملاء — clients
    // ─────────────────────────────────────────────
    Route::middleware(['auth:admin', 'admin.permission:branches,can_read'])->group(function () {
        Route::get('clients', [ClientsController::class, 'index'])->name('clients.index');
    });
    Route::middleware(['auth:admin', 'admin.permission:branches,can_create'])->group(function () {
        Route::get('clients/create',           [ClientsController::class, 'create'])->name('clients.create');
        Route::post('clients/store',           [ClientsController::class, 'store'])->name('clients.store');
        Route::get('clients/{id}/import',      [ClientsController::class, 'importForm'])->name('clients.import.form');
        Route::post('clients/{id}/import',     [ClientsController::class, 'importCsv'])->name('clients.import.csv');
    });
    Route::middleware(['auth:admin', 'admin.permission:branches,can_update'])->group(function () {
        Route::get('clients/{id}/edit',        [ClientsController::class, 'edit'])->name('clients.edit');
        Route::post('clients/update/{id}',     [ClientsController::class, 'update'])->name('clients.update');
    });
    Route::get('clients/delete/{id}', [ClientsController::class, 'delete'])
        ->name('clients.delete')->middleware(['auth:admin', 'admin.permission:branches,can_delete']);

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
    Route::post('jobs_categories/bulk-delete', [Jobs_categoriesController::class, 'bulkDelete'])
        ->name('jobs_categories.bulk_delete')->middleware(['auth:admin', 'admin.permission:jobs_categories,can_delete']);

    // ─────────────────────────────────────────────
    //  الهيكل الوظيفي — org_levels
    // ─────────────────────────────────────────────
    Route::middleware(['auth:admin'])->group(function () {
        Route::get('org_levels',                       [OrgLevelsController::class, 'index'])->name('org_levels.index');
        Route::get('org_levels/create',                [OrgLevelsController::class, 'create'])->name('org_levels.create');
        Route::post('org_levels/store',                [OrgLevelsController::class, 'store'])->name('org_levels.store');
        Route::get('org_levels/{id}/edit',             [OrgLevelsController::class, 'edit'])->name('org_levels.edit');
        Route::post('org_levels/update/{id}',          [OrgLevelsController::class, 'update'])->name('org_levels.update');
        Route::get('org_levels/delete/{id}',           [OrgLevelsController::class, 'delete'])->name('org_levels.delete');
        Route::get('org_levels/templates',             [OrgLevelsController::class, 'templates'])->name('org_levels.templates');
        Route::post('org_levels/load-template',        [OrgLevelsController::class, 'loadTemplate'])->name('org_levels.load_template');
    });

    // ─────────────────────────────────────────────
    //  الموظفون — employees
    // ─────────────────────────────────────────────
    Route::middleware(['auth:admin', 'admin.permission:employees,can_read'])->group(function () {
        Route::get('employees',           [EmployeesConroller::class, 'index'])->name('employees.index');
        Route::get('employees/show/{id}', [EmployeesConroller::class, 'show'])->name('employees.show');
        Route::get('employees/export',            [EmployeesConroller::class, 'export'])->name('employees.export');
        Route::get('employees/export-system-csv', [EmployeesConroller::class, 'exportSystemCsv'])->name('employees.export.system.csv');
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
        Route::post('employees/{id}/documents/upload', [EmployeesConroller::class, 'uploadDocument'])->name('employees.document.upload');
        Route::get('employees/{id}/documents/{docId}/download', [EmployeesConroller::class, 'downloadDocument'])->name('employees.document.download');
        Route::get('employees/{id}/documents/{docId}/delete', [EmployeesConroller::class, 'deleteDocument'])->name('employees.document.delete');
        Route::post('employees/update-nid-excel', [EmployeesConroller::class, 'updateNidFromExcel'])->name('employees.update.nid.excel');
        Route::post('employees/do-upload-medical', [EmployeesConroller::class, 'doUploadMedicalExcel'])->name('employees.do.upload.medical');
    });
    Route::get('employees/delete/{id}', [EmployeesConroller::class, 'delete'])
        ->name('employees.delete')->middleware(['auth:admin', 'admin.permission:employees,can_delete']);
    Route::post('employees/delete-filtered', [EmployeesConroller::class, 'deleteFiltered'])
        ->name('employees.deleteFiltered')->middleware(['auth:admin', 'admin.permission:employees,can_delete']);
    Route::middleware('auth:admin')->group(function () {
        Route::get('employees/dictionary',       [EmployeesConroller::class, 'getDictionary'])->name('employees.dictionary.get');
        Route::post('employees/dictionary/save', [EmployeesConroller::class, 'saveDictionary'])->name('employees.dictionary.save');
    });

    // ─────────────────────────────────────────────
    //  السنوات المالية — finance_calender
    // ─────────────────────────────────────────────
    Route::middleware(['auth:admin', 'admin.permission:finance_calender,can_read'])->group(function () {
        Route::get('finance_calender',        [Finance_calendersController::class, 'index'])->name('finance_calender.index');
        Route::get('finance_calender/{id}',   [Finance_calendersController::class, 'show'])->name('finance_calender.show')->where('id', '[0-9]+');
        Route::post('finance_calender/show_year_monthes', [Finance_calendersController::class, 'show_year_monthes'])->name('finance_calender.show_year_monthes');
    });
    Route::middleware(['auth:admin', 'admin.permission:finance_calender,can_create'])->group(function () {
        Route::get('finance_calender/create',  [Finance_calendersController::class, 'create'])->name('finance_calender.create');
        Route::post('finance_calender',        [Finance_calendersController::class, 'store'])->name('finance_calender.store');
    });
    Route::middleware(['auth:admin', 'admin.permission:finance_calender,can_update'])->group(function () {
        Route::get('finance_calender/{id}/edit',        [Finance_calendersController::class,   'edit'])->name('finance_calender.edit')->where('id', '[0-9]+');
        Route::put('finance_calender/{id}',             [Finance_calendersController::class,   'update'])->name('finance_calender.update')->where('id', '[0-9]+');
        Route::put('finance_calender/updatee/{id}',     [Finance_calendersController::class,   'updatee'])->name('finance_calender.updatee')->where('id', '[0-9]+');
        Route::get('finance_calender/period/{id}/edit', [Finance_cln_periodsController::class, 'edit'])->name('finance_cln_period.edit')->where('id', '[0-9]+');
        Route::put('finance_calender/period/{id}',      [Finance_cln_periodsController::class, 'update'])->name('finance_cln_period.update')->where('id', '[0-9]+');
    });
    Route::get('finance_calender/delete/{id}', [Finance_calendersController::class, 'delete'])
        ->name('finance_calender.delete')->middleware(['auth:admin', 'admin.permission:finance_calender,can_delete'])->where('id', '[0-9]+');

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
        Route::get('attendance/generate-weekly-leaves',  [AttendanceController::class, 'generateWeeklyLeavesForm'])->name('attendance.generate_weekly_leaves_form');
        Route::post('attendance/generate-weekly-leaves', [AttendanceController::class, 'generateWeeklyLeaves'])->name('attendance.generate_weekly_leaves');
        Route::get('fingerprint_devices/create',        [FingerprintDevicesController::class, 'create'])->name('fingerprint_devices.create');
        Route::post('fingerprint_devices/store',        [FingerprintDevicesController::class, 'store'])->name('fingerprint_devices.store');
        Route::post('fingerprint_devices/{id}/sync',    [FingerprintDevicesController::class, 'sync'])->name('fingerprint_devices.sync');
        Route::post('fingerprint_devices/sync-all',     [FingerprintDevicesController::class, 'syncAll'])->name('fingerprint_devices.sync_all');
        Route::post('fingerprint_devices/process-logs', [FingerprintDevicesController::class, 'processLogs'])->name('fingerprint_devices.process_logs');
    });
    Route::middleware(['auth:admin', 'admin.permission:attendance,can_update'])->group(function () {
        Route::get('attendance/{id}/edit',               [AttendanceController::class, 'edit'])->name('attendance.edit');
        Route::post('attendance/update/{id}',            [AttendanceController::class, 'update'])->name('attendance.update');
        Route::post('attendance/{id}/resolve-missing',   [AttendanceController::class, 'resolveMissingPunch'])->name('attendance.resolve_missing');
        Route::post('attendance/{id}/update-shift',        [AttendanceController::class, 'updateShift'])->name('attendance.update_shift');
        Route::post('attendance/{id}/reprocess-fingerprint', [AttendanceController::class, 'reprocessFingerprint'])->name('attendance.reprocess_fingerprint');
        Route::get('fingerprint_devices/{id}/edit',        [FingerprintDevicesController::class, 'edit'])->name('fingerprint_devices.edit');
        Route::put('fingerprint_devices/{id}',             [FingerprintDevicesController::class, 'update'])->name('fingerprint_devices.update');
        Route::post('fingerprint_devices/{id}/generate-token', [FingerprintDevicesController::class, 'generateToken'])->name('fingerprint_devices.generate_token');
        Route::get('fingerprint_devices/{id}/setup-guide',    [FingerprintDevicesController::class, 'setupGuide'])->name('fingerprint_devices.setup_guide');
        Route::post('fingerprint_devices/{id}/void-logs',      [FingerprintDevicesController::class, 'voidLogs'])->name('fingerprint_devices.void_logs');
        Route::put('fingerprint_devices/{id}/logs/{logId}',    [FingerprintDevicesController::class, 'updateLog'])->name('fingerprint_devices.log_update');
    });
    Route::get('attendance/delete/{id}', [AttendanceController::class, 'delete'])
        ->name('attendance.delete')->middleware(['auth:admin', 'admin.permission:attendance,can_delete']);
    Route::post('attendance/bulk-delete', [AttendanceController::class, 'bulkDelete'])
        ->name('attendance.bulk_delete')->middleware(['auth:admin', 'admin.permission:attendance,can_delete']);
    Route::post('attendance/void-fingerprint', [AttendanceController::class, 'voidFingerprint'])
        ->name('attendance.void_fingerprint')->middleware(['auth:admin', 'admin.permission:attendance,can_update']);
    Route::post('attendance/bulk-reprocess-fingerprint', [AttendanceController::class, 'bulkReprocessFingerprint'])
        ->name('attendance.bulk_reprocess_fingerprint')->middleware(['auth:admin', 'admin.permission:attendance,can_update']);
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
    //  ⚠️ الـ routes الثابتة يجب أن تكون قبل payroll/{id}
    // ─────────────────────────────────────────────
    Route::middleware(['auth:admin', 'admin.permission:payroll,can_read'])->group(function () {
        Route::get('payroll', [PayrollController::class, 'index'])->name('payroll.index');
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
    // payroll/{id} يجب أن يكون آخراً لأنه wildcard يلتقط أي نص
    Route::get('payroll/{id}', [PayrollController::class, 'show'])
        ->name('payroll.show')->middleware(['auth:admin', 'admin.permission:payroll,can_read'])
        ->where('id', '[0-9]+');

    // ─────────────────────────────────────────────
    //  بدل الإجازة — leave_compensation
    // ─────────────────────────────────────────────
    Route::middleware('auth:admin')->group(function () {
        Route::get('leave-compensation',       [\App\Http\Controllers\Admin\LeaveCompensationController::class, 'index'])->name('leave_compensation.index');
        Route::post('leave-compensation/save', [\App\Http\Controllers\Admin\LeaveCompensationController::class, 'update'])->name('leave_compensation.update');
    });

    // ─────────────────────────────────────────────
    //  الجزاءات — sanctions
    // ─────────────────────────────────────────────
    Route::middleware('auth:admin')->group(function () {
        Route::get('sanctions',              [\App\Http\Controllers\Admin\SanctionController::class, 'index'])->name('sanctions.index');
        Route::get('sanctions/create',       [\App\Http\Controllers\Admin\SanctionController::class, 'create'])->name('sanctions.create');
        Route::post('sanctions/store',       [\App\Http\Controllers\Admin\SanctionController::class, 'store'])->name('sanctions.store');
        Route::get('sanctions/{id}/edit',    [\App\Http\Controllers\Admin\SanctionController::class, 'edit'])->name('sanctions.edit');
        Route::post('sanctions/update/{id}', [\App\Http\Controllers\Admin\SanctionController::class, 'update'])->name('sanctions.update');
        Route::post('sanctions/cancel/{id}', [\App\Http\Controllers\Admin\SanctionController::class, 'cancel'])->name('sanctions.cancel');
        Route::post('sanctions/delete/{id}', [\App\Http\Controllers\Admin\SanctionController::class, 'delete'])->name('sanctions.delete');
    });

    // ─────────────────────────────────────────────
    //  صلاحيات المستخدمين — سوبر أدمن فقط
    // ─────────────────────────────────────────────
    Route::middleware('auth:admin')->group(function () {
        Route::get('permissions',           [AdminPermissionsController::class, 'index'])->name('admin.permissions.index');
        Route::get('permissions/{id}/edit', [AdminPermissionsController::class, 'edit'])->name('admin.permissions.edit');
        Route::put('permissions/{id}',      [AdminPermissionsController::class, 'update'])->name('admin.permissions.update');
    });

    // ─────────────────────────────────────────────
    //  التقارير — reports
    // ─────────────────────────────────────────────
    Route::middleware('auth:admin')->group(function () {
        Route::get('reports',            [\App\Http\Controllers\Admin\ReportsController::class, 'index'])->name('reports.index');
        Route::get('reports/attendance', [\App\Http\Controllers\Admin\ReportsController::class, 'attendance'])->name('reports.attendance');
        Route::get('reports/employees',  [\App\Http\Controllers\Admin\ReportsController::class, 'employees'])->name('reports.employees');
        Route::get('reports/advances',   [\App\Http\Controllers\Admin\ReportsController::class, 'advances'])->name('reports.advances');
        Route::get('reports/vacations',  [\App\Http\Controllers\Admin\ReportsController::class, 'vacations'])->name('reports.vacations');
    });

    // ─────────────────────────────────────────────
    //  الصيانة والنسخ الاحتياطي — maintenance (سوبر أدمن فقط)
    // ─────────────────────────────────────────────
    Route::middleware('auth:admin')->group(function () {
        Route::get('maintenance',                    [MaintenanceController::class, 'index'])->name('maintenance.index');
        Route::post('maintenance/backup/now',        [MaintenanceController::class, 'backupNow'])->name('maintenance.backup.now');
        Route::get('maintenance/backup/download',    [MaintenanceController::class, 'download'])->name('maintenance.backup.download');
        Route::post('maintenance/backup/restore',    [MaintenanceController::class, 'restore'])->name('maintenance.backup.restore');
        Route::get('maintenance/backup/delete',      [MaintenanceController::class, 'deleteBackup'])->name('maintenance.backup.delete');
        Route::post('maintenance/logs/clear',        [MaintenanceController::class, 'clearLogs'])->name('maintenance.logs.clear');
    });

    // ─────────────────────────────────────────────
    //  الضرائب والفواتير الإلكترونية (ETA)
    // ─────────────────────────────────────────────
    Route::middleware(['auth:admin', 'admin.permission:tax,can_read'])->group(function () {
        Route::get('tax',                        [TaxController::class, 'index'])->name('tax.index');
        Route::get('tax/invoices',               [TaxController::class, 'invoices'])->name('tax.invoices');
        Route::get('tax/invoices/{id}',          [TaxController::class, 'show'])->name('tax.show');
        Route::post('tax/invoices/{id}/fetch',   [TaxController::class, 'fetchDetails'])->name('tax.fetch_details');
        Route::get('tax/vat-report',             [TaxController::class, 'vatReport'])->name('tax.vat_report');
        Route::get('tax/export',                 [TaxController::class, 'export'])->name('tax.export');
        Route::get('tax/export/sales-doc',       [TaxController::class, 'exportSalesDoc'])->name('tax.export.sales_doc');
        Route::get('tax/export/form41',          [TaxController::class, 'exportForm41'])->name('tax.export.form41');
        Route::get('tax/export/csv-form',        [TaxController::class, 'exportCsvForm'])->name('tax.export.csv_form');
        Route::get('tax/sync',                   [TaxController::class, 'syncForm'])->name('tax.sync.form');
        Route::get('tax/free-zones',             [EtaFreeZoneController::class, 'index'])->name('tax.free_zones.index');
    });
    Route::middleware(['auth:admin', 'admin.permission:tax,can_create'])->group(function () {
        Route::post('tax/sync',                  [TaxController::class, 'sync'])->name('tax.sync');
        Route::post('tax/free-zones',            [EtaFreeZoneController::class, 'store'])->name('tax.free_zones.store');
        Route::delete('tax/free-zones/{freeZone}', [EtaFreeZoneController::class, 'destroy'])->name('tax.free_zones.destroy');
    });
    Route::middleware(['auth:admin', 'admin.permission:tax,can_update'])->group(function () {
        Route::get('tax/credentials',            [TaxController::class, 'credentials'])->name('tax.credentials');
        Route::post('tax/credentials',           [TaxController::class, 'saveCredentials'])->name('tax.credentials.save');
        Route::post('tax/credentials/test',      [TaxController::class, 'testConnection'])->name('tax.test_connection');
        Route::post('tax/invoices/{id}/post',    [TaxController::class, 'postInvoice'])->name('tax.post');
        Route::post('tax/invoices/{id}/unpost',  [TaxController::class, 'unpostInvoice'])->name('tax.unpost');
        Route::post('tax/invoices/post-bulk',    [TaxController::class, 'postBulk'])->name('tax.post_bulk');
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
Route::middleware(['auth:admin', 'admin.permission:vacations_balance,can_delete'])->group(function () {
    Route::delete('vacations/{empId}/{year}', [VacationsController::class, 'deleteBalance'])->name('vacations.delete_balance');
    // fallback for browsers that don't support DELETE
    Route::post('vacations/{empId}/{year}/delete', [VacationsController::class, 'deleteBalance'])->name('vacations.delete_balance_post');
});

