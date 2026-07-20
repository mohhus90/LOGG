<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * يضيف حساب "محفظة Wuilt" (أصل، يمثّل الرصيد الحي في محفظة Wuilt الحقيقية)
 * وحساب توضيحي لفروقات التسوية غير المصنّفة، لكل شركة، بنفس أسلوب
 * 2026_07_06_000018_seed_default_chart_of_accounts.php (idempotent عبر updateOrInsert).
 */
return new class extends Migration
{
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

            $parentId = DB::table('chart_of_accounts')
                ->where('com_code', $comCode)->where('account_code', '1100')
                ->value('id');

            $accounts = [
                '1103' => ['محفظة Wuilt', 'Wuilt Wallet'],
                '1104' => ['فروقات محفظة Wuilt غير مصنّفة', 'Unclassified Wuilt Wallet Adjustments'],
            ];

            $codeToId = [];
            foreach ($accounts as $code => [$name, $nameEn]) {
                $id = DB::table('chart_of_accounts')
                    ->where('com_code', $comCode)->where('account_code', $code)
                    ->value('id');

                if (!$id) {
                    $id = DB::table('chart_of_accounts')->insertGetId([
                        'com_code'         => $comCode,
                        'account_code'     => $code,
                        'account_name'     => $name,
                        'account_name_en'  => $nameEn,
                        'account_type'     => 'asset',
                        'account_nature'   => 'debit',
                        'parent_id'        => $parentId,
                        'level'            => 2,
                        'is_group'         => false,
                        'is_active'        => true,
                        'allow_cost_center'=> false,
                        'current_balance'  => 0,
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ]);
                }
                $codeToId[$code] = $id;
            }

            $rules = [
                'WUILT_WALLET'              => ['1103', 'debit'],
                'WUILT_WALLET_UNCLASSIFIED' => ['1104', 'debit'],
            ];

            foreach ($rules as $role => [$accountCode, $side]) {
                $accountId = $codeToId[$accountCode] ?? null;
                if (!$accountId) continue;

                DB::table('gl_posting_rules')->updateOrInsert(
                    ['com_code' => $comCode, 'event_type' => 'wuilt_wallet_adjustment', 'line_role' => $role],
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

    public function down(): void
    {
        // بيانات إعداد أساسية — لا تُحذف تلقائيًا عند التراجع
    }
};
