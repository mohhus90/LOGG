<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\AdminModule;
use App\Models\AdminPermission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * إدارة صلاحيات المستخدمين (الأدمنات)
 * متاح فقط للسوبر أدمن
 */
class AdminPermissionsController extends Controller
{
    /**
     * عرض قائمة الأدمنات لاختيار من تعديل صلاحياته
     */
    public function index()
    {
        $this->authorizeSuperAdmin();

        $admins = Admin::where('id', '!=', Auth::guard('admin')->id())
            ->where('com_code', Auth::guard('admin')->user()->com_code)
            ->get();

        return view('admin.permissions.index', compact('admins'));
    }

    /**
     * يضمن وجود الوحدات الأساسية في قاعدة البيانات
     */
    private function ensureModulesSeeded(): void
    {
        if (AdminModule::count() > 0) return;

        $modules = [
            ['module_key' => 'general_settings',  'module_name' => 'الضبط العام',               'module_icon' => 'fas fa-cog',              'sort_order' => 1],
            ['module_key' => 'branches',           'module_name' => 'الفروع',                    'module_icon' => 'fas fa-code-branch',      'sort_order' => 2],
            ['module_key' => 'shifts',             'module_name' => 'الشيفتات',                  'module_icon' => 'fas fa-clock',            'sort_order' => 3],
            ['module_key' => 'departments',        'module_name' => 'الإدارات',                  'module_icon' => 'fas fa-building',         'sort_order' => 4],
            ['module_key' => 'jobs_categories',    'module_name' => 'الوظائف',                   'module_icon' => 'fas fa-briefcase',        'sort_order' => 5],
            ['module_key' => 'employees',          'module_name' => 'الموظفين',                  'module_icon' => 'fas fa-users',            'sort_order' => 6],
            ['module_key' => 'attendance',         'module_name' => 'الحضور والانصراف',          'module_icon' => 'fas fa-fingerprint',      'sort_order' => 7],
            ['module_key' => 'advances',           'module_name' => 'السلف',                     'module_icon' => 'fas fa-hand-holding-usd', 'sort_order' => 8],
            ['module_key' => 'commissions',        'module_name' => 'العمولات',                  'module_icon' => 'fas fa-percentage',       'sort_order' => 9],
            ['module_key' => 'deductions',         'module_name' => 'الخصومات',                  'module_icon' => 'fas fa-minus-circle',     'sort_order' => 10],
            ['module_key' => 'payroll',            'module_name' => 'مسير الرواتب',              'module_icon' => 'fas fa-money-check-alt',  'sort_order' => 11],
            ['module_key' => 'finance_calender',   'module_name' => 'السنوات المالية',           'module_icon' => 'fas fa-calendar-alt',     'sort_order' => 12],
            ['module_key' => 'vacations_balance',  'module_name' => 'الرصيد السنوي للإجازات',   'module_icon' => 'fas fa-umbrella-beach',   'sort_order' => 13],
            ['module_key' => 'admin_permissions',  'module_name' => 'صلاحيات المستخدمين',       'module_icon' => 'fas fa-user-shield',      'sort_order' => 14],
        ];

        foreach ($modules as $module) {
            DB::table('admin_modules')->updateOrInsert(
                ['module_key' => $module['module_key']],
                array_merge($module, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    /**
     * عرض وتعديل صلاحيات أدمن معين
     */
    public function edit(int $adminId)
    {
        $this->authorizeSuperAdmin();
        $this->ensureModulesSeeded();

        $targetAdmin = Admin::findOrFail($adminId);
        $modules     = AdminModule::orderBy('sort_order')->get();

        // جلب الصلاحيات الحالية لهذا الأدمن مع indexing بـ module_id
        $existingPerms = AdminPermission::where('admin_id', $adminId)
            ->get()
            ->keyBy('module_id');

        return view('admin.permissions.edit', compact('targetAdmin', 'modules', 'existingPerms'));
    }

    /**
     * حفظ الصلاحيات
     */
    public function update(Request $request, int $adminId)
    {
        $this->authorizeSuperAdmin();

        $targetAdmin = Admin::findOrFail($adminId);
        $com_code    = Auth::guard('admin')->user()->com_code;
        $permissions = $request->input('permissions', []);

        $modules = AdminModule::all();

        foreach ($modules as $module) {
            $modulePerms = $permissions[$module->id] ?? [];

            AdminPermission::updateOrCreate(
                ['admin_id' => $adminId, 'module_id' => $module->id],
                [
                    'can_read'   => isset($modulePerms['can_read'])   ? 1 : 0,
                    'can_create' => isset($modulePerms['can_create']) ? 1 : 0,
                    'can_update' => isset($modulePerms['can_update']) ? 1 : 0,
                    'can_delete' => isset($modulePerms['can_delete']) ? 1 : 0,
                    'com_code'   => $com_code,
                ]
            );
        }

        return redirect()->route('admin.permissions.index')
            ->with('success', 'تم حفظ الصلاحيات بنجاح للأدمن: ' . $targetAdmin->name);
    }

    /**
     * تحديد صلاحية الوصول - السوبر أدمن فقط
     */
    private function authorizeSuperAdmin(): void
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->is_super_admin != 1) {
            abort(403, 'هذه الصفحة متاحة للسوبر أدمن فقط');
        }
    }
}
