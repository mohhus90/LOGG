<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * يضيف قواعد الترحيل التلقائي لمرتجعات البيع والشراء (Phase 3) فوق
 * دليل الحسابات الذي زُرع بالفعل في 2026_07_06_000018_seed_default_chart_of_accounts.php
 */
return new class extends Migration
{
    private function postingRules(): array
    {
        return [
            'sales_return_posted' => [
                'SALES_REVENUE' => ['4100', 'debit'],
                'VAT_OUTPUT'    => ['2110', 'debit'],
                'AR_CONTROL'    => ['1110', 'credit'],
            ],
            'sales_return_cogs' => [
                'INVENTORY' => ['1130', 'debit'],
                'COGS'      => ['5100', 'credit'],
            ],
            'purchase_return_posted' => [
                'AP_CONTROL' => ['2100', 'debit'],
                'INVENTORY'  => ['1130', 'credit'],
                'VAT_INPUT'  => ['1140', 'credit'],
            ],
        ];
    }

    public function up(): void
    {
        $now = now();

        $comCodes = DB::table('admins')
            ->selectRaw('DISTINCT COALESCE(NULLIF(com_code, 0), company_id) as cc')
            ->whereRaw('COALESCE(NULLIF(com_code, 0), company_id) IS NOT NULL')
            ->pluck('cc');

        foreach ($comCodes as $comCode) {
            $comCode = (int) $comCode;
            if ($comCode <= 0) continue;

            $accountIds = DB::table('chart_of_accounts')
                ->where('com_code', $comCode)
                ->pluck('id', 'account_code');

            foreach ($this->postingRules() as $eventType => $roles) {
                foreach ($roles as $role => [$accountCode, $side]) {
                    $accountId = $accountIds[$accountCode] ?? null;
                    if (!$accountId) continue;

                    DB::table('gl_posting_rules')->updateOrInsert(
                        ['com_code' => $comCode, 'event_type' => $eventType, 'line_role' => $role],
                        [
                            'account_id' => $accountId,
                            'side'       => $side,
                            'is_active'  => true,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]
                    );
                }
            }
        }
    }

    public function down(): void
    {
        DB::table('gl_posting_rules')->whereIn('event_type', [
            'sales_return_posted', 'sales_return_cogs', 'purchase_return_posted',
        ])->delete();
    }
};
