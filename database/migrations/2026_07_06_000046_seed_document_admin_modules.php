<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now = now();
        $modules = [
            ['module_key' => 'document_categories', 'module_name' => 'فئات الوثائق', 'module_icon' => 'fas fa-folder',   'sort_order' => 120],
            ['module_key' => 'documents',            'module_name' => 'الوثائق',       'module_icon' => 'fas fa-file-alt','sort_order' => 121],
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
        DB::table('admin_modules')->whereIn('module_key', ['document_categories', 'documents'])->delete();
    }
};
