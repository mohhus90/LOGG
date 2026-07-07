<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now = now();

        $modules = [
            ['module_key' => 'pos_terminal', 'module_name' => 'نقطة البيع (POS)', 'module_icon' => 'fas fa-cash-register', 'sort_order' => 30],
            ['module_key' => 'pos_sessions', 'module_name' => 'جلسات الكاشير',     'module_icon' => 'fas fa-receipt',       'sort_order' => 31],
            ['module_key' => 'pos_registers','module_name' => 'ماكينات الكاشير',   'module_icon' => 'fas fa-store',         'sort_order' => 32],
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
        DB::table('admin_modules')->whereIn('module_key', ['pos_terminal', 'pos_sessions', 'pos_registers'])->delete();
    }
};
