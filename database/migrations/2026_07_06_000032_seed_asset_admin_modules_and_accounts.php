<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $modules = [
            ['module_key' => 'asset_categories', 'module_name' => 'فئات الأصول',      'module_icon' => 'fas fa-tags',      'sort_order' => 70],
            ['module_key' => 'fixed_assets',     'module_name' => 'الأصول الثابتة',   'module_icon' => 'fas fa-building',   'sort_order' => 71],
            ['module_key' => 'asset_depreciation','module_name' => 'إهلاك الأصول',    'module_icon' => 'fas fa-chart-line', 'sort_order' => 72],
            ['module_key' => 'asset_reports',    'module_name' => 'تقارير الأصول',    'module_icon' => 'fas fa-file-alt',   'sort_order' => 73],
        ];

        foreach ($modules as $module) {
            DB::table('admin_modules')->updateOrInsert(
                ['module_key' => $module['module_key']],
                array_merge($module, ['created_at' => $now, 'updated_at' => $now])
            );
        }

        // حساب "أرباح/خسائر بيع الأصول الثابتة" - يُستخدم عند التخلص من أصل بفارق عن القيمة الدفترية
        $comCodes = DB::table('admins')
            ->selectRaw('DISTINCT COALESCE(NULLIF(com_code, 0), company_id) as cc')
            ->whereRaw('COALESCE(NULLIF(com_code, 0), company_id) IS NOT NULL')
            ->pluck('cc');

        foreach ($comCodes as $comCode) {
            $comCode = (int) $comCode;
            if ($comCode <= 0) continue;

            $revenueGroupId = DB::table('chart_of_accounts')->where('com_code', $comCode)->where('account_code', '4000')->value('id');

            $accountId = DB::table('chart_of_accounts')->where('com_code', $comCode)->where('account_code', '4200')->value('id');
            if (!$accountId) {
                $accountId = DB::table('chart_of_accounts')->insertGetId([
                    'com_code'         => $comCode,
                    'account_code'     => '4200',
                    'account_name'     => 'أرباح/خسائر بيع الأصول الثابتة',
                    'account_name_en'  => 'Gain/Loss on Asset Disposal',
                    'account_type'     => 'revenue',
                    'account_nature'   => 'credit',
                    'parent_id'        => $revenueGroupId,
                    'level'            => 2,
                    'is_group'         => false,
                    'is_active'        => true,
                    'current_balance'  => 0,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]);
            }

            DB::table('gl_posting_rules')->updateOrInsert(
                ['com_code' => $comCode, 'event_type' => 'asset_disposal', 'line_role' => 'GAIN_LOSS_ON_DISPOSAL'],
                ['account_id' => $accountId, 'side' => 'credit', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    public function down(): void
    {
        DB::table('admin_modules')->whereIn('module_key', ['asset_categories', 'fixed_assets', 'asset_depreciation', 'asset_reports'])->delete();
        DB::table('gl_posting_rules')->where('event_type', 'asset_disposal')->delete();
    }
};
