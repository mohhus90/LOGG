<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now = now();

        $modules = [
            ['module_key' => 'sales_customers',  'module_name' => 'عملاء المبيعات',    'module_icon' => 'fas fa-users',              'sort_order' => 20],
            ['module_key' => 'items',            'module_name' => 'الأصناف',            'module_icon' => 'fas fa-boxes',              'sort_order' => 21],
            ['module_key' => 'item_units',       'module_name' => 'وحدات القياس',       'module_icon' => 'fas fa-ruler-combined',     'sort_order' => 22],
            ['module_key' => 'item_categories',  'module_name' => 'مجموعات الأصناف',   'module_icon' => 'fas fa-layer-group',        'sort_order' => 23],
            ['module_key' => 'sales_quotations', 'module_name' => 'عروض الأسعار',       'module_icon' => 'fas fa-file-alt',           'sort_order' => 24],
            ['module_key' => 'sales_orders',     'module_name' => 'أوامر البيع',        'module_icon' => 'fas fa-shopping-cart',      'sort_order' => 25],
            ['module_key' => 'sales_invoices',   'module_name' => 'فواتير البيع',       'module_icon' => 'fas fa-file-invoice',       'sort_order' => 26],
            ['module_key' => 'sales_payments',   'module_name' => 'مدفوعات العملاء',   'module_icon' => 'fas fa-money-bill-wave',    'sort_order' => 27],
            ['module_key' => 'sales_returns',    'module_name' => 'مرتجعات البيع',      'module_icon' => 'fas fa-undo-alt',           'sort_order' => 28],
            ['module_key' => 'sales_reports',    'module_name' => 'تقارير المبيعات',    'module_icon' => 'fas fa-chart-bar',          'sort_order' => 29],
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
        $keys = ['sales_customers','items','item_units','item_categories',
                 'sales_quotations','sales_orders','sales_invoices',
                 'sales_payments','sales_returns','sales_reports'];
        DB::table('admin_modules')->whereIn('module_key', $keys)->delete();
    }
};
