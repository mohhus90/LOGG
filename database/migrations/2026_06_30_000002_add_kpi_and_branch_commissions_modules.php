<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now = now();

        // ── إضافة وحدة KPI ──
        DB::table('admin_modules')->updateOrInsert(
            ['module_key' => 'kpi'],
            ['module_key' => 'kpi', 'module_name' => 'مؤشرات الأداء', 'module_icon' => 'fas fa-chart-line', 'sort_order' => 12, 'created_at' => $now, 'updated_at' => $now]
        );

        // ── إضافة وحدة عمولات الفروع ──
        DB::table('admin_modules')->updateOrInsert(
            ['module_key' => 'branch_commissions'],
            ['module_key' => 'branch_commissions', 'module_name' => 'عمولات الفروع', 'module_icon' => 'fas fa-code-branch', 'sort_order' => 13, 'created_at' => $now, 'updated_at' => $now]
        );

        // ── منح صلاحيات KPI لكل أدمن لديه صلاحية attendance ──
        $kpiModule = DB::table('admin_modules')->where('module_key', 'kpi')->first();
        $branchModule = DB::table('admin_modules')->where('module_key', 'branch_commissions')->first();
        $attendanceModule = DB::table('admin_modules')->where('module_key', 'attendance')->first();
        $commissionsModule = DB::table('admin_modules')->where('module_key', 'commissions')->first();

        if ($kpiModule && $attendanceModule) {
            $attendancePerms = DB::table('admin_permissions')
                ->where('module_id', $attendanceModule->id)
                ->get();

            foreach ($attendancePerms as $perm) {
                DB::table('admin_permissions')->updateOrInsert(
                    ['admin_id' => $perm->admin_id, 'module_id' => $kpiModule->id],
                    [
                        'can_read'   => $perm->can_read,
                        'can_create' => $perm->can_create,
                        'can_update' => $perm->can_update,
                        'can_delete' => $perm->can_delete,
                        'com_code'   => $perm->com_code,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }

        if ($branchModule && $commissionsModule) {
            $commissionsPerms = DB::table('admin_permissions')
                ->where('module_id', $commissionsModule->id)
                ->get();

            foreach ($commissionsPerms as $perm) {
                DB::table('admin_permissions')->updateOrInsert(
                    ['admin_id' => $perm->admin_id, 'module_id' => $branchModule->id],
                    [
                        'can_read'   => $perm->can_read,
                        'can_create' => $perm->can_create,
                        'can_update' => $perm->can_update,
                        'can_delete' => $perm->can_delete,
                        'com_code'   => $perm->com_code,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        $kpiModule = DB::table('admin_modules')->where('module_key', 'kpi')->first();
        $branchModule = DB::table('admin_modules')->where('module_key', 'branch_commissions')->first();

        if ($kpiModule) {
            DB::table('admin_permissions')->where('module_id', $kpiModule->id)->delete();
        }
        if ($branchModule) {
            DB::table('admin_permissions')->where('module_id', $branchModule->id)->delete();
        }

        DB::table('admin_modules')->whereIn('module_key', ['kpi', 'branch_commissions'])->delete();
    }
};
