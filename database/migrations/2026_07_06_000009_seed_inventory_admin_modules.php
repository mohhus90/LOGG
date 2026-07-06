<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now = now();

        $modules = [
            ['module_key' => 'warehouses',        'module_name' => 'المخازن',            'module_icon' => 'fas fa-warehouse',        'sort_order' => 40],
            ['module_key' => 'stock_levels',       'module_name' => 'أرصدة المخزون',      'module_icon' => 'fas fa-boxes',            'sort_order' => 41],
            ['module_key' => 'stock_movements',    'module_name' => 'حركة الأصناف',       'module_icon' => 'fas fa-exchange-alt',     'sort_order' => 42],
            ['module_key' => 'stock_adjustments',  'module_name' => 'تسويات المخزون',     'module_icon' => 'fas fa-balance-scale',    'sort_order' => 43],
            ['module_key' => 'stock_transfers',    'module_name' => 'تحويلات المخازن',    'module_icon' => 'fas fa-dolly',            'sort_order' => 44],
            ['module_key' => 'inventory_reports',  'module_name' => 'تقارير المخازن',     'module_icon' => 'fas fa-chart-bar',        'sort_order' => 45],
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
        $keys = ['warehouses','stock_levels','stock_movements','stock_adjustments','stock_transfers','inventory_reports'];
        DB::table('admin_modules')->whereIn('module_key', $keys)->delete();
    }
};
