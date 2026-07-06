<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * بيانات تجريبية لـ KPIs — يمكن حذفها بأمر:
 *   php artisan kpi:delete-demo
 *
 * لتشغيل الـ seeder:
 *   php artisan db:seed --class=KpiDemoSeeder
 *
 * ملاحظة: يمكن تغيير COM_CODE و ADDED_BY أدناه قبل التشغيل
 */
class KpiDemoSeeder extends Seeder
{
    // ─── غيّر هذه القيم لتناسب الشركة المطلوبة ───
    const COM_CODE  = 1;
    const ADDED_BY  = 1;

    public function run(): void
    {
        $comCode  = self::COM_CODE;
        $addedBy  = self::ADDED_BY;
        $hasSortOrder = Schema::hasColumn('kpi_definitions', 'sort_order');

        $definitions = [
            [
                'name'               => '[تجريبي] المبيعات الشهرية',
                'code'               => 'DEMO_SALES',
                'category'           => 'sales',
                'measurement_unit'   => 'ج.م',
                'target_value'       => 50000,
                'weight'             => 35,
                'affects_salary'     => 1,
                'salary_effect_type' => 'both',
                'max_bonus_pct'      => 10,
                'max_deduction_pct'  => 5,
                'is_active'          => 1,
                'description'        => 'DEMO_DATA — احذف باستخدام: php artisan kpi:delete-demo',
                'sort_order'         => 1,
            ],
            [
                'name'               => '[تجريبي] رضا العملاء',
                'code'               => 'DEMO_CSAT',
                'category'           => 'quality',
                'measurement_unit'   => 'نقطة',
                'target_value'       => 90,
                'weight'             => 25,
                'affects_salary'     => 1,
                'salary_effect_type' => 'bonus',
                'max_bonus_pct'      => 5,
                'max_deduction_pct'  => 0,
                'is_active'          => 1,
                'description'        => 'DEMO_DATA — احذف باستخدام: php artisan kpi:delete-demo',
                'sort_order'         => 2,
            ],
            [
                'name'               => '[تجريبي] الانضباط والحضور',
                'code'               => 'DEMO_ATTEND',
                'category'           => 'attendance',
                'measurement_unit'   => 'يوم',
                'target_value'       => 26,
                'weight'             => 20,
                'affects_salary'     => 1,
                'salary_effect_type' => 'both',
                'max_bonus_pct'      => 5,
                'max_deduction_pct'  => 10,
                'is_active'          => 1,
                'description'        => 'DEMO_DATA — احذف باستخدام: php artisan kpi:delete-demo',
                'sort_order'         => 3,
            ],
            [
                'name'               => '[تجريبي] معدل إتمام المهام',
                'code'               => 'DEMO_TASKS',
                'category'           => 'performance',
                'measurement_unit'   => '%',
                'target_value'       => 85,
                'weight'             => 15,
                'affects_salary'     => 1,
                'salary_effect_type' => 'both',
                'max_bonus_pct'      => 5,
                'max_deduction_pct'  => 5,
                'is_active'          => 1,
                'description'        => 'DEMO_DATA — احذف باستخدام: php artisan kpi:delete-demo',
                'sort_order'         => 4,
            ],
            [
                'name'               => '[تجريبي] معدل الأخطاء',
                'code'               => 'DEMO_ERRORS',
                'category'           => 'quality',
                'measurement_unit'   => '%',
                'target_value'       => 2,
                'weight'             => 5,
                'affects_salary'     => 0,
                'salary_effect_type' => 'bonus',
                'max_bonus_pct'      => 0,
                'max_deduction_pct'  => 0,
                'is_active'          => 1,
                'description'        => 'DEMO_DATA (للإحصاء فقط لا يؤثر على الراتب) — احذف: php artisan kpi:delete-demo',
                'sort_order'         => 5,
            ],
        ];

        $inserted = 0;
        foreach ($definitions as $def) {
            // تجاهل إذا كان الكود موجوداً مسبقاً
            if (DB::table('kpi_definitions')->where('code', $def['code'])->exists()) {
                $this->command->warn("  تجاهل: {$def['code']} — موجود مسبقاً");
                continue;
            }

            $row = [
                'name'               => $def['name'],
                'code'               => $def['code'],
                'category'           => $def['category'],
                'measurement_unit'   => $def['measurement_unit'],
                'target_value'       => $def['target_value'],
                'weight'             => $def['weight'],
                'affects_salary'     => $def['affects_salary'],
                'salary_effect_type' => $def['salary_effect_type'],
                'max_bonus_pct'      => $def['max_bonus_pct'],
                'max_deduction_pct'  => $def['max_deduction_pct'],
                'is_active'          => $def['is_active'],
                'description'        => $def['description'],
                'com_code'           => $comCode,
                'added_by'           => $addedBy,
                'created_at'         => now(),
                'updated_at'         => now(),
            ];

            if ($hasSortOrder) {
                $row['sort_order'] = $def['sort_order'];
            }

            DB::table('kpi_definitions')->insert($row);
            $inserted++;
        }

        $total = count($definitions);
        $this->command->info("  ✅ تم إدراج {$inserted} من {$total} مؤشرات تجريبية (com_code={$comCode})");
        $this->command->line("  ❕ لحذفها: php artisan kpi:delete-demo --com={$comCode}");
    }
}
