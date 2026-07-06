<?php
namespace App\Services\Accounting;

use App\Models\AccountingPeriod;
use App\Models\ChartOfAccount;
use App\Models\GlPostingRule;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;

/**
 * البوابة الوحيدة للترحيل المحاسبي: كل موديول (مبيعات/مشتريات/رواتب/خزينة/أصول/إنتاج)
 * يرحّل عبرها فقط، ولا يُنشئ JournalEntry مباشرة. انظر docs/ROADMAP_ERP_COMPLETION.md.
 */
class JournalPostingService
{
    /**
     * @param array $lines كل سطر: ['role'=>string|null, 'account_id'=>int|null (يُستنتج من role إن لم يُمرَّر),
     *                      'debit'=>float, 'credit'=>float, 'cost_center_id'=>?int, 'branch_id'=>?int,
     *                      'description'=>?string, 'party_type'=>?string, 'party_id'=>?int]
     * @param array $meta ['source_module'=>?string, 'source_id'=>?int, 'entry_date'=>?string, 'reference'=>?string,
     *                      'description'=>?string, 'created_by'=>?int, 'entry_type'=>?string]
     */
    public static function post(string $eventType, int $comCode, array $lines, array $meta = []): JournalEntry
    {
        return DB::transaction(function () use ($eventType, $comCode, $lines, $meta) {
            $entryDate = $meta['entry_date'] ?? now()->toDateString();

            if (AccountingPeriod::isClosedFor($comCode, $entryDate)) {
                throw new \RuntimeException('لا يمكن الترحيل: الفترة المحاسبية لهذا التاريخ مغلقة');
            }

            $resolved    = [];
            $totalDebit  = 0.0;
            $totalCredit = 0.0;

            foreach ($lines as $line) {
                $accountId = $line['account_id'] ?? (isset($line['role']) ? self::resolveAccount($comCode, $eventType, $line['role']) : null);
                if (!$accountId) {
                    throw new \RuntimeException('سطر القيد بدون حساب أو دور (role) محدد');
                }
                $debit  = round((float) ($line['debit'] ?? 0), 4);
                $credit = round((float) ($line['credit'] ?? 0), 4);
                $totalDebit  += $debit;
                $totalCredit += $credit;
                $resolved[] = [
                    'account_id'     => $accountId,
                    'cost_center_id' => $line['cost_center_id'] ?? null,
                    'branch_id'      => $line['branch_id'] ?? null,
                    'debit'          => $debit,
                    'credit'         => $credit,
                    'description'    => $line['description'] ?? null,
                    'party_type'     => $line['party_type'] ?? null,
                    'party_id'       => $line['party_id'] ?? null,
                ];
            }

            if (round($totalDebit, 2) !== round($totalCredit, 2)) {
                throw new \RuntimeException("القيد غير متوازن: إجمالي مدين {$totalDebit} لا يساوي إجمالي دائن {$totalCredit}");
            }

            $period = AccountingPeriod::forDate($comCode, $entryDate);

            $entry = JournalEntry::create([
                'com_code'      => $comCode,
                'entry_number'  => self::nextEntryNumber($comCode),
                'entry_date'    => $entryDate,
                'entry_type'    => $meta['entry_type'] ?? (!empty($meta['source_module']) ? 'auto' : 'manual'),
                'source_module' => $meta['source_module'] ?? null,
                'source_id'     => $meta['source_id'] ?? null,
                'reference'     => $meta['reference'] ?? null,
                'description'   => $meta['description'] ?? null,
                'total_debit'   => $totalDebit,
                'total_credit'  => $totalCredit,
                'status'        => 'posted',
                'period_id'     => $period?->id,
                'created_by'    => $meta['created_by'] ?? null,
                'posted_by'     => $meta['created_by'] ?? null,
                'posted_at'     => now(),
            ]);

            foreach ($resolved as $line) {
                $entry->lines()->create($line);
                self::applyBalance($line['account_id'], $line['debit'], $line['credit']);
            }

            return $entry;
        });
    }

