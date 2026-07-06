<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * قاعدة ترحيل إضافية لتطبيق تكلفة العمالة والتكاليف الصناعية غير المباشرة
 * المقدّرة على حساب "تحت التشغيل" (WIP) عند بدء تنفيذ أمر الإنتاج، بدلاً من
 * تركها أرقامًا بلا قيد تُحمَّل لاحقًا على المخزون التام دون مقابل محاسبي.
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

            $wipId = DB::table('chart_of_accounts')->where('com_code', $comCode)->where('account_code', '1135')->value('id');
            $expenseId = DB::table('chart_of_accounts')->where('com_code', $comCode)->where('account_code', '5900')->value('id');
            if (!$wipId || !$expenseId) continue;

            DB::table('gl_posting_rules')->updateOrInsert(
                ['com_code' => $comCode, 'event_type' => 'production_overhead_applied', 'line_role' => 'WIP'],
                ['account_id' => $wipId, 'side' => 'debit', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
            );
            DB::table('gl_posting_rules')->updateOrInsert(
                ['com_code' => $comCode, 'event_type' => 'production_overhead_applied', 'line_role' => 'MANUFACTURING_OVERHEAD_APPLIED'],
                ['account_id' => $expenseId, 'side' => 'credit', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    public function down(): void
    {
        DB::table('gl_posting_rules')->where('event_type', 'production_overhead_applied')->delete();
    }
};
