<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** قواعد الترحيل التلقائي لسندات القبض/الصرف والشيكات (Phase 4) */
return new class extends Migration
{
    private function postingRules(): array
    {
        return [
            'treasury_receipt' => [
                'AR_CONTROL'              => ['1110', 'credit'],
                'AP_CONTROL'              => ['2100', 'credit'],
                'CHEQUES_UNDER_COLLECTION'=> ['1120', 'debit'],
            ],
            'treasury_payment' => [
                'AP_CONTROL'      => ['2100', 'debit'],
                'AR_CONTROL'      => ['1110', 'debit'],
                'CHEQUES_PAYABLE' => ['2130', 'credit'],
            ],
            'cheque_collected_received' => [
                'CHEQUES_UNDER_COLLECTION' => ['1120', 'credit'],
            ],
            'cheque_collected_issued' => [
                'CHEQUES_PAYABLE' => ['2130', 'debit'],
            ],
            'cheque_bounced_received' => [
                'AR_CONTROL'               => ['1110', 'debit'],
                'CHEQUES_UNDER_COLLECTION' => ['1120', 'credit'],
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
        DB::table('gl_posting_rules')->whereIn('event_type', [
            'treasury_receipt', 'treasury_payment', 'cheque_collected_received', 'cheque_collected_issued', 'cheque_bounced_received',
        ])->delete();
    }
};