    public static function reverse(JournalEntry $entry, ?int $byAdminId = null, ?string $reason = null): JournalEntry
    {
        return DB::transaction(function () use ($entry, $byAdminId, $reason) {
            if ($entry->status === 'reversed') {
                throw new \RuntimeException('هذا القيد معكوس بالفعل');
            }

            $entry->loadMissing('lines');

            $reversal = JournalEntry::create([
                'com_code'          => $entry->com_code,
                'entry_number'      => self::nextEntryNumber($entry->com_code),
                'entry_date'        => now()->toDateString(),
                'entry_type'        => 'auto',
                'source_module'     => $entry->source_module,
                'source_id'         => $entry->source_id,
                'reference'         => $entry->reference,
                'description'       => 'عكس قيد '.$entry->entry_number.($reason ? ' - '.$reason : ''),
                'total_debit'       => $entry->total_credit,
                'total_credit'      => $entry->total_debit,
                'status'            => 'posted',
                'reversed_entry_id' => $entry->id,
                'created_by'        => $byAdminId,
                'posted_by'         => $byAdminId,
                'posted_at'         => now(),
            ]);

            foreach ($entry->lines as $line) {
                $reversal->lines()->create([
                    'account_id'     => $line->account_id,
                    'cost_center_id' => $line->cost_center_id,
                    'branch_id'      => $line->branch_id,
                    'debit'          => $line->credit,
                    'credit'         => $line->debit,
                    'description'    => 'عكس: '.($line->description ?? $entry->entry_number),
                    'party_type'     => $line->party_type,
                    'party_id'       => $line->party_id,
                ]);
                self::applyBalance($line->account_id, $line->credit, $line->debit);
            }

            $entry->update(['status' => 'reversed']);

            return $reversal;
        });
    }

    public static function resolveAccount(int $comCode, string $eventType, string $role): int
    {
        $rule = GlPostingRule::where('com_code', $comCode)
            ->where('event_type', $eventType)
            ->where('line_role', $role)
            ->where('is_active', true)
            ->first();

        if (!$rule) {
            throw new \RuntimeException("لا يوجد ربط محاسبي لحدث [$eventType] ودور [$role] - راجع شاشة إعدادات الترحيل التلقائي");
        }

        return (int) $rule->account_id;
    }

    public static function alreadyPosted(int $comCode, string $sourceModule, int $sourceId): bool
    {
        return JournalEntry::where('com_code', $comCode)
            ->where('source_module', $sourceModule)
            ->where('source_id', $sourceId)
            ->where('status', '!=', 'reversed')
            ->exists();
    }

    /** يعكس كل القيود المرحّلة المرتبطة بمصدر معيّن (فاتورة/كشف راتب/مرتجع...) دفعة واحدة */
    public static function reverseBySource(int $comCode, string $sourceModule, int $sourceId, ?int $byAdminId = null, ?string $reason = null): void
    {
        $entries = JournalEntry::where('com_code', $comCode)
            ->where('source_module', $sourceModule)
            ->where('source_id', $sourceId)
            ->where('status', 'posted')
            ->get();

        foreach ($entries as $entry) {
            self::reverse($entry, $byAdminId, $reason);
        }
    }

    private static function applyBalance(int $accountId, float $debit, float $credit): void
    {
        $account = ChartOfAccount::lockForUpdate()->find($accountId);
        if (!$account) return;
        $delta = $account->account_nature === 'debit' ? ($debit - $credit) : ($credit - $debit);
        $account->increment('current_balance', $delta);
    }

    private static function nextEntryNumber(int $comCode): string
    {
        $last = JournalEntry::where('com_code', $comCode)->whereYear('created_at', now()->year)->max('entry_number');
        $num  = $last ? ((int) substr($last, -4)) + 1 : 1;
        return 'JE-'.now()->year.'-'.str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}
