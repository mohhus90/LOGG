<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AdminPermission;
use App\Models\AdminModule;

/**
 * CheckAdminPermission Middleware
 *
 * الاستخدام في routes:
 *   ->middleware('admin.permission:employees,can_read')
 *   ->middleware('admin.permission:employees,can_create')
 *   ->middleware('admin.permission:employees,can_delete')
 *
 * السوبر أدمن (is_super_admin=1) يتجاوز جميع الصلاحيات تلقائيًا
 */
class CheckAdminPermission
{
    public function handle(Request $request, Closure $next, string $module, string $permission = 'can_read')
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('admin.dashboard.login');
        }

        // السوبر أدمن يملك كل الصلاحيات
        if ($admin->is_super_admin == 1) {
            return $next($request);
        }

        // البحث عن القسم
        $moduleRecord = AdminModule::where('module_key', $module)->first();
        if (!$moduleRecord) {
            abort(403, 'القسم غير موجود');
        }

        // البحث عن صلاحية الأدمن
        $perm = AdminPermission::where('admin_id', $admin->id)
            ->where('module_id', $moduleRecord->id)
            ->first();

        if (!$perm || !$perm->{$permission}) {
            if ($request->ajax()) {
                return response()->json(['error' => 'غير مصرح لك بهذه العملية', 'permission' => $permission], 403);
            }
            abort(403, 'ليس لديك صلاحية للقيام بهذه العملية');
        }

        return $next($request);
    }
}
