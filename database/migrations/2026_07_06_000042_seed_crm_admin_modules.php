<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now = now();
        $modules = [
            ['module_key' => 'crm_leads',         'module_name' => 'العملاء المحتملون', 'module_icon' => 'fas fa-user-plus',   'sort_order' => 100],
            ['module_key' => 'crm_opportunities', 'module_name' => 'الفرص البيعية',     'module_icon' => 'fas fa-bullseye',    'sort_order' => 101],
            ['module_key' => 'crm_activities',    'module_name' => 'المتابعات',          'module_icon' => 'fas fa-comments',    'sort_order' => 102],
        ];

        foreach ($modules as $module) {
            DB::table('admin_modules')->updateOrInsert(
                ['module_key' => $module['module_key']],
                array_merge($module, ['created_at' => $now, 'updated_at' => $now])
            );
        }
    }

    public function down(): void
    {
        DB::table('admin_modules')->whereIn('module_key', ['crm_leads', 'crm_opportunities', 'crm_activities'])->delete();
    }
};
