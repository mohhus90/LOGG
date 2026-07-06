<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function postingRules(): array
    {
        return [
            'production_issue' => [
                'WIP'       => ['1135', 'debit'],
                'INVENTORY' => ['1130', 'credit'],
            ],
            'production_receipt' => [
                'INVENTORY' => ['1130', 'debit'],
                'WIP'       => ['1135', 'credit'],
            ],
        ];
    }

    public function up(): void
    {
        $now = now();

        $modules = [
            ['module_key' => 'bill_of_materials',    'module_name' => 'مكونات المنتج (BOM)', 'module_icon' => 'fas fa-sitemap',   'sort_order' => 80],
            ['module_key' => 'production_orders',    'module_name' => 'أوامر الإنتاج',        'module_icon' => 'fas fa-industry',  'sort_order' => 81],
            ['module_key' => 'manufacturing_reports','module_name' => 'تقارير الإنتاج',        'module_icon' => 'fas fa-chart-bar', 'sort_order' => 82],
        ];

        foreach ($modules as $module) {
            DB::table('admin_modules')->updateOrInsert(
                ['module_key' => $module['module_key']],
                array_merge($module, ['created_at' => $now, 'updated_at' => $now])
            );
        }

        $comCodes = DB::table('admins')
            ->selectRaw('DISTINCT COALESCE(NULLIF(com_code, 0), company_id) as cc')
            ->whereRaw('COALESCE(NULLIF(com_code, 0), company_id) IS NOT NULL')
            ->pluck('cc');

        foreach ($comCodes as $comCode) {
            $comCode = (int) $comCode;
            if ($comCode <= 0) continue;

            $accountIds = DB::table('chart_of_accounts')->where('com_code', $comCode)->pluck('id', 'account_code');

            foreach ($this->postingRules() as $eventType => $roles) {
                foreach ($roles as $role => [$accountCode, $side]) {
                    $accountId = $accountIds[$accountCode] ?? null;
                    if (!$accountId) continue;

                    DB::table('gl_posting_rules')->updateOrInsert(
                        ['com_code' => $comCode, 'event_type' => $eventType, 'line_role' => $role],
                        ['account_id' => $accountId, 'side' => $side, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
                    );
                }
            }
        }
    }

    public function down(): void
    {
        DB::table('admin_modules')->whereIn('module_key', ['bill_of_materials', 'production_orders', 'manufacturing_reports'])->delete();
        DB::table('gl_posting_rules')->whereIn('event_type', ['production_issue', 'production_receipt'])->delete();
    }
};
