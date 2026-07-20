<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * قواعد ترحيل حدث "wuilt_wallet_collection" — يُرحَّل مرة لكل طلب لوحده لما نلاقي
 * eventType=CodWalletSettled في orderHistory بتاعه (بدل قيد تسوية إجمالي غير مصنّف).
 * يعيد استخدام نفس حسابي محفظة Wuilt (1103) وعملاء (1110) الموجودين بالفعل.
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

            $walletAccountId = DB::table('chart_of_accounts')
                ->where('com_code', $comCode)->where('account_code', '1103')->value('id');
            $arAccountId = DB::table('chart_of_accounts')
                ->where('com_code', $comCode)->where('account_code', '1110')->value('id');

            if ($walletAccountId) {
                DB::table('gl_posting_rules')->updateOrInsert(
                    ['com_code' => $comCode, 'event_type' => 'wuilt_wallet_collection', 'line_role' => 'WUILT_WALLET'],
                    ['account_id' => $walletAccountId, 'side' => 'debit', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
                );
            }

            if ($arAccountId) {
                DB::table('gl_posting_rules')->updateOrInsert(
                    ['com_code' => $comCode, 'event_type' => 'wuilt_wallet_collection', 'line_role' => 'AR_CONTROL'],
                    ['account_id' => $arAccountId, 'side' => 'credit', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
                );
            }
        }
    }

    public function down(): void
    {
        // بيانات إعداد أساسية — لا تُحذف تلقائيًا عند التراجع
    }
};
