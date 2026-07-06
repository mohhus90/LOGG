<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now = now();

        $modules = [
            ['module_key' => 'chart_of_accounts', 'module_name' => 'دليل الحسابات',           'module_icon' => 'fas fa-sitemap',        'sort_order' => 50],
            ['module_key' => 'cost_centers',      'module_name' => 'مراكز التكلفة',           'module_icon' => 'fas fa-project-diagram','sort_order' => 51],
            ['module_key' => 'journal_entries',   'module_name' => 'القيود اليومية',          'module_icon' => 'fas fa-book',           'sort_order' => 52],
            ['module_key' => 'accounting_periods','module_name' => 'الفترات المحاسبية',       'module_icon' => 'fas fa-calendar-check', 'sort_order' => 53],
            ['module_key' => 'gl_posting_rules',  'module_name' => 'إعدادات الترحيل التلقائي','module_icon' => 'fas fa-cogs',           'sort_order' => 54],
            ['module_key' => 'accounting_reports','module_name' => 'التقارير المالية',        'module_icon' => 'fas fa-chart-pie',      'sort_order' => 55],
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
        $keys = ['chart_of_accounts','cost_centers','journal_entries','accounting_periods','gl_posting_rules','accounting_reports'];
        DB::table('admin_modules')->whereIn('module_key', $keys)->delete();
    }
};
