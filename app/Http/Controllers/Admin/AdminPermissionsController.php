<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\AdminModule;
use App\Models\AdminPermission;
use Illuminate\Support\Facades\Auth;

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
     * عرض وتعديل صلاحيات أدمن معين
     */
    public function edit(int $adminId)
    {
        $this->authorizeSuperAdmin();

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