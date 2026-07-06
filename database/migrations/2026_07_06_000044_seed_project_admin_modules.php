<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now = now();
        $modules = [
            ['module_key' => 'projects',      'module_name' => 'المشاريع', 'module_icon' => 'fas fa-project-diagram', 'sort_order' => 110],
            ['module_key' => 'project_tasks', 'module_name' => 'المهام',   'module_icon' => 'fas fa-tasks',           'sort_order' => 111],
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
        DB::table('admin_modules')->whereIn('module_key', ['projects', 'project_tasks'])->delete();
    }
};
