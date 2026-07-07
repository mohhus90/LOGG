<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('admin_modules')->updateOrInsert(
            ['module_key' => 'income_tax_brackets'],
            ['module_name' => 'شرائح ضريبة كسب العمل', 'module_icon' => 'fas fa-percentage', 'sort_order' => 16, 'created_at' => $now, 'updated_at' => $now]
        );

        // حساب "ضرائب كسب عمل مستحقة" لكل شركة قائمة فعليًا + قاعدة ترحيل جديدة (INCOME_TAX_PAYABLE)
        $comCodes = DB::table('admins')
            ->selectRaw('DISTINCT COALESCE(NULLIF(com_code, 0), company_id) as cc')
            ->whereRaw('COALESCE(NULLIF(com_code, 0), company_id) IS NOT NULL')
            ->pluck('cc');

        foreach ($comCodes as $comCode) {
            $comCode = (int) $comCode;
            if ($comCode <= 0) continue;

            $liabilityGroupId = DB::table('chart_of_accounts')->where('com_code', $comCode)->where('account_code', '2000')->value('id');

            $accountId = DB::table('chart_of_accounts')->where('com_code', $comCode)->where('account_code', '2140')->value('id');
            if (!$accountId) {
                $accountId = DB::table('chart_of_accounts')->insertGetId([
                    'com_code'         => $comCode,
                    'account_code'     => '2140',
                    'account_name'     => 'ضرائب كسب عمل مستحقة',
                    'account_name_en'  => 'Payroll Income Tax Payable',
                    'account_type'     => 'liability',
                    'account_nature'   => 'credit',
                    'parent_id'        => $liabilityGroupId,
                    'level'            => 2,
                    'is_group'         => false,
                    'is_active'        => true,
                    'current_balance'  => 0,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]);
            }

            DB::table('gl_posting_rules')->updateOrInsert(
                ['com_code' => $comCode, 'event_type' => 'payroll_approved', 'line_role' => 'INCOME_TAX_PAYABLE'],
                ['account_id' => $accountId, 'side' => 'credit', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    public function down(): void
    {
        DB::table('admin_modules')->where('module_key', 'income_tax_brackets')->delete();
        DB::table('gl_posting_rules')->where('event_type', 'payroll_approved')->where('line_role', 'INCOME_TAX_PAYABLE')->delete();
    }
};
