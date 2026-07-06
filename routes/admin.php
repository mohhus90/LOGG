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
use App\Http\Controllers\Admin\BranchCommissionsController;
use App\Http\Controllers\Admin\BonusesController;

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
    Route::middleware(['auth:admin', 'admin.permission:general_settings,can_update'])->group(function () {
        Route::post('sms/test',    [AdminPanelSettingController::class, 'testSms'])->name('sms.test');
    });
    // ─────────────────────────────────────────────
    //  SMS — إرسال رسائل جماعية
    // ─────────────────────────────────────────────
    Route::middleware(['auth:admin', 'admin.permission:sms,can_read'])->group(function () {
        Route::get('sms/compose',  [SmsController::class, 'compose'])->name('sms.compose');
        Route::get('sms/filter',   [SmsController::class, 'filterEmployees'])->name('sms.filter');
    });
    Route::middleware(['auth:admin', 'admin.permission:sms,can_create'])->group(function () {
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
        Route::get('attendance/range-batch',     [AttendanceController::class, 'rangeBatchCreate'])->name('attendance.range_batch_create');
        Route::post('attendance/range-batch',    [AttendanceController::class, 'rangeBatchStore'])->name('attendance.range_batch_store');
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
        Route::post('attendance/{id}/toggle-weekly-off',    [AttendanceController::class, 'toggleWeeklyOff'])->name('attendance.toggle_weekly_off');
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
        Route::get('advances/create',      [AdvancesController::class, 'create'])->name('advances.create');
        Route::post('advances/store',      [AdvancesController::class, 'store'])->name('advances.store');
        Route::get('advances/copy-month',  [AdvancesController::class, 'copyMonthForm'])->name('advances.copy_month_form');
        Route::post('advances/copy-month', [AdvancesController::class, 'copyMonth'])->name('advances.copy_month');
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
        Route::get('commissions/report', [CommissionsController::class, 'report'])->name('commissions.report');
        Route::get('commissions_v2/rules',    [CommissionsV2Controller::class, 'rules'])->name('commissions_v2.rules');
        Route::get('commissions_v2/sales',    [CommissionsV2Controller::class, 'sales'])->name('commissions_v2.sales');
        Route::get('commissions_v2/calculate', [CommissionsV2Controller::class, 'calculate'])->name('commissions_v2.calculate');
    });
    Route::middleware(['auth:admin', 'admin.permission:commissions,can_create'])->group(function () {
        Route::get('commissions/create',  [CommissionsController::class, 'create'])->name('commissions.create');
        Route::post('commissions/store',  [CommissionsController::class, 'store'])->name('commissions.store');
        Route::get('commissions_v2/rules/create', [CommissionsV2Controller::class, 'createRule'])->name('commissions_v2.create_rule');
        Route::post('commissions_v2/rules/store', [CommissionsV2Controller::class, 'storeRule'])->name('commissions_v2.store_rule');
        Route::post('commissions_v2/sales/save',         [CommissionsV2Controller::class, 'saveSales'])->name('commissions_v2.save_sales');
        Route::post('commissions_v2/sales/store',        [CommissionsV2Controller::class, 'storeSaleRecord'])->name('commissions_v2.store_sale');
        Route::post('commissions_v2/confirm',            [CommissionsV2Controller::class, 'confirmCalculate'])->name('commissions_v2.confirm');
    });
    Route::middleware(['auth:admin', 'admin.permission:commissions,can_update'])->group(function () {
        Route::get('commissions/{id}/edit',    [CommissionsController::class, 'edit'])->name('commissions.edit');
        Route::post('commissions/update/{id}', [CommissionsController::class, 'update'])->name('commissions.update');
        Route::post('commissions_v2/sales/update/{id}',  [CommissionsV2Controller::class, 'updateSaleRecord'])->name('commissions_v2.update_sale');
    });
    Route::get('commissions/delete/{id}', [CommissionsController::class, 'delete'])
        ->name('commissions.delete')->middleware(['auth:admin', 'admin.permission:commissions,can_delete']);
    Route::get('commissions_v2/rules/delete/{id}', [CommissionsV2Controller::class, 'deleteRule'])
        ->name('commissions_v2.delete_rule')->middleware(['auth:admin', 'admin.permission:commissions,can_delete']);
    Route::get('commissions_v2/sales/delete/{id}', [CommissionsV2Controller::class, 'deleteSaleRecord'])
        ->name('commissions_v2.delete_sale')->middleware(['auth:admin', 'admin.permission:commissions,can_delete']);

    // ─────────────────────────────────────────────
    //  عمولات الفروع (مبنية على نسبة تحقيق التارجت)
    // ─────────────────────────────────────────────
    Route::middleware(['auth:admin', 'admin.permission:commissions,can_read'])->group(function () {
        Route::get('branch_commissions',                    [BranchCommissionsController::class, 'index'])->name('branch_commissions.index');
        Route::get('branch_commissions/targets',            [BranchCommissionsController::class, 'targets'])->name('branch_commissions.targets');
        Route::get('branch_commissions/employee-targets',   [BranchCommissionsController::class, 'employeeTargets'])->name('branch_commissions.employee_targets');
        Route::get('branch_commissions/events',             [BranchCommissionsController::class, 'events'])->name('branch_commissions.events');
        Route::get('branch_commissions/calculate',          [BranchCommissionsController::class, 'calculate'])->name('branch_commissions.calculate');
    });
    Route::middleware(['auth:admin', 'admin.permission:commissions,can_create'])->group(function () {
        Route::get('branch_commissions/create',        [BranchCommissionsController::class, 'create'])->name('branch_commissions.create');
        Route::post('branch_commissions/store',        [BranchCommissionsController::class, 'store'])->name('branch_commissions.store');
        Route::post('branch_commissions/targets/save',          [BranchCommissionsController::class, 'saveTargets'])->name('branch_commissions.save_targets');
        Route::post('branch_commissions/employee-targets/save', [BranchCommissionsController::class, 'saveEmployeeTargets'])->name('branch_commissions.save_employee_targets');
        Route::post('branch_commissions/events/save',           [BranchCommissionsController::class, 'saveEvent'])->name('branch_commissions.save_event');
        Route::post('branch_commissions/confirm',               [BranchCommissionsController::class, 'confirmCalculate'])->name('branch_commissions.confirm');
    });
    Route::middleware(['auth:admin', 'admin.permission:commissions,can_update'])->group(function () {
        Route::get('branch_commissions/{id}/edit',    [BranchCommissionsController::class, 'edit'])->name('branch_commissions.edit');
        Route::post('branch_commissions/update/{id}', [BranchCommissionsController::class, 'update'])->name('branch_commissions.update');
    });
    Route::get('branch_commissions/delete/{id}', [BranchCommissionsController::class, 'delete'])
        ->name('branch_commissions.delete')->middleware(['auth:admin', 'admin.permission:commissions,can_delete']);
    Route::get('branch_commissions/events/delete/{id}', [BranchCommissionsController::class, 'deleteEvent'])
        ->name('branch_commissions.delete_event')->middleware(['auth:admin', 'admin.permission:commissions,can_delete']);

    // ─────────────────────────────────────────────
    //  المكافآت — bonuses
    // ─────────────────────────────────────────────
    Route::middleware(['auth:admin', 'admin.permission:bonuses,can_read'])->group(function () {
        Route::get('bonuses', [BonusesController::class, 'index'])->name('bonuses.index');
    });
    Route::middleware(['auth:admin', 'admin.permission:bonuses,can_create'])->group(function () {
        Route::get('bonuses/create',  [BonusesController::class, 'create'])->name('bonuses.create');
        Route::post('bonuses/store',  [BonusesController::class, 'store'])->name('bonuses.store');
    });
    Route::middleware(['auth:admin', 'admin.permission:bonuses,can_update'])->group(function () {
        Route::get('bonuses/{id}/edit',    [BonusesController::class, 'edit'])->name('bonuses.edit');
        Route::post('bonuses/update/{id}', [BonusesController::class, 'update'])->name('bonuses.update');
    });
    Route::get('bonuses/delete/{id}', [BonusesController::class, 'delete'])
        ->name('bonuses.delete')->middleware(['auth:admin', 'admin.permission:bonuses,can_delete']);

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
    Route::middleware(['auth:admin', 'admin.permission:kpi,can_read'])->group(function () {
        Route::get('kpi/definitions',   [KpiController::class, 'definitions'])->name('kpi.definitions');
        Route::get('kpi/scores',        [KpiController::class, 'scores'])->name('kpi.scores');
        Route::get('kpi/report',        [KpiController::class, 'report'])->name('kpi.report');
        Route::get('kpi/guide',         [KpiController::class, 'guide'])->name('kpi.guide');
        Route::get('kpi/export-template', [KpiController::class, 'exportTemplate'])->name('kpi.export_template');
    });
    Route::middleware(['auth:admin', 'admin.permission:kpi,can_create'])->group(function () {
        Route::get('kpi/definitions/create',    [KpiController::class, 'createDefinition'])->name('kpi.create_definition');
        Route::post('kpi/definitions/store',    [KpiController::class, 'storeDefinition'])->name('kpi.store_definition');
        Route::post('kpi/scores/save',          [KpiController::class, 'saveScores'])->name('kpi.save_scores');
        Route::post('kpi/import-scores',        [KpiController::class, 'importScores'])->name('kpi.import_scores');
    });
    Route::middleware(['auth:admin', 'admin.permission:kpi,can_update'])->group(function () {
        Route::get('kpi/definitions/{id}/edit', [KpiController::class, 'editDefinition'])->name('kpi.edit_definition');
        Route::put('kpi/definitions/{id}',      [KpiController::class, 'updateDefinition'])->name('kpi.update_definition');
    });
    Route::get('kpi/definitions/{id}/delete', [KpiController::class, 'deleteDefinition'])
        ->name('kpi.delete_definition')->middleware(['auth:admin', 'admin.permission:kpi,can_delete']);

    // ─────────────────────────────────────────────
    //  كشف الرواتب — payroll
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
    Route::get('payroll/unapprove/{id}', [PayrollController::class, 'unapprove'])
        ->name('payroll.unapprove')->middleware(['auth:admin', 'admin.permission:payroll,can_update']);
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
        Route::get('reports/advances',     [\App\Http\Controllers\Admin\ReportsController::class, 'advances'])->name('reports.advances');
        Route::get('reports/vacations',    [\App\Http\Controllers\Admin\ReportsController::class, 'vacations'])->name('reports.vacations');
        Route::get('reports/commissions',  [\App\Http\Controllers\Admin\ReportsController::class, 'commissions'])->name('reports.commissions');
        Route::get('reports/kpi',          [\App\Http\Controllers\Admin\ReportsController::class, 'kpiReport'])->name('reports.kpi');
        Route::get('reports/payroll',      [\App\Http\Controllers\Admin\ReportsController::class, 'payroll'])->name('reports.payroll');
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

    // ═════════════════════════════════════════════
    //  موديول المبيعات — Sales Module
    // ═════════════════════════════════════════════

    // ── وحدات القياس ──
    Route::middleware(['auth:admin', 'admin.permission:item_units,can_read'])->group(function () {
        Route::get('sales/item-units',           [\App\Http\Controllers\Admin\Sales\ItemUnitsController::class, 'index'])->name('item_units.index');
    });
    Route::middleware(['auth:admin', 'admin.permission:item_units,can_create'])->group(function () {
        Route::get('sales/item-units/create',    [\App\Http\Controllers\Admin\Sales\ItemUnitsController::class, 'create'])->name('item_units.create');
        Route::post('sales/item-units/store',    [\App\Http\Controllers\Admin\Sales\ItemUnitsController::class, 'store'])->name('item_units.store');
    });
    Route::middleware(['auth:admin', 'admin.permission:item_units,can_update'])->group(function () {
        Route::get('sales/item-units/{id}/edit', [\App\Http\Controllers\Admin\Sales\ItemUnitsController::class, 'edit'])->name('item_units.edit');
        Route::post('sales/item-units/{id}',     [\App\Http\Controllers\Admin\Sales\ItemUnitsController::class, 'update'])->name('item_units.update');
    });
    Route::middleware(['auth:admin', 'admin.permission:item_units,can_delete'])->group(function () {
        Route::get('sales/item-units/{id}/delete', [\App\Http\Controllers\Admin\Sales\ItemUnitsController::class, 'delete'])->name('item_units.delete');
    });

    // ── مجموعات الأصناف ──
    Route::middleware(['auth:admin', 'admin.permission:item_categories,can_read'])->group(function () {
        Route::get('sales/item-categories',           [\App\Http\Controllers\Admin\Sales\ItemCategoriesController::class, 'index'])->name('item_categories.index');
    });
    Route::middleware(['auth:admin', 'admin.permission:item_categories,can_create'])->group(function () {
        Route::get('sales/item-categories/create',    [\App\Http\Controllers\Admin\Sales\ItemCategoriesController::class, 'create'])->name('item_categories.create');
        Route::post('sales/item-categories/store',    [\App\Http\Controllers\Admin\Sales\ItemCategoriesController::class, 'store'])->name('item_categories.store');
    });
    Route::middleware(['auth:admin', 'admin.permission:item_categories,can_update'])->group(function () {
        Route::get('sales/item-categories/{id}/edit', [\App\Http\Controllers\Admin\Sales\ItemCategoriesController::class, 'edit'])->name('item_categories.edit');
        Route::post('sales/item-categories/{id}',     [\App\Http\Controllers\Admin\Sales\ItemCategoriesController::class, 'update'])->name('item_categories.update');
    });
    Route::middleware(['auth:admin', 'admin.permission:item_categories,can_delete'])->group(function () {
        Route::get('sales/item-categories/{id}/delete', [\App\Http\Controllers\Admin\Sales\ItemCategoriesController::class, 'delete'])->name('item_categories.delete');
    });

    // ── الأصناف ──
    Route::middleware(['auth:admin', 'admin.permission:items,can_read'])->group(function () {
        Route::get('sales/items',                [\App\Http\Controllers\Admin\Sales\ItemsController::class, 'index'])->name('items.index');
        Route::get('sales/items/{id}',           [\App\Http\Controllers\Admin\Sales\ItemsController::class, 'show'])->where('id', '[0-9]+')->name('items.show');
        Route::get('sales/items/ajax/search',    [\App\Http\Controllers\Admin\Sales\ItemsController::class, 'ajaxSearch'])->name('items.ajax.search');
    });
    Route::middleware(['auth:admin', 'admin.permission:items,can_create'])->group(function () {
        Route::get('sales/items/create',         [\App\Http\Controllers\Admin\Sales\ItemsController::class, 'create'])->name('items.create');
        Route::post('sales/items/store',         [\App\Http\Controllers\Admin\Sales\ItemsController::class, 'store'])->name('items.store');
    });
    Route::middleware(['auth:admin', 'admin.permission:items,can_update'])->group(function () {
        Route::get('sales/items/{id}/edit',      [\App\Http\Controllers\Admin\Sales\ItemsController::class, 'edit'])->name('items.edit');
        Route::post('sales/items/{id}',          [\App\Http\Controllers\Admin\Sales\ItemsController::class, 'update'])->name('items.update');
    });
    Route::middleware(['auth:admin', 'admin.permission:items,can_delete'])->group(function () {
        Route::get('sales/items/{id}/delete',    [\App\Http\Controllers\Admin\Sales\ItemsController::class, 'delete'])->name('items.delete');
    });

    // ── العملاء ──
    Route::middleware(['auth:admin', 'admin.permission:sales_customers,can_read'])->group(function () {
        Route::get('sales/customers',            [\App\Http\Controllers\Admin\Sales\CustomersController::class, 'index'])->name('sales_customers.index');
        Route::get('sales/customers/{id}',       [\App\Http\Controllers\Admin\Sales\CustomersController::class, 'show'])->where('id', '[0-9]+')->name('sales_customers.show');
    });
    Route::middleware(['auth:admin', 'admin.permission:sales_customers,can_create'])->group(function () {
        Route::get('sales/customers/create',     [\App\Http\Controllers\Admin\Sales\CustomersController::class, 'create'])->name('sales_customers.create');
        Route::post('sales/customers/store',     [\App\Http\Controllers\Admin\Sales\CustomersController::class, 'store'])->name('sales_customers.store');
    });
    Route::middleware(['auth:admin', 'admin.permission:sales_customers,can_update'])->group(function () {
        Route::get('sales/customers/{id}/edit',  [\App\Http\Controllers\Admin\Sales\CustomersController::class, 'edit'])->name('sales_customers.edit');
        Route::post('sales/customers/{id}',      [\App\Http\Controllers\Admin\Sales\CustomersController::class, 'update'])->name('sales_customers.update');
    });
    Route::middleware(['auth:admin', 'admin.permission:sales_customers,can_delete'])->group(function () {
        Route::get('sales/customers/{id}/delete', [\App\Http\Controllers\Admin\Sales\CustomersController::class, 'delete'])->name('sales_customers.delete');
    });

    // ── عروض الأسعار ──
    Route::middleware(['auth:admin', 'admin.permission:sales_quotations,can_read'])->group(function () {
        Route::get('sales/quotations',               [\App\Http\Controllers\Admin\Sales\SalesQuotationsController::class, 'index'])->name('sales_quotations.index');
        Route::get('sales/quotations/{id}',          [\App\Http\Controllers\Admin\Sales\SalesQuotationsController::class, 'show'])->where('id', '[0-9]+')->name('sales_quotations.show');
        Route::get('sales/quotations/{id}/print',    [\App\Http\Controllers\Admin\Sales\SalesQuotationsController::class, 'print'])->name('sales_quotations.print');
    });
    Route::middleware(['auth:admin', 'admin.permission:sales_quotations,can_create'])->group(function () {
        Route::get('sales/quotations/create',        [\App\Http\Controllers\Admin\Sales\SalesQuotationsController::class, 'create'])->name('sales_quotations.create');
        Route::post('sales/quotations/store',        [\App\Http\Controllers\Admin\Sales\SalesQuotationsController::class, 'store'])->name('sales_quotations.store');
        Route::post('sales/quotations/{id}/convert', [\App\Http\Controllers\Admin\Sales\SalesQuotationsController::class, 'convertToOrder'])->name('sales_quotations.convert');
    });
    Route::middleware(['auth:admin', 'admin.permission:sales_quotations,can_update'])->group(function () {
        Route::get('sales/quotations/{id}/edit',     [\App\Http\Controllers\Admin\Sales\SalesQuotationsController::class, 'edit'])->name('sales_quotations.edit');
        Route::post('sales/quotations/{id}',         [\App\Http\Controllers\Admin\Sales\SalesQuotationsController::class, 'update'])->name('sales_quotations.update');
        Route::post('sales/quotations/{id}/status',  [\App\Http\Controllers\Admin\Sales\SalesQuotationsController::class, 'updateStatus'])->name('sales_quotations.status');
    });
    Route::middleware(['auth:admin', 'admin.permission:sales_quotations,can_delete'])->group(function () {
        Route::get('sales/quotations/{id}/delete',   [\App\Http\Controllers\Admin\Sales\SalesQuotationsController::class, 'delete'])->name('sales_quotations.delete');
    });

    // ── أوامر البيع ──
    Route::middleware(['auth:admin', 'admin.permission:sales_orders,can_read'])->group(function () {
        Route::get('sales/orders',               [\App\Http\Controllers\Admin\Sales\SalesOrdersController::class, 'index'])->name('sales_orders.index');
        Route::get('sales/orders/{id}',          [\App\Http\Controllers\Admin\Sales\SalesOrdersController::class, 'show'])->where('id', '[0-9]+')->name('sales_orders.show');
        Route::get('sales/orders/{id}/print',    [\App\Http\Controllers\Admin\Sales\SalesOrdersController::class, 'print'])->name('sales_orders.print');
    });
    Route::middleware(['auth:admin', 'admin.permission:sales_orders,can_create'])->group(function () {
        Route::get('sales/orders/create',        [\App\Http\Controllers\Admin\Sales\SalesOrdersController::class, 'create'])->name('sales_orders.create');
        Route::post('sales/orders/store',        [\App\Http\Controllers\Admin\Sales\SalesOrdersController::class, 'store'])->name('sales_orders.store');
        Route::post('sales/orders/{id}/invoice', [\App\Http\Controllers\Admin\Sales\SalesOrdersController::class, 'createInvoice'])->name('sales_orders.invoice');
    });
    Route::middleware(['auth:admin', 'admin.permission:sales_orders,can_update'])->group(function () {
        Route::get('sales/orders/{id}/edit',     [\App\Http\Controllers\Admin\Sales\SalesOrdersController::class, 'edit'])->name('sales_orders.edit');
        Route::post('sales/orders/{id}',         [\App\Http\Controllers\Admin\Sales\SalesOrdersController::class, 'update'])->name('sales_orders.update');
        Route::post('sales/orders/{id}/status',  [\App\Http\Controllers\Admin\Sales\SalesOrdersController::class, 'updateStatus'])->name('sales_orders.status');
    });
    Route::middleware(['auth:admin', 'admin.permission:sales_orders,can_delete'])->group(function () {
        Route::get('sales/orders/{id}/delete',   [\App\Http\Controllers\Admin\Sales\SalesOrdersController::class, 'delete'])->name('sales_orders.delete');
    });

    // ── فواتير البيع ──
    Route::middleware(['auth:admin', 'admin.permission:sales_invoices,can_read'])->group(function () {
        Route::get('sales/invoices',             [\App\Http\Controllers\Admin\Sales\SalesInvoicesController::class, 'index'])->name('sales_invoices.index');
        Route::get('sales/invoices/{id}',        [\App\Http\Controllers\Admin\Sales\SalesInvoicesController::class, 'show'])->where('id', '[0-9]+')->name('sales_invoices.show');
        Route::get('sales/invoices/{id}/print',  [\App\Http\Controllers\Admin\Sales\SalesInvoicesController::class, 'print'])->name('sales_invoices.print');
    });
    Route::middleware(['auth:admin', 'admin.permission:sales_invoices,can_create'])->group(function () {
        Route::get('sales/invoices/create',      [\App\Http\Controllers\Admin\Sales\SalesInvoicesController::class, 'create'])->name('sales_invoices.create');
        Route::post('sales/invoices/store',      [\App\Http\Controllers\Admin\Sales\SalesInvoicesController::class, 'store'])->name('sales_invoices.store');
    });
    Route::middleware(['auth:admin', 'admin.permission:sales_invoices,can_update'])->group(function () {
        Route::get('sales/invoices/{id}/edit',   [\App\Http\Controllers\Admin\Sales\SalesInvoicesController::class, 'edit'])->name('sales_invoices.edit');
        Route::post('sales/invoices/{id}',       [\App\Http\Controllers\Admin\Sales\SalesInvoicesController::class, 'update'])->name('sales_invoices.update');
        Route::post('sales/invoices/{id}/cancel',[\App\Http\Controllers\Admin\Sales\SalesInvoicesController::class, 'cancel'])->name('sales_invoices.cancel');
    });
    Route::middleware(['auth:admin', 'admin.permission:sales_invoices,can_delete'])->group(function () {
        Route::get('sales/invoices/{id}/delete', [\App\Http\Controllers\Admin\Sales\SalesInvoicesController::class, 'delete'])->name('sales_invoices.delete');
    });

    // ── مدفوعات العملاء ──
    Route::middleware(['auth:admin', 'admin.permission:sales_payments,can_read'])->group(function () {
        Route::get('sales/payments',             [\App\Http\Controllers\Admin\Sales\SalesPaymentsController::class, 'index'])->name('sales_payments.index');
        Route::get('sales/payments/{id}',        [\App\Http\Controllers\Admin\Sales\SalesPaymentsController::class, 'show'])->where('id', '[0-9]+')->name('sales_payments.show');
    });
    Route::middleware(['auth:admin', 'admin.permission:sales_payments,can_create'])->group(function () {
        Route::get('sales/payments/create',      [\App\Http\Controllers\Admin\Sales\SalesPaymentsController::class, 'create'])->name('sales_payments.create');
        Route::post('sales/payments/store',      [\App\Http\Controllers\Admin\Sales\SalesPaymentsController::class, 'store'])->name('sales_payments.store');
    });
    Route::middleware(['auth:admin', 'admin.permission:sales_payments,can_delete'])->group(function () {
        Route::get('sales/payments/{id}/delete', [\App\Http\Controllers\Admin\Sales\SalesPaymentsController::class, 'delete'])->name('sales_payments.delete');
    });

    // ── مرتجعات البيع ──
    Route::middleware(['auth:admin', 'admin.permission:sales_returns,can_read'])->group(function () {
        Route::get('sales/returns',              [\App\Http\Controllers\Admin\Sales\SalesReturnsController::class, 'index'])->name('sales_returns.index');
        Route::get('sales/returns/{id}',         [\App\Http\Controllers\Admin\Sales\SalesReturnsController::class, 'show'])->where('id', '[0-9]+')->name('sales_returns.show');
    });
    Route::middleware(['auth:admin', 'admin.permission:sales_returns,can_create'])->group(function () {
        Route::get('sales/returns/create',       [\App\Http\Controllers\Admin\Sales\SalesReturnsController::class, 'create'])->name('sales_returns.create');
        Route::post('sales/returns/store',       [\App\Http\Controllers\Admin\Sales\SalesReturnsController::class, 'store'])->name('sales_returns.store');
    });
    Route::middleware(['auth:admin', 'admin.permission:sales_returns,can_update'])->group(function () {
        Route::post('sales/returns/{id}/approve',[\App\Http\Controllers\Admin\Sales\SalesReturnsController::class, 'approve'])->name('sales_returns.approve');
        Route::post('sales/returns/{id}/reject', [\App\Http\Controllers\Admin\Sales\SalesReturnsController::class, 'reject'])->name('sales_returns.reject');
    });
    Route::middleware(['auth:admin', 'admin.permission:sales_returns,can_delete'])->group(function () {
        Route::get('sales/returns/{id}/delete',  [\App\Http\Controllers\Admin\Sales\SalesReturnsController::class, 'delete'])->name('sales_returns.delete');
    });

    // ── تقارير المبيعات ──
    Route::middleware(['auth:admin', 'admin.permission:sales_reports,can_read'])->group(function () {
        Route::get('sales/reports',              [\App\Http\Controllers\Admin\Sales\SalesReportsController::class, 'index'])->name('sales_reports.index');
        Route::get('sales/reports/summary',      [\App\Http\Controllers\Admin\Sales\SalesReportsController::class, 'summary'])->name('sales_reports.summary');
        Route::get('sales/reports/customer',     [\App\Http\Controllers\Admin\Sales\SalesReportsController::class, 'byCustomer'])->name('sales_reports.customer');
        Route::get('sales/reports/item',         [\App\Http\Controllers\Admin\Sales\SalesReportsController::class, 'byItem'])->name('sales_reports.item');
        Route::get('sales/reports/debt',         [\App\Http\Controllers\Admin\Sales\SalesReportsController::class, 'debt'])->name('sales_reports.debt');
    });

    // ═════════════════════════════════════════════
    //  موديول المشتريات — Purchasing Module
    // ═════════════════════════════════════════════

    // ── الموردون ──
    Route::middleware(['auth:admin', 'admin.permission:suppliers,can_read'])->group(function () {
        Route::get('purchasing/suppliers',            [\App\Http\Controllers\Admin\Purchasing\SuppliersController::class, 'index'])->name('suppliers.index');
        Route::get('purchasing/suppliers/{id}',        [\App\Http\Controllers\Admin\Purchasing\SuppliersController::class, 'show'])->where('id', '[0-9]+')->name('suppliers.show');
    });
    Route::middleware(['auth:admin', 'admin.permission:suppliers,can_create'])->group(function () {
        Route::get('purchasing/suppliers/create',      [\App\Http\Controllers\Admin\Purchasing\SuppliersController::class, 'create'])->name('suppliers.create');
        Route::post('purchasing/suppliers/store',      [\App\Http\Controllers\Admin\Purchasing\SuppliersController::class, 'store'])->name('suppliers.store');
    });
    Route::middleware(['auth:admin', 'admin.permission:suppliers,can_update'])->group(function () {
        Route::get('purchasing/suppliers/{id}/edit',   [\App\Http\Controllers\Admin\Purchasing\SuppliersController::class, 'edit'])->name('suppliers.edit');
        Route::post('purchasing/suppliers/{id}',       [\App\Http\Controllers\Admin\Purchasing\SuppliersController::class, 'update'])->name('suppliers.update');
    });
    Route::middleware(['auth:admin', 'admin.permission:suppliers,can_delete'])->group(function () {
        Route::get('purchasing/suppliers/{id}/delete', [\App\Http\Controllers\Admin\Purchasing\SuppliersController::class, 'delete'])->name('suppliers.delete');
    });

    // ── طلبات الشراء ──
    Route::middleware(['auth:admin', 'admin.permission:purchase_requests,can_read'])->group(function () {
        Route::get('purchasing/requests',               [\App\Http\Controllers\Admin\Purchasing\PurchaseRequestsController::class, 'index'])->name('purchase_requests.index');
        Route::get('purchasing/requests/{id}',          [\App\Http\Controllers\Admin\Purchasing\PurchaseRequestsController::class, 'show'])->where('id', '[0-9]+')->name('purchase_requests.show');
    });
    Route::middleware(['auth:admin', 'admin.permission:purchase_requests,can_create'])->group(function () {
        Route::get('purchasing/requests/create',        [\App\Http\Controllers\Admin\Purchasing\PurchaseRequestsController::class, 'create'])->name('purchase_requests.create');
        Route::post('purchasing/requests/store',        [\App\Http\Controllers\Admin\Purchasing\PurchaseRequestsController::class, 'store'])->name('purchase_requests.store');
        Route::post('purchasing/requests/{id}/convert',  [\App\Http\Controllers\Admin\Purchasing\PurchaseRequestsController::class, 'convertToOrder'])->name('purchase_requests.convert');
    });
    Route::middleware(['auth:admin', 'admin.permission:purchase_requests,can_update'])->group(function () {
        Route::get('purchasing/requests/{id}/edit',     [\App\Http\Controllers\Admin\Purchasing\PurchaseRequestsController::class, 'edit'])->name('purchase_requests.edit');
        Route::post('purchasing/requests/{id}',         [\App\Http\Controllers\Admin\Purchasing\PurchaseRequestsController::class, 'update'])->name('purchase_requests.update');
        Route::post('purchasing/requests/{id}/status',  [\App\Http\Controllers\Admin\Purchasing\PurchaseRequestsController::class, 'updateStatus'])->name('purchase_requests.status');
    });
    Route::middleware(['auth:admin', 'admin.permission:purchase_requests,can_delete'])->group(function () {
        Route::get('purchasing/requests/{id}/delete',   [\App\Http\Controllers\Admin\Purchasing\PurchaseRequestsController::class, 'delete'])->name('purchase_requests.delete');
    });

    // ── أوامر الشراء ──
    Route::middleware(['auth:admin', 'admin.permission:purchase_orders,can_read'])->group(function () {
        Route::get('purchasing/orders',               [\App\Http\Controllers\Admin\Purchasing\PurchaseOrdersController::class, 'index'])->name('purchase_orders.index');
        Route::get('purchasing/orders/{id}',          [\App\Http\Controllers\Admin\Purchasing\PurchaseOrdersController::class, 'show'])->where('id', '[0-9]+')->name('purchase_orders.show');
        Route::get('purchasing/orders/{id}/print',    [\App\Http\Controllers\Admin\Purchasing\PurchaseOrdersController::class, 'print'])->name('purchase_orders.print');
    });
    Route::middleware(['auth:admin', 'admin.permission:purchase_orders,can_create'])->group(function () {
        Route::get('purchasing/orders/create',        [\App\Http\Controllers\Admin\Purchasing\PurchaseOrdersController::class, 'create'])->name('purchase_orders.create');
        Route::post('purchasing/orders/store',        [\App\Http\Controllers\Admin\Purchasing\PurchaseOrdersController::class, 'store'])->name('purchase_orders.store');
        Route::post('purchasing/orders/{id}/invoice', [\App\Http\Controllers\Admin\Purchasing\PurchaseOrdersController::class, 'createInvoice'])->name('purchase_orders.invoice');
    });
    Route::middleware(['auth:admin', 'admin.permission:purchase_orders,can_update'])->group(function () {
        Route::get('purchasing/orders/{id}/edit',     [\App\Http\Controllers\Admin\Purchasing\PurchaseOrdersController::class, 'edit'])->name('purchase_orders.edit');
        Route::post('purchasing/orders/{id}',         [\App\Http\Controllers\Admin\Purchasing\PurchaseOrdersController::class, 'update'])->name('purchase_orders.update');
        Route::post('purchasing/orders/{id}/status',  [\App\Http\Controllers\Admin\Purchasing\PurchaseOrdersController::class, 'updateStatus'])->name('purchase_orders.status');
    });
    Route::middleware(['auth:admin', 'admin.permission:purchase_orders,can_delete'])->group(function () {
        Route::get('purchasing/orders/{id}/delete',   [\App\Http\Controllers\Admin\Purchasing\PurchaseOrdersController::class, 'delete'])->name('purchase_orders.delete');
    });

    // ── فواتير الشراء ──
    Route::middleware(['auth:admin', 'admin.permission:purchase_invoices,can_read'])->group(function () {
        Route::get('purchasing/invoices',             [\App\Http\Controllers\Admin\Purchasing\PurchaseInvoicesController::class, 'index'])->name('purchase_invoices.index');
        Route::get('purchasing/invoices/{id}',        [\App\Http\Controllers\Admin\Purchasing\PurchaseInvoicesController::class, 'show'])->where('id', '[0-9]+')->name('purchase_invoices.show');
        Route::get('purchasing/invoices/{id}/print',  [\App\Http\Controllers\Admin\Purchasing\PurchaseInvoicesController::class, 'print'])->name('purchase_invoices.print');
    });
    Route::middleware(['auth:admin', 'admin.permission:purchase_invoices,can_create'])->group(function () {
        Route::get('purchasing/invoices/create',      [\App\Http\Controllers\Admin\Purchasing\PurchaseInvoicesController::class, 'create'])->name('purchase_invoices.create');
        Route::post('purchasing/invoices/store',      [\App\Http\Controllers\Admin\Purchasing\PurchaseInvoicesController::class, 'store'])->name('purchase_invoices.store');
    });
    Route::middleware(['auth:admin', 'admin.permission:purchase_invoices,can_update'])->group(function () {
        Route::get('purchasing/invoices/{id}/edit',   [\App\Http\Controllers\Admin\Purchasing\PurchaseInvoicesController::class, 'edit'])->name('purchase_invoices.edit');
        Route::post('purchasing/invoices/{id}',       [\App\Http\Controllers\Admin\Purchasing\PurchaseInvoicesController::class, 'update'])->name('purchase_invoices.update');
        Route::post('purchasing/invoices/{id}/cancel',[\App\Http\Controllers\Admin\Purchasing\PurchaseInvoicesController::class, 'cancel'])->name('purchase_invoices.cancel');
    });
    Route::middleware(['auth:admin', 'admin.permission:purchase_invoices,can_delete'])->group(function () {
        Route::get('purchasing/invoices/{id}/delete', [\App\Http\Controllers\Admin\Purchasing\PurchaseInvoicesController::class, 'delete'])->name('purchase_invoices.delete');
    });

    // ── مدفوعات الموردين ──
    Route::middleware(['auth:admin', 'admin.permission:purchase_payments,can_read'])->group(function () {
        Route::get('purchasing/payments',             [\App\Http\Controllers\Admin\Purchasing\PurchasePaymentsController::class, 'index'])->name('purchase_payments.index');
        Route::get('purchasing/payments/{id}',        [\App\Http\Controllers\Admin\Purchasing\PurchasePaymentsController::class, 'show'])->where('id', '[0-9]+')->name('purchase_payments.show');
    });
    Route::middleware(['auth:admin', 'admin.permission:purchase_payments,can_create'])->group(function () {
        Route::get('purchasing/payments/create',      [\App\Http\Controllers\Admin\Purchasing\PurchasePaymentsController::class, 'create'])->name('purchase_payments.create');
        Route::post('purchasing/payments/store',      [\App\Http\Controllers\Admin\Purchasing\PurchasePaymentsController::class, 'store'])->name('purchase_payments.store');
    });
    Route::middleware(['auth:admin', 'admin.permission:purchase_payments,can_delete'])->group(function () {
        Route::get('purchasing/payments/{id}/delete', [\App\Http\Controllers\Admin\Purchasing\PurchasePaymentsController::class, 'delete'])->name('purchase_payments.delete');
    });

    // ── مرتجعات الشراء ──
    Route::middleware(['auth:admin', 'admin.permission:purchase_returns,can_read'])->group(function () {
        Route::get('purchasing/returns',              [\App\Http\Controllers\Admin\Purchasing\PurchaseReturnsController::class, 'index'])->name('purchase_returns.index');
        Route::get('purchasing/returns/{id}',         [\App\Http\Controllers\Admin\Purchasing\PurchaseReturnsController::class, 'show'])->where('id', '[0-9]+')->name('purchase_returns.show');
    });
    Route::middleware(['auth:admin', 'admin.permission:purchase_returns,can_create'])->group(function () {
        Route::get('purchasing/returns/create',       [\App\Http\Controllers\Admin\Purchasing\PurchaseReturnsController::class, 'create'])->name('purchase_returns.create');
        Route::post('purchasing/returns/store',       [\App\Http\Controllers\Admin\Purchasing\PurchaseReturnsController::class, 'store'])->name('purchase_returns.store');
    });
    Route::middleware(['auth:admin', 'admin.permission:purchase_returns,can_update'])->group(function () {
        Route::post('purchasing/returns/{id}/approve',[\App\Http\Controllers\Admin\Purchasing\PurchaseReturnsController::class, 'approve'])->name('purchase_returns.approve');
        Route::post('purchasing/returns/{id}/reject', [\App\Http\Controllers\Admin\Purchasing\PurchaseReturnsController::class, 'reject'])->name('purchase_returns.reject');
    });
    Route::middleware(['auth:admin', 'admin.permission:purchase_returns,can_delete'])->group(function () {
        Route::get('purchasing/returns/{id}/delete',  [\App\Http\Controllers\Admin\Purchasing\PurchaseReturnsController::class, 'delete'])->name('purchase_returns.delete');
    });

    // ── تقارير المشتريات ──
    Route::middleware(['auth:admin', 'admin.permission:purchase_reports,can_read'])->group(function () {
        Route::get('purchasing/reports',              [\App\Http\Controllers\Admin\Purchasing\PurchaseReportsController::class, 'index'])->name('purchase_reports.index');
        Route::get('purchasing/reports/summary',      [\App\Http\Controllers\Admin\Purchasing\PurchaseReportsController::class, 'summary'])->name('purchase_reports.summary');
        Route::get('purchasing/reports/supplier',     [\App\Http\Controllers\Admin\Purchasing\PurchaseReportsController::class, 'bySupplier'])->name('purchase_reports.supplier');
        Route::get('purchasing/reports/item',         [\App\Http\Controllers\Admin\Purchasing\PurchaseReportsController::class, 'byItem'])->name('purchase_reports.item');
        Route::get('purchasing/reports/debt',         [\App\Http\Controllers\Admin\Purchasing\PurchaseReportsController::class, 'debt'])->name('purchase_reports.debt');
    });

    // ═════════════════════════════════════════════
    //  موديول المخازن — Inventory Module
    // ═════════════════════════════════════════════

    // ── المخازن ──
    Route::middleware(['auth:admin', 'admin.permission:warehouses,can_read'])->group(function () {
        Route::get('inventory/warehouses',            [\App\Http\Controllers\Admin\Inventory\WarehousesController::class, 'index'])->name('warehouses.index');
    });
    Route::middleware(['auth:admin', 'admin.permission:warehouses,can_create'])->group(function () {
        Route::get('inventory/warehouses/create',     [\App\Http\Controllers\Admin\Inventory\WarehousesController::class, 'create'])->name('warehouses.create');
        Route::post('inventory/warehouses/store',     [\App\Http\Controllers\Admin\Inventory\WarehousesController::class, 'store'])->name('warehouses.store');
    });
    Route::middleware(['auth:admin', 'admin.permission:warehouses,can_update'])->group(function () {
        Route::get('inventory/warehouses/{id}/edit',  [\App\Http\Controllers\Admin\Inventory\WarehousesController::class, 'edit'])->name('warehouses.edit');
        Route::post('inventory/warehouses/{id}',      [\App\Http\Controllers\Admin\Inventory\WarehousesController::class, 'update'])->name('warehouses.update');
    });
    Route::middleware(['auth:admin', 'admin.permission:warehouses,can_delete'])->group(function () {
        Route::get('inventory/warehouses/{id}/delete',[\App\Http\Controllers\Admin\Inventory\WarehousesController::class, 'delete'])->name('warehouses.delete');
    });

    // ── أرصدة المخزون ──
    Route::middleware(['auth:admin', 'admin.permission:stock_levels,can_read'])->group(function () {
        Route::get('inventory/stock',                 [\App\Http\Controllers\Admin\Inventory\StockLevelsController::class, 'index'])->name('stock_levels.index');
        Route::get('inventory/stock/{itemId}',        [\App\Http\Controllers\Admin\Inventory\StockLevelsController::class, 'show'])->name('stock_levels.show');
    });

    // ── حركة الأصناف ──
    Route::middleware(['auth:admin', 'admin.permission:stock_movements,can_read'])->group(function () {
        Route::get('inventory/movements',             [\App\Http\Controllers\Admin\Inventory\StockMovementsController::class, 'index'])->name('stock_movements.index');
    });

    // ── تسويات المخزون ──
    Route::middleware(['auth:admin', 'admin.permission:stock_adjustments,can_read'])->group(function () {
        Route::get('inventory/adjustments',           [\App\Http\Controllers\Admin\Inventory\StockAdjustmentsController::class, 'index'])->name('stock_adjustments.index');
        Route::get('inventory/adjustments/{id}',       [\App\Http\Controllers\Admin\Inventory\StockAdjustmentsController::class, 'show'])->where('id', '[0-9]+')->name('stock_adjustments.show');
    });
    Route::middleware(['auth:admin', 'admin.permission:stock_adjustments,can_create'])->group(function () {
        Route::get('inventory/adjustments/create',     [\App\Http\Controllers\Admin\Inventory\StockAdjustmentsController::class, 'create'])->name('stock_adjustments.create');
        Route::post('inventory/adjustments/store',     [\App\Http\Controllers\Admin\Inventory\StockAdjustmentsController::class, 'store'])->name('stock_adjustments.store');
    });
    Route::middleware(['auth:admin', 'admin.permission:stock_adjustments,can_update'])->group(function () {
        Route::post('inventory/adjustments/{id}/approve', [\App\Http\Controllers\Admin\Inventory\StockAdjustmentsController::class, 'approve'])->name('stock_adjustments.approve');
        Route::post('inventory/adjustments/{id}/reject',  [\App\Http\Controllers\Admin\Inventory\StockAdjustmentsController::class, 'reject'])->name('stock_adjustments.reject');
    });
    Route::middleware(['auth:admin', 'admin.permission:stock_adjustments,can_delete'])->group(function () {
        Route::get('inventory/adjustments/{id}/delete',[\App\Http\Controllers\Admin\Inventory\StockAdjustmentsController::class, 'delete'])->name('stock_adjustments.delete');
    });

    // ── تحويلات المخازن ──
    Route::middleware(['auth:admin', 'admin.permission:stock_transfers,can_read'])->group(function () {
        Route::get('inventory/transfers',              [\App\Http\Controllers\Admin\Inventory\StockTransfersController::class, 'index'])->name('stock_transfers.index');
        Route::get('inventory/transfers/{id}',         [\App\Http\Controllers\Admin\Inventory\StockTransfersController::class, 'show'])->where('id', '[0-9]+')->name('stock_transfers.show');
    });
    Route::middleware(['auth:admin', 'admin.permission:stock_transfers,can_create'])->group(function () {
        Route::get('inventory/transfers/create',       [\App\Http\Controllers\Admin\Inventory\StockTransfersController::class, 'create'])->name('stock_transfers.create');
        Route::post('inventory/transfers/store',       [\App\Http\Controllers\Admin\Inventory\StockTransfersController::class, 'store'])->name('stock_transfers.store');
    });
    Route::middleware(['auth:admin', 'admin.permission:stock_transfers,can_update'])->group(function () {
        Route::post('inventory/transfers/{id}/complete', [\App\Http\Controllers\Admin\Inventory\StockTransfersController::class, 'complete'])->name('stock_transfers.complete');
        Route::post('inventory/transfers/{id}/cancel',   [\App\Http\Controllers\Admin\Inventory\StockTransfersController::class, 'cancel'])->name('stock_transfers.cancel');
    });
    Route::middleware(['auth:admin', 'admin.permission:stock_transfers,can_delete'])->group(function () {
        Route::get('inventory/transfers/{id}/delete',  [\App\Http\Controllers\Admin\Inventory\StockTransfersController::class, 'delete'])->name('stock_transfers.delete');
    });

    // ── تقارير المخازن ──
    Route::middleware(['auth:admin', 'admin.permission:inventory_reports,can_read'])->group(function () {
        Route::get('inventory/reports',                [\App\Http\Controllers\Admin\Inventory\InventoryReportsController::class, 'index'])->name('inventory_reports.index');
        Route::get('inventory/reports/valuation',      [\App\Http\Controllers\Admin\Inventory\InventoryReportsController::class, 'valuation'])->name('inventory_reports.valuation');
        Route::get('inventory/reports/low-stock',      [\App\Http\Controllers\Admin\Inventory\InventoryReportsController::class, 'lowStock'])->name('inventory_reports.low_stock');
        Route::get('inventory/reports/movements-summary', [\App\Http\Controllers\Admin\Inventory\InventoryReportsController::class, 'movementsSummary'])->name('inventory_reports.movements_summary');
    });

    // ═════════════════════════════════════════════
    //  موديول المحاسبة — Accounting Module
    // ═════════════════════════════════════════════

    // ── دليل الحسابات ──
    Route::middleware(['auth:admin', 'admin.permission:chart_of_accounts,can_read'])->group(function () {
        Route::get('accounting/accounts',            [\App\Http\Controllers\Admin\Accounting\ChartOfAccountsController::class, 'index'])->name('chart_of_accounts.index');
    });
    Route::middleware(['auth:admin', 'admin.permission:chart_of_accounts,can_create'])->group(function () {
        Route::get('accounting/accounts/create',     [\App\Http\Controllers\Admin\Accounting\ChartOfAccountsController::class, 'create'])->name('chart_of_accounts.create');
        Route::post('accounting/accounts/store',     [\App\Http\Controllers\Admin\Accounting\ChartOfAccountsController::class, 'store'])->name('chart_of_accounts.store');
    });
    Route::middleware(['auth:admin', 'admin.permission:chart_of_accounts,can_update'])->group(function () {
        Route::get('accounting/accounts/{id}/edit',  [\App\Http\Controllers\Admin\Accounting\ChartOfAccountsController::class, 'edit'])->name('chart_of_accounts.edit');
        Route::post('accounting/accounts/{id}',      [\App\Http\Controllers\Admin\Accounting\ChartOfAccountsController::class, 'update'])->name('chart_of_accounts.update');
    });
    Route::middleware(['auth:admin', 'admin.permission:chart_of_accounts,can_delete'])->group(function () {
        Route::get('accounting/accounts/{id}/delete',[\App\Http\Controllers\Admin\Accounting\ChartOfAccountsController::class, 'delete'])->name('chart_of_accounts.delete');
    });

    // ── مراكز التكلفة ──
    Route::middleware(['auth:admin', 'admin.permission:cost_centers,can_read'])->group(function () {
        Route::get('accounting/cost-centers',            [\App\Http\Controllers\Admin\Accounting\CostCentersController::class, 'index'])->name('cost_centers.index');
    });
    Route::middleware(['auth:admin', 'admin.permission:cost_centers,can_create'])->group(function () {
        Route::get('accounting/cost-centers/create',     [\App\Http\Controllers\Admin\Accounting\CostCentersController::class, 'create'])->name('cost_centers.create');
        Route::post('accounting/cost-centers/store',     [\App\Http\Controllers\Admin\Accounting\CostCentersController::class, 'store'])->name('cost_centers.store');
    });
    Route::middleware(['auth:admin', 'admin.permission:cost_centers,can_update'])->group(function () {
        Route::get('accounting/cost-centers/{id}/edit',  [\App\Http\Controllers\Admin\Accounting\CostCentersController::class, 'edit'])->name('cost_centers.edit');
        Route::post('accounting/cost-centers/{id}',      [\App\Http\Controllers\Admin\Accounting\CostCentersController::class, 'update'])->name('cost_centers.update');
    });
    Route::middleware(['auth:admin', 'admin.permission:cost_centers,can_delete'])->group(function () {
        Route::get('accounting/cost-centers/{id}/delete',[\App\Http\Controllers\Admin\Accounting\CostCentersController::class, 'delete'])->name('cost_centers.delete');
    });

    // ── القيود اليومية ──
    Route::middleware(['auth:admin', 'admin.permission:journal_entries,can_read'])->group(function () {
        Route::get('accounting/journal-entries',        [\App\Http\Controllers\Admin\Accounting\JournalEntriesController::class, 'index'])->name('journal_entries.index');
        Route::get('accounting/journal-entries/{id}',   [\App\Http\Controllers\Admin\Accounting\JournalEntriesController::class, 'show'])->where('id', '[0-9]+')->name('journal_entries.show');
    });
    Route::middleware(['auth:admin', 'admin.permission:journal_entries,can_create'])->group(function () {
        Route::get('accounting/journal-entries/create', [\App\Http\Controllers\Admin\Accounting\JournalEntriesController::class, 'create'])->name('journal_entries.create');
        Route::post('accounting/journal-entries/store', [\App\Http\Controllers\Admin\Accounting\JournalEntriesController::class, 'store'])->name('journal_entries.store');
    });
    Route::middleware(['auth:admin', 'admin.permission:journal_entries,can_update'])->group(function () {
        Route::post('accounting/journal-entries/{id}/reverse', [\App\Http\Controllers\Admin\Accounting\JournalEntriesController::class, 'reverse'])->name('journal_entries.reverse');
    });

    // ── الفترات المحاسبية ──
    Route::middleware(['auth:admin', 'admin.permission:accounting_periods,can_read'])->group(function () {
        Route::get('accounting/periods',              [\App\Http\Controllers\Admin\Accounting\AccountingPeriodsController::class, 'index'])->name('accounting_periods.index');
    });
    Route::middleware(['auth:admin', 'admin.permission:accounting_periods,can_create'])->group(function () {
        Route::post('accounting/periods/generate',    [\App\Http\Controllers\Admin\Accounting\AccountingPeriodsController::class, 'generate'])->name('accounting_periods.generate');
    });
    Route::middleware(['auth:admin', 'admin.permission:accounting_periods,can_update'])->group(function () {
        Route::post('accounting/periods/{id}/close',  [\App\Http\Controllers\Admin\Accounting\AccountingPeriodsController::class, 'close'])->name('accounting_periods.close');
        Route::post('accounting/periods/{id}/reopen', [\App\Http\Controllers\Admin\Accounting\AccountingPeriodsController::class, 'reopen'])->name('accounting_periods.reopen');
    });

    // ── إعدادات الترحيل التلقائي ──
    Route::middleware(['auth:admin', 'admin.permission:gl_posting_rules,can_read'])->group(function () {
        Route::get('accounting/posting-rules',        [\App\Http\Controllers\Admin\Accounting\GlPostingRulesController::class, 'index'])->name('gl_posting_rules.index');
    });
    Route::middleware(['auth:admin', 'admin.permission:gl_posting_rules,can_update'])->group(function () {
        Route::post('accounting/posting-rules',       [\App\Http\Controllers\Admin\Accounting\GlPostingRulesController::class, 'update'])->name('gl_posting_rules.update');
    });

    // ── التقارير المالية ──
    Route::middleware(['auth:admin', 'admin.permission:accounting_reports,can_read'])->group(function () {
        Route::get('accounting/reports',                   [\App\Http\Controllers\Admin\Accounting\AccountingReportsController::class, 'index'])->name('accounting_reports.index');
        Route::get('accounting/reports/trial-balance',     [\App\Http\Controllers\Admin\Accounting\AccountingReportsController::class, 'trialBalance'])->name('accounting_reports.trial_balance');
        Route::get('accounting/reports/income-statement',  [\App\Http\Controllers\Admin\Accounting\AccountingReportsController::class, 'incomeStatement'])->name('accounting_reports.income_statement');
        Route::get('accounting/reports/balance-sheet',     [\App\Http\Controllers\Admin\Accounting\AccountingReportsController::class, 'balanceSheet'])->name('accounting_reports.balance_sheet');
        Route::get('accounting/reports/ledger',            [\App\Http\Controllers\Admin\Accounting\AccountingReportsController::class, 'ledgerDetail'])->name('accounting_reports.ledger');
    });

    // ═════════════════════════════════════════════
    //  موديول الخزينة — Treasury Module
    // ═════════════════════════════════════════════

    // ── الخزائن النقدية ──
    Route::middleware(['auth:admin', 'admin.permission:cash_boxes,can_read'])->group(function () {
        Route::get('treasury/cash-boxes',            [\App\Http\Controllers\Admin\Treasury\CashBoxesController::class, 'index'])->name('cash_boxes.index');
    });
    Route::middleware(['auth:admin', 'admin.permission:cash_boxes,can_create'])->group(function () {
        Route::get('treasury/cash-boxes/create',     [\App\Http\Controllers\Admin\Treasury\CashBoxesController::class, 'create'])->name('cash_boxes.create');
        Route::post('treasury/cash-boxes/store',     [\App\Http\Controllers\Admin\Treasury\CashBoxesController::class, 'store'])->name('cash_boxes.store');
    });
    Route::middleware(['auth:admin', 'admin.permission:cash_boxes,can_update'])->group(function () {
        Route::get('treasury/cash-boxes/{id}/edit',  [\App\Http\Controllers\Admin\Treasury\CashBoxesController::class, 'edit'])->where('id', '[0-9]+')->name('cash_boxes.edit');
        Route::post('treasury/cash-boxes/{id}',      [\App\Http\Controllers\Admin\Treasury\CashBoxesController::class, 'update'])->where('id', '[0-9]+')->name('cash_boxes.update');
    });
    Route::middleware(['auth:admin', 'admin.permission:cash_boxes,can_delete'])->group(function () {
        Route::get('treasury/cash-boxes/{id}/delete',[\App\Http\Controllers\Admin\Treasury\CashBoxesController::class, 'delete'])->where('id', '[0-9]+')->name('cash_boxes.delete');
    });

    // ── الحسابات البنكية ──
    Route::middleware(['auth:admin', 'admin.permission:bank_accounts,can_read'])->group(function () {
        Route::get('treasury/bank-accounts',            [\App\Http\Controllers\Admin\Treasury\BankAccountsController::class, 'index'])->name('bank_accounts.index');
    });
    Route::middleware(['auth:admin', 'admin.permission:bank_accounts,can_create'])->group(function () {
        Route::get('treasury/bank-accounts/create',     [\App\Http\Controllers\Admin\Treasury\BankAccountsController::class, 'create'])->name('bank_accounts.create');
        Route::post('treasury/bank-accounts/store',     [\App\Http\Controllers\Admin\Treasury\BankAccountsController::class, 'store'])->name('bank_accounts.store');
    });
    Route::middleware(['auth:admin', 'admin.permission:bank_accounts,can_update'])->group(function () {
        Route::get('treasury/bank-accounts/{id}/edit',  [\App\Http\Controllers\Admin\Treasury\BankAccountsController::class, 'edit'])->where('id', '[0-9]+')->name('bank_accounts.edit');
        Route::post('treasury/bank-accounts/{id}',      [\App\Http\Controllers\Admin\Treasury\BankAccountsController::class, 'update'])->where('id', '[0-9]+')->name('bank_accounts.update');
    });
    Route::middleware(['auth:admin', 'admin.permission:bank_accounts,can_delete'])->group(function () {
        Route::get('treasury/bank-accounts/{id}/delete',[\App\Http\Controllers\Admin\Treasury\BankAccountsController::class, 'delete'])->where('id', '[0-9]+')->name('bank_accounts.delete');
    });

    // ── سندات القبض ──
    Route::middleware(['auth:admin', 'admin.permission:treasury_receipts,can_read'])->group(function () {
        Route::get('treasury/receipts',            [\App\Http\Controllers\Admin\Treasury\ReceiptVouchersController::class, 'index'])->name('treasury_receipts.index');
        Route::get('treasury/receipts/{id}',       [\App\Http\Controllers\Admin\Treasury\ReceiptVouchersController::class, 'show'])->where('id', '[0-9]+')->name('treasury_receipts.show');
    });
    Route::middleware(['auth:admin', 'admin.permission:treasury_receipts,can_create'])->group(function () {
        Route::get('treasury/receipts/create',     [\App\Http\Controllers\Admin\Treasury\ReceiptVouchersController::class, 'create'])->name('treasury_receipts.create');
        Route::post('treasury/receipts/store',     [\App\Http\Controllers\Admin\Treasury\ReceiptVouchersController::class, 'store'])->name('treasury_receipts.store');
    });

    // ── سندات الصرف ──
    Route::middleware(['auth:admin', 'admin.permission:treasury_payments,can_read'])->group(function () {
        Route::get('treasury/payments',            [\App\Http\Controllers\Admin\Treasury\PaymentVouchersController::class, 'index'])->name('treasury_payments.index');
        Route::get('treasury/payments/{id}',       [\App\Http\Controllers\Admin\Treasury\PaymentVouchersController::class, 'show'])->where('id', '[0-9]+')->name('treasury_payments.show');
    });
    Route::middleware(['auth:admin', 'admin.permission:treasury_payments,can_create'])->group(function () {
        Route::get('treasury/payments/create',     [\App\Http\Controllers\Admin\Treasury\PaymentVouchersController::class, 'create'])->name('treasury_payments.create');
        Route::post('treasury/payments/store',     [\App\Http\Controllers\Admin\Treasury\PaymentVouchersController::class, 'store'])->name('treasury_payments.store');
    });

    // ── الشيكات ──
    Route::middleware(['auth:admin', 'admin.permission:cheques,can_read'])->group(function () {
        Route::get('treasury/cheques',             [\App\Http\Controllers\Admin\Treasury\ChequesController::class, 'index'])->name('cheques.index');
        Route::get('treasury/cheques/{id}',        [\App\Http\Controllers\Admin\Treasury\ChequesController::class, 'show'])->where('id', '[0-9]+')->name('cheques.show');
    });
    Route::middleware(['auth:admin', 'admin.permission:cheques,can_update'])->group(function () {
        Route::post('treasury/cheques/{id}/collect', [\App\Http\Controllers\Admin\Treasury\ChequesController::class, 'collect'])->where('id', '[0-9]+')->name('cheques.collect');
        Route::post('treasury/cheques/{id}/bounce',  [\App\Http\Controllers\Admin\Treasury\ChequesController::class, 'bounce'])->where('id', '[0-9]+')->name('cheques.bounce');
    });

    // ── تقارير الخزينة ──
    Route::middleware(['auth:admin', 'admin.permission:treasury_reports,can_read'])->group(function () {
        Route::get('treasury/reports',             [\App\Http\Controllers\Admin\Treasury\TreasuryReportsController::class, 'index'])->name('treasury_reports.index');
        Route::get('treasury/reports/cheques-due', [\App\Http\Controllers\Admin\Treasury\TreasuryReportsController::class, 'chequesDue'])->name('treasury_reports.cheques_due');
    });

    // ═════════════════════════════════════════════
    //  موديول الأصول الثابتة — Fixed Assets Module
    // ═════════════════════════════════════════════

    // ── فئات الأصول ──
    Route::middleware(['auth:admin', 'admin.permission:asset_categories,can_read'])->group(function () {
        Route::get('assets/categories',            [\App\Http\Controllers\Admin\Assets\AssetCategoriesController::class, 'index'])->name('asset_categories.index');
    });
    Route::middleware(['auth:admin', 'admin.permission:asset_categories,can_create'])->group(function () {
        Route::get('assets/categories/create',     [\App\Http\Controllers\Admin\Assets\AssetCategoriesController::class, 'create'])->name('asset_categories.create');
        Route::post('assets/categories/store',     [\App\Http\Controllers\Admin\Assets\AssetCategoriesController::class, 'store'])->name('asset_categories.store');
    });
    Route::middleware(['auth:admin', 'admin.permission:asset_categories,can_update'])->group(function () {
        Route::get('assets/categories/{id}/edit',  [\App\Http\Controllers\Admin\Assets\AssetCategoriesController::class, 'edit'])->where('id', '[0-9]+')->name('asset_categories.edit');
        Route::post('assets/categories/{id}',      [\App\Http\Controllers\Admin\Assets\AssetCategoriesController::class, 'update'])->where('id', '[0-9]+')->name('asset_categories.update');
    });
    Route::middleware(['auth:admin', 'admin.permission:asset_categories,can_delete'])->group(function () {
        Route::get('assets/categories/{id}/delete',[\App\Http\Controllers\Admin\Assets\AssetCategoriesController::class, 'delete'])->where('id', '[0-9]+')->name('asset_categories.delete');
    });

    // ── الأصول الثابتة ──
    Route::middleware(['auth:admin', 'admin.permission:fixed_assets,can_read'])->group(function () {
        Route::get('assets/fixed-assets',            [\App\Http\Controllers\Admin\Assets\FixedAssetsController::class, 'index'])->name('fixed_assets.index');
        Route::get('assets/fixed-assets/{id}',       [\App\Http\Controllers\Admin\Assets\FixedAssetsController::class, 'show'])->where('id', '[0-9]+')->name('fixed_assets.show');
    });
    Route::middleware(['auth:admin', 'admin.permission:fixed_assets,can_create'])->group(function () {
        Route::get('assets/fixed-assets/create',     [\App\Http\Controllers\Admin\Assets\FixedAssetsController::class, 'create'])->name('fixed_assets.create');
        Route::post('assets/fixed-assets/store',     [\App\Http\Controllers\Admin\Assets\FixedAssetsController::class, 'store'])->name('fixed_assets.store');
    });
    Route::middleware(['auth:admin', 'admin.permission:fixed_assets,can_update'])->group(function () {
        Route::get('assets/fixed-assets/{id}/edit',      [\App\Http\Controllers\Admin\Assets\FixedAssetsController::class, 'edit'])->where('id', '[0-9]+')->name('fixed_assets.edit');
        Route::post('assets/fixed-assets/{id}',          [\App\Http\Controllers\Admin\Assets\FixedAssetsController::class, 'update'])->where('id', '[0-9]+')->name('fixed_assets.update');
        Route::post('assets/fixed-assets/{id}/dispose',  [\App\Http\Controllers\Admin\Assets\FixedAssetsController::class, 'dispose'])->where('id', '[0-9]+')->name('fixed_assets.dispose');
        Route::post('assets/fixed-assets/{id}/transfer', [\App\Http\Controllers\Admin\Assets\FixedAssetsController::class, 'transfer'])->where('id', '[0-9]+')->name('fixed_assets.transfer');
    });

    // ── إهلاك الأصول ──
    Route::middleware(['auth:admin', 'admin.permission:asset_depreciation,can_read'])->group(function () {
        Route::get('assets/depreciation',         [\App\Http\Controllers\Admin\Assets\DepreciationRunController::class, 'form'])->name('asset_depreciation.form');
        Route::get('assets/depreciation/history', [\App\Http\Controllers\Admin\Assets\DepreciationRunController::class, 'history'])->name('asset_depreciation.history');
    });
    Route::middleware(['auth:admin', 'admin.permission:asset_depreciation,can_create'])->group(function () {
        Route::post('assets/depreciation/run',    [\App\Http\Controllers\Admin\Assets\DepreciationRunController::class, 'run'])->name('asset_depreciation.run');
    });

    // ── تقارير الأصول ──
    Route::middleware(['auth:admin', 'admin.permission:asset_reports,can_read'])->group(function () {
        Route::get('assets/reports',                       [\App\Http\Controllers\Admin\Assets\AssetReportsController::class, 'index'])->name('asset_reports.index');
        Route::get('assets/reports/register',              [\App\Http\Controllers\Admin\Assets\AssetReportsController::class, 'register'])->name('asset_reports.register');
        Route::get('assets/reports/{id}/schedule',          [\App\Http\Controllers\Admin\Assets\AssetReportsController::class, 'depreciationSchedule'])->where('id', '[0-9]+')->name('asset_reports.schedule');
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

