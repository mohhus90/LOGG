<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminModulesSeeder extends Seeder
{
    /**
     * يقوم بإضافة جميع أقسام التطبيق إلى جدول admin_modules
     * يُنفَّذ مرة واحدة بعد التهجير
     */
    public function run(): void
    {
        $modules = [
            ['module_key' => 'general_settings', 'module_name' => 'الضبط العام',          'module_icon' => 'fas fa-cog',           'sort_order' => 1],
            ['module_key' => 'branches',          'module_name' => 'الفروع',               'module_icon' => 'fas fa-code-branch',   'sort_order' => 2],
            ['module_key' => 'shifts',            'module_name' => 'الشيفتات',             'module_icon' => 'fas fa-clock',         'sort_order' => 3],
            ['module_key' => 'departments',       'module_name' => 'الإدارات',             'module_icon' => 'fas fa-building',      'sort_order' => 4],
            ['module_key' => 'jobs_categories',   'module_name' => 'الوظائف',              'module_icon' => 'fas fa-briefcase',     'sort_order' => 5],
            ['module_key' => 'employees',         'module_name' => 'الموظفين',             'module_icon' => 'fas fa-users',         'sort_order' => 6],
            ['module_key' => 'attendance',        'module_name' => 'الحضور والانصراف',     'module_icon' => 'fas fa-fingerprint',   'sort_order' => 7],
            ['module_key' => 'advances',          'module_name' => 'السلف',                'module_icon' => 'fas fa-hand-holding-usd', 'sort_order' => 8],
            ['module_key' => 'commissions',       'module_name' => 'العمولات',             'module_icon' => 'fas fa-percentage',    'sort_order' => 9],
            ['module_key' => 'deductions',        'module_name' => 'الخصومات',             'module_icon' => 'fas fa-minus-circle',  'sort_order' => 10],
            ['module_key' => 'payroll',           'module_name' => 'مسير الرواتب',         'module_icon' => 'fas fa-money-check-alt', 'sort_order' => 11],
            ['module_key' => 'finance_calender',  'module_name' => 'السنوات المالية',      'module_icon' => 'fas fa-calendar-alt', 'sort_order' => 12],
            ['module_key' => 'vacations_balance', 'module_name' => 'الرصيد السنوي للإجازات', 'module_icon' => 'fas fa-umbrella-beach', 'sort_order' => 13],
            ['module_key' => 'admin_permissions', 'module_name' => 'صلاحيات المستخدمين', 'module_icon' => 'fas fa-user-shield',  'sort_order' => 14],
        ];

        foreach ($modules as $module) {
            DB::table('admin_modules')->updateOrInsert(
                ['module_key' => $module['module_key']],
                array_merge($module, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
