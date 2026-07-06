<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now = now();
        $modules = [
            ['module_key' => 'quality_checklists',  'module_name' => 'قوالب فحص الجودة', 'module_icon' => 'fas fa-clipboard-list', 'sort_order' => 83],
            ['module_key' => 'quality_inspections', 'module_name' => 'فحوصات الجودة',    'module_icon' => 'fas fa-search',         'sort_order' => 84],
            ['module_key' => 'quality_reports',     'module_name' => 'تقارير الجودة',    'module_icon' => 'fas fa-certificate',    'sort_order' => 85],
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
        DB::table('admin_modules')->whereIn('module_key', ['quality_checklists', 'quality_inspections', 'quality_reports'])->delete();
    }
};
