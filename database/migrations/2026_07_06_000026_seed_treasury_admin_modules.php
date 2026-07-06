<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now = now();

        $modules = [
            ['module_key' => 'cash_boxes',          'module_name' => 'الخزائن النقدية',   'module_icon' => 'fas fa-cash-register',   'sort_order' => 60],
            ['module_key' => 'bank_accounts',       'module_name' => 'الحسابات البنكية',  'module_icon' => 'fas fa-university',      'sort_order' => 61],
            ['module_key' => 'treasury_receipts',   'module_name' => 'سندات القبض',       'module_icon' => 'fas fa-hand-holding-usd','sort_order' => 62],
            ['module_key' => 'treasury_payments',   'module_name' => 'سندات الصرف',       'module_icon' => 'fas fa-money-check',     'sort_order' => 63],
            ['module_key' => 'cheques',             'module_name' => 'الشيكات',           'module_icon' => 'fas fa-money-check-alt', 'sort_order' => 64],
            ['module_key' => 'treasury_reports',    'module_name' => 'تقارير الخزينة',    'module_icon' => 'fas fa-chart-line',      'sort_order' => 66],
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
        $keys = ['cash_boxes','bank_accounts','treasury_receipts','treasury_payments','cheques','treasury_reports'];
        DB::table('admin_modules')->whereIn('module_key', $keys)->delete();
    }
};
