<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * حساب "إيرادات الشحن" — مطلوب لتوازن قيد فاتورة البيع لما تتضمن مصروف شحن محصّل من العميل
 * (مثال: فواتير Wuilt التلقائية)، بنفس أسلوب 2026_07_06_000018_seed_default_chart_of_accounts.php.
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
                ->where('com_code', $comCode)->where('account_code', '4000')
                ->value('id');

            $accountId = DB::table('chart_of_accounts')
                ->where('com_code', $comCode)->where('account_code', '4200')
                ->value('id');

            if (!$accountId) {
                $accountId = DB::table('chart_of_accounts')->insertGetId([
                    'com_code'         => $comCode,
                    'account_code'     => '4200',
                    'account_name'     => 'إيرادات الشحن',
                    'account_name_en'  => 'Shipping Revenue',
                    'account_type'     => 'revenue',
                    'account_nature'   => 'credit',
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

            DB::table('gl_posting_rules')->updateOrInsert(
                ['com_code' => $comCode, 'event_type' => 'sales_invoice_issued', 'line_role' => 'SHIPPING_REVENUE'],
                [
                    'account_id' => $accountId,
                    'side'       => 'credit',
                    'is_active'  => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        // بيانات إعداد أساسية — لا تُحذف تلقائيًا عند التراجع
    }
};
