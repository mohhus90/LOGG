<?php
namespace App\Services\Treasury;

use App\Models\BankAccount;
use App\Models\CashBox;
use App\Models\Cheque;
use App\Models\TreasuryVoucher;
use App\Services\Accounting\JournalPostingService;
use Illuminate\Support\Facades\DB;

/**
 * نقطة الدخول الوحيدة لحركة الخزينة (نقدية/بنكية/شيكات). كل سند يُنشأ هنا يحدّث
 * رصيد الخزنة/البنك ويرحّل قيدًا محاسبيًا عبر JournalPostingService (D1/D3 في
 * docs/ROADMAP_ERP_COMPLETION.md).
 */
class TreasuryService
{
    private static function nextVoucherNumber(int $comCode, string $voucherType): string
    {
        $prefix = $voucherType === 'receipt' ? 'RV' : 'PV';
        $last = TreasuryVoucher::where('com_code', $comCode)->where('voucher_type', $voucherType)
            ->whereYear('created_at', now()->year)->max('voucher_number');
        $num  = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix.'-'.now()->year.'-'.str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    /**
     * @param array $data ['date','payment_method'(cash|bank|cheque),'cash_box_id','bank_account_id',
     *                      'party_type'(customer|supplier|employee|other),'party_id','amount','gl_account_id'
     *                      (مطلوب فقط لو party_type=other),'linked_type','linked_id','reference_number','notes',
     *                      'cheque_number','cheque_bank_name','cheque_date','cheque_due_date' (لو payment_method=cheque)]
     */
    public static function createVoucher(int $comCode, string $voucherType, array $data, int $adminId): TreasuryVoucher
    {
        return DB::transaction(function () use ($comCode, $voucherType, $data, $adminId) {
            $amount        = (float) $data['amount'];
            $paymentMethod = $data['payment_method'];

            $voucher = TreasuryVoucher::create([
                'com_code'         => $comCode,
                'voucher_number'   => self::nextVoucherNumber($comCode, $voucherType),
                'voucher_type'     => $voucherType,
                'date'             => $data['date'],
                'payment_method'   => $paymentMethod,
                'cash_box_id'      => $data['cash_box_id'] ?? null,
                'bank_account_id'  => $data['bank_account_id'] ?? null,
                'party_type'       => $data['party_type'],
                'party_id'         => $data['party_id'] ?? null,
                'amount'           => $amount,
                'gl_account_id'    => $data['gl_account_id'] ?? null,
                'linked_type'      => $data['linked_type'] ?? null,
                'linked_id'        => $data['linked_id'] ?? null,
                'reference_number' => $data['reference_number'] ?? null,
                'notes'            => $data['notes'] ?? null,
                'status'           => 'posted',
                'created_by'       => $adminId,
                'posted_by'        => $adminId,
                'posted_at'        => now(),
            ]);

            // تحديث رصيد الخزنة/البنك (الشيك لا يؤثر على رصيد نقدي/بنكي حتى يُحصَّل)
            if ($paymentMethod === 'cash' && $voucher->cash_box_id) {
                $delta = $voucherType === 'receipt' ? $amount : -$amount;
                CashBox::where('id', $voucher->cash_box_id)->increment('current_balance', $delta);
            } elseif ($paymentMethod === 'bank' && $voucher->bank_account_id) {
                $delta = $voucherType === 'receipt' ? $amount : -$amount;
                BankAccount::where('id', $voucher->bank_account_id)->increment('current_balance', $delta);
            }

            if ($paymentMethod === 'cheque') {
                $cheque = Cheque::create([
                    'com_code'            => $comCode,
                    'direction'           => $voucherType === 'receipt' ? 'received' : 'issued',
                    'cheque_number'       => $data['cheque_number'],
                    'bank_name'           => $data['cheque_bank_name'] ?? null,
                    'cheque_date'         => $data['cheque_date'] ?? $data['date'],
                    'due_date'            => $data['cheque_due_date'] ?? $data['date'],
                    'amount'              => $amount,
                    'party_type'          => in_array($data['party_type'], ['customer', 'supplier']) ? $data['party_type'] : 'customer',
                    'party_id'            => $data['party_id'] ?? null,
                    'bank_account_id'     => $data['bank_account_id'] ?? null,
                    'treasury_voucher_id' => $voucher->id,
                    'status'              => 'under_collection',
                    'created_by'          => $adminId,
                ]);
                $voucher->update(['cheque_id' => $cheque->id]);
            }

            self::postVoucherJournal($voucher, $adminId);

            return $voucher->fresh();
        });
    }

    private static function postVoucherJournal(TreasuryVoucher $voucher, int $adminId): void
    {
        $comCode  = $voucher->com_code;
        $isReceipt = $voucher->voucher_type === 'receipt';
        $eventType = $isReceipt ? 'treasury_receipt' : 'treasury_payment';

        // الطرف الأول: الصندوق/البنك المباشر، أو حساب "شيكات تحت التحصيل/دفع" لو الطريقة شيك
        if ($voucher->payment_method === 'cash') {
            $cashLine = ['account_id' => \App\Models\CashBox::find($voucher->cash_box_id)?->gl_account_id];
        } elseif ($voucher->payment_method === 'bank') {
            $cashLine = ['account_id' => \App\Models\BankAccount::find($voucher->bank_account_id)?->gl_account_id];
        } else { // cheque
            $cashLine = ['role' => $isReceipt ? 'CHEQUES_UNDER_COLLECTION' : 'CHEQUES_PAYABLE'];
        }
        if (empty($cashLine['account_id']) && empty($cashLine['role'])) {
            throw new \RuntimeException('لا يوجد حساب محاسبي مرتبط بالخزنة/البنك المختار - راجع إعدادات الخزينة');
        }

        // الطرف الثاني: حسب نوع الطرف (عميل/مورد/آخر)
        if ($voucher->party_type === 'customer') {
            $partyLine = ['role' => 'AR_CONTROL', 'party_type' => 'customer', 'party_id' => $voucher->party_id];
        } elseif ($voucher->party_type === 'supplier') {
            $partyLine = ['role' => 'AP_CONTROL', 'party_type' => 'supplier', 'party_id' => $voucher->party_id];
        } else {
            if (!$voucher->gl_account_id) {
                throw new \RuntimeException('يجب تحديد الحساب المحاسبي عند اختيار طرف "أخرى"');
            }
            $partyLine = ['account_id' => $voucher->gl_account_id];
        }

        $lines = $isReceipt
            ? [array_merge($cashLine, ['debit' => $voucher->amount, 'credit' => 0]), array_merge($partyLine, ['debit' => 0, 'credit' => $voucher->amount])]
            : [array_merge($partyLine, ['debit' => $voucher->amount, 'credit' => 0]), array_merge($cashLine, ['debit' => 0, 'credit' => $voucher->amount])];

        JournalPostingService::post($eventType, $comCode, $lines, [
            'source_module' => 'treasury_voucher',
            'source_id'     => $voucher->id,
            'entry_date'    => $voucher->date,
            'reference'     => $voucher->voucher_number,
            'description'   => ($isReceipt ? 'سند قبض ' : 'سند صرف ').$voucher->voucher_number,
            'created_by'    => $adminId,
        ]);
    }

    public static function collectCheque(Cheque $cheque, int $adminId): void
    {
        DB::transaction(function () use ($cheque, $adminId) {
            if ($cheque->status !== 'under_collection') {
                throw new \RuntimeException('لا يمكن تحصيل شيك ليس تحت التحصيل');
            }
            if (!$cheque->bank_account_id) {
                throw new \RuntimeException('يجب تحديد الحساب البنكي الذي سيُحصَّل عليه الشيك');
            }

            $cheque->update(['status' => 'collected', 'collected_at' => now()]);

            $bankAccount = BankAccount::find($cheque->bank_account_id);
            $delta = $cheque->direction === 'received' ? $cheque->amount : -$cheque->amount;
            $bankAccount?->increment('current_balance', $delta);

            $eventType = $cheque->direction === 'received' ? 'cheque_collected_received' : 'cheque_collected_issued';
            $bankLine  = ['account_id' => $bankAccount?->gl_account_id];
            $clearingRole = $cheque->direction === 'received' ? 'CHEQUES_UNDER_COLLECTION' : 'CHEQUES_PAYABLE';

            $lines = $cheque->direction === 'received'
                ? [array_merge($bankLine, ['debit' => $cheque->amount, 'credit' => 0]), ['role' => $clearingRole, 'debit' => 0, 'credit' => $cheque->amount]]
                : [['role' => $clearingRole, 'debit' => $cheque->amount, 'credit' => 0], array_merge($bankLine, ['debit' => 0, 'credit' => $cheque->amount])];

            JournalPostingService::post($eventType, $cheque->com_code, $lines, [
                'source_module' => 'cheque',
                'source_id'     => $cheque->id,
                'entry_date'    => now()->toDateString(),
                'reference'     => $cheque->cheque_number,
                'description'   => 'تحصيل شيك '.$cheque->cheque_number,
                'created_by'    => $adminId,
            ]);
        });
    }

    public static function bounceCheque(Cheque $cheque, string $reason, int $adminId): void
    {
        DB::transaction(function () use ($cheque, $reason, $adminId) {
            if ($cheque->status !== 'under_collection') {
                throw new \RuntimeException('لا يمكن ارتجاع شيك ليس تحت التحصيل');
            }

            $cheque->update(['status' => 'bounced', 'bounced_at' => now(), 'bounce_reason' => $reason]);

            // الشيكات الواردة المرتجعة فقط تحتاج قيد إعادة فتح المديونية (الصادرة المرتجعة تبقى التزامًا قائمًا بلا تغيير محاسبي)
            if ($cheque->direction === 'received') {
                JournalPostingService::post('cheque_bounced_received', $cheque->com_code, [
                    ['role' => 'AR_CONTROL', 'debit' => $cheque->amount, 'credit' => 0, 'party_type' => 'customer', 'party_id' => $cheque->party_id],
                    ['role' => 'CHEQUES_UNDER_COLLECTION', 'debit' => 0, 'credit' => $cheque->amount],
                ], [
                    'source_module' => 'cheque',
                    'source_id'     => $cheque->id,
                    'entry_date'    => now()->toDateString(),
                    'reference'     => $cheque->cheque_number,
                    'description'   => 'ارتجاع شيك '.$cheque->cheque_number.' - '.$reason,
                    'created_by'    => $adminId,
                ]);
            }
        });
    }
}
