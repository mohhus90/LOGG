<?php

namespace App\Services\Sales;

use App\Models\SalesInvoice;
use App\Services\Accounting\JournalPostingService;

/**
 * منطق ترحيل قيد فاتورة البيع (إيراد/ضريبة + تكلفة بضاعة مباعة)، مستخرج من
 * SalesInvoicesController::postInvoiceJournal() ليُستخدم من أي مسار آخر ينشئ
 * فاتورة بيع (مثل التحويل التلقائي لأمر بيع Wuilt عند التسليم).
 */
class InvoicePostingService
{
    public static function postInvoiceJournal(SalesInvoice $invoice, float $taxableAmount, float $taxAmount, float $totalCogs, ?int $adminId, float $shippingAmount = 0.0): void
    {
        $comCode = $invoice->com_code;
        if (JournalPostingService::alreadyPosted($comCode, 'sales_invoice', $invoice->id)) {
            return;
        }

        $lines = [
            ['role' => 'AR_CONTROL',    'debit' => $invoice->total, 'credit' => 0, 'party_type' => 'customer', 'party_id' => $invoice->customer_id],
            ['role' => 'SALES_REVENUE', 'debit' => 0, 'credit' => $taxableAmount],
            ['role' => 'VAT_OUTPUT',    'debit' => 0, 'credit' => $taxAmount],
        ];

        if ($shippingAmount > 0) {
            $lines[] = ['role' => 'SHIPPING_REVENUE', 'debit' => 0, 'credit' => $shippingAmount];
        }

        JournalPostingService::post('sales_invoice_issued', $comCode, $lines, [
            'source_module' => 'sales_invoice',
            'source_id'     => $invoice->id,
            'entry_date'    => $invoice->date,
            'reference'     => $invoice->invoice_number,
            'description'   => 'فاتورة بيع '.$invoice->invoice_number,
            'created_by'    => $adminId,
        ]);

        if ($totalCogs > 0) {
            JournalPostingService::post('sales_invoice_cogs', $comCode, [
                ['role' => 'COGS',      'debit' => $totalCogs, 'credit' => 0],
                ['role' => 'INVENTORY', 'debit' => 0, 'credit' => $totalCogs],
            ], [
                'source_module' => 'sales_invoice',
                'source_id'     => $invoice->id,
                'entry_date'    => $invoice->date,
                'reference'     => $invoice->invoice_number,
                'description'   => 'تكلفة البضاعة المباعة - فاتورة '.$invoice->invoice_number,
                'created_by'    => $adminId,
            ]);
        }
    }
}
