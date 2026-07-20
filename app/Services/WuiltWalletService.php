<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\EcommerceStore;
use App\Services\Accounting\JournalPostingService;

class WuiltWalletService
{
    /**
     * يجيب رصيد المحفظة الحقيقي من Wuilt ويقارنه برصيد حساب "محفظة Wuilt" في شجرة الحسابات،
     * ويرحّل قيد تسوية واحد بالفرق (لو فيه فرق فعلاً) ضد حساب "فروقات غير مصنّفة" مؤقتاً،
     * لحد ما Wuilt يفعّل تفاصيل الحركات (transactions) ويبقى ممكن نرحّل حركات مصنّفة فعلياً.
     */
    public function syncBalance(EcommerceStore $store): array
    {
        $service = new WuiltService($store);
        $realBalance = $service->fetchWalletBalance();

        $walletAccount = ChartOfAccount::where('com_code', $store->com_code)
            ->where('account_code', '1103')
            ->first();

        if (!$walletAccount) {
            throw new \RuntimeException('حساب محفظة Wuilt غير موجود في شجرة الحسابات لهذه الشركة');
        }

        $diff = round($realBalance - (float) $walletAccount->current_balance, 2);

        if (abs($diff) >= 0.01) {
            $lines = $diff > 0
                ? [
                    ['role' => 'WUILT_WALLET',              'debit' => $diff,  'credit' => 0],
                    ['role' => 'WUILT_WALLET_UNCLASSIFIED', 'debit' => 0,      'credit' => $diff],
                ]
                : [
                    ['role' => 'WUILT_WALLET_UNCLASSIFIED', 'debit' => abs($diff), 'credit' => 0],
                    ['role' => 'WUILT_WALLET',               'debit' => 0,          'credit' => abs($diff)],
                ];

            JournalPostingService::post('wuilt_wallet_adjustment', $store->com_code, $lines, [
                'source_module' => 'wuilt_wallet_balance',
                'source_id'     => $store->id,
                'reference'     => 'مزامنة رصيد محفظة Wuilt ' . now()->format('Y-m-d H:i'),
                'description'   => 'تسوية رصيد محفظة Wuilt (غير مصنّفة — بانتظار تفعيل تفاصيل الحركات من Wuilt)',
            ]);
        }

        $store->update([
            'wallet_balance'   => $realBalance,
            'wallet_synced_at' => now(),
        ]);

        return ['balance' => $realBalance, 'diff' => $diff];
    }
}
