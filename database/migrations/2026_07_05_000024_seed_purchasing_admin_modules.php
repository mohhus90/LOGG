<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now = now();

        $modules = [
            ['module_key' => 'suppliers',         'module_name' => 'الموردون',              'module_icon' => 'fas fa-truck',              'sort_order' => 30],
            ['module_key' => 'purchase_requests',  'module_name' => 'طلبات الشراء',          'module_icon' => 'fas fa-clipboard-list',     'sort_order' => 31],
            ['module_key' => 'purchase_orders',    'module_name' => 'أوامر الشراء',          'module_icon' => 'fas fa-file-signature',     'sort_order' => 32],
            ['module_key' => 'purchase_invoices',  'module_name' => 'فواتير الشراء',         'module_icon' => 'fas fa-file-invoice',       'sort_order' => 33],
            ['module_key' => 'purchase_payments',  'module_name' => 'مدفوعات الموردين',      'module_icon' => 'fas fa-money-check-alt',    'sort_order' => 34],
            ['module_key' => 'purchase_returns',   'module_name' => 'مرتجعات الشراء',        'module_icon' => 'fas fa-undo-alt',           'sort_order' => 35],
            ['module_key' => 'purchase_reports',   'module_name' => 'تقارير المشتريات',      'module_icon' => 'fas fa-chart-bar',          'sort_order' => 36],
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
        $keys = ['suppliers','purchase_requests','purchase_orders','purchase_invoices',
                 'purchase_payments','purchase_returns','purchase_reports'];
        DB::table('admin_modules')->whereIn('module_key', $keys)->delete();
    }
};
