<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\EcommerceStore;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Warehouse;
use App\Services\Accounting\JournalPostingService;
use App\Services\Sales\InvoicePostingService;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WuiltOrderSyncService
{
    private const SOURCE = 'wuilt';
    private const OVERLAP_MINUTES = 5;
    private const PAGE_SIZE = 50;
    /** Wuilt يفلتر الطلبات بتاريخ الإنشاء فقط (مفيش updatedAt) — فبنعيد فحص آخر 30 يوم في كل مزامنة
     *  عشان نلتقط تغيّر حالة الشحن/التحصيل لطلبات سابقة، مش بس الطلبات الجديدة. */
    private const STATUS_REFRESH_DAYS = 30;

    /** الحالات المحلية التي لا يجوز الكتابة فوقها بمزامنة لاحقة (تعديل يدوي بعد التسليم/الإلغاء) */
    private const FINAL_LOCAL_STATUSES = ['delivered', 'cancelled'];

    public function sync(EcommerceStore $store): array
    {
        $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => 0, 'error_details' => []];

        // أول مزامنة لمتجر جديد: نسحب كل التاريخ المتاح بدل الاكتفاء بلحظة إضافة بيانات الاعتماد،
        // لأن المتجر غالباً يكون عنده طلبات سابقة فعلية قبل ربطه بـ NEXA.
        // المزامنات التالية: نراجع دايماً آخر STATUS_REFRESH_DAYS يوم (مش بس منذ آخر مزامنة) عشان نلتقط
        // تحديثات حالة الشحن/الدفع لطلبات سابقة، لأن Wuilt يفلتر بتاريخ الإنشاء فقط.
        $from = $store->last_synced_at
            ? min($store->last_synced_at->copy()->subMinutes(self::OVERLAP_MINUTES), now()->subDays(self::STATUS_REFRESH_DAYS))
            : \Carbon\Carbon::create(2000, 1, 1);
        $to = now();

        $service = new WuiltService($store);
        $offset  = 0;

        try {
            do {
                $page = $service->fetchOrders($from, $to, self::PAGE_SIZE, $offset);

                foreach ($page['nodes'] as $rawOrder) {
                    try {
                        DB::transaction(function () use ($store, $rawOrder, &$stats) {
                            $result = $this->upsertOrder($store, $rawOrder);
                            $stats[$result]++;
                        });
                    } catch (\Throwable $e) {
                        Log::error("Wuilt order sync error (store #{$store->id}, order {$rawOrder['id']}): {$e->getMessage()}");
                        $stats['errors']++;
                        $stats['error_details'][] = "طلب {$rawOrder['orderSerial']}: {$e->getMessage()}";
                    }
                }

                $offset += self::PAGE_SIZE;
            } while ($offset < $page['totalCount']);

            $store->update([
                'last_synced_at'    => $to,
                'last_sync_status'  => $stats['errors'] > 0 && ($stats['created'] + $stats['updated']) === 0 ? 'failed' : 'success',
                'last_sync_count'   => $stats['created'] + $stats['updated'],
                'last_sync_error'   => $stats['errors'] > 0 ? implode("\n", $stats['error_details']) : null,
            ]);
        } catch (\Throwable $e) {
            Log::error("Wuilt sync failed (store #{$store->id}): {$e->getMessage()}");
            $store->update([
                'last_sync_status' => 'failed',
                'last_sync_error'  => $e->getMessage(),
            ]);
            $stats['errors']++;
            $stats['error_details'][] = $e->getMessage();
        }

        return $stats;
    }

    private function upsertOrder(EcommerceStore $store, array $rawOrder): string
    {
        $comCode = $store->com_code;
        $customer = $this->findOrCreateCustomer($comCode, $rawOrder['customer'] ?? []);

        $existing = SalesOrder::where('com_code', $comCode)
            ->where('source', self::SOURCE)
            ->where('external_order_id', $rawOrder['id'])
            ->first();

        // shippingDetails.shippingStatus (مسار متداخل) هو اللي فعلياً بيوصل لقيمة DELIVERED — مختلف عن
        // Order.shippingStatus الأعلى (اللي بيرجع PENDING/CANCELED بس في هذا المتجر). بنستخدمه للعرض والتتبع.
        $shippingDetails = $rawOrder['shippingDetails'] ?? [];
        $externalStatus  = $shippingDetails['shippingStatus'] ?? $rawOrder['fulfillmentStatus'] ?? $rawOrder['paymentStatus'] ?? null;
        $history         = $rawOrder['orderHistory'] ?? [];
        $codSettled      = collect($history)->first(fn ($h) => ($h['eventType'] ?? null) === 'CodWalletSettled');

        $orderData = [
            'com_code'           => $comCode,
            'order_number'       => 'WUILT-' . ($rawOrder['orderSerial'] ?? $rawOrder['id']),
            'date'               => isset($rawOrder['createdAt']) ? \Carbon\Carbon::parse($rawOrder['createdAt']) : now(),
            'customer_id'        => $customer->id,
            'subtotal'           => $rawOrder['receipt']['subtotal']['amount'] ?? 0,
            'discount_amount'    => $rawOrder['receipt']['discount']['amount'] ?? 0,
            'tax_amount'         => $rawOrder['receipt']['tax']['amount'] ?? 0,
            'shipping_amount'    => $rawOrder['receipt']['shipping']['amount'] ?? 0,
            'total'              => $rawOrder['receipt']['total']['amount'] ?? 0,
            'delivery_address'   => $this->formatAddress($rawOrder['shippingAddress'] ?? null),
            'source'             => self::SOURCE,
            'external_order_id'  => $rawOrder['id'],
            'external_status'    => $externalStatus,
            'synced_at'          => now(),
            'shipping_company'   => $rawOrder['shippingRateName'] ?? null,
            'waybill_number'     => $shippingDetails['orderTrackingNumber'] ?? null,
            'tracking_url'       => $shippingDetails['trackingURL'] ?? null,
            'awb_url'            => $shippingDetails['airWayBill'] ?? null,
            'cod_amount'         => $rawOrder['paidAmount']['amount'] ?? null,
            'cod_status'         => $codSettled ? 'collected' : (($rawOrder['paymentMethod'] ?? null) === 'CASH_ON_DELIVERY' ? 'pending' : 'none'),
            'cod_collected_at'   => isset($codSettled['timestamp']) ? \Carbon\Carbon::parse($codSettled['timestamp']) : null,
        ];

        if (!$existing) {
            $orderData['status'] = $this->mapStatus($externalStatus, 'confirmed');
            $order = SalesOrder::create($orderData);
        } else {
            if (!in_array($existing->status, self::FINAL_LOCAL_STATUSES, true)) {
                $orderData['status'] = $this->mapStatus($externalStatus, $existing->status);
            }
            $existing->update($orderData);
            $order = $existing;
        }

        $this->syncItems($order, $rawOrder['items'] ?? []);
        $order = $order->fresh();
        $this->maybeAutoInvoice($order, $rawOrder);
        $this->maybeSettleWallet($order->fresh(), $rawOrder);

        return $existing ? 'updated' : 'created';
    }

    /**
     * تحويل أمر البيع لفاتورة تلقائياً لما تتأكد حالة التسليم الفعلية من Wuilt
     * (shippingDetails.shippingStatus = DELIVERED — الحقل الصحيح، وليس Order.shippingStatus الأعلى
     * الذي لا يصل لهذه القيمة في هذا المتجر). بنتخطى التحويل لو فيه بنود محتاجة مطابقة يدوية
     * (needs_item_mapping) عشان ما نرحّلش حركة مخزون على item_id خطأ أو فاضي.
     */
    private function maybeAutoInvoice(SalesOrder $order, array $rawOrder): void
    {
        $delivered = (($rawOrder['shippingDetails']['shippingStatus'] ?? null) === 'DELIVERED');

        if (!$delivered || $order->needs_item_mapping) {
            return;
        }

        if ($order->invoices()->exists()) {
            return;
        }

        $comCode     = $order->com_code;
        $warehouseId = Warehouse::defaultId($comCode);

        $invoice = SalesInvoice::create([
            'com_code'        => $comCode,
            'invoice_number'  => $this->nextInvoiceNumber($comCode),
            'date'            => $order->date,
            'customer_id'     => $order->customer_id,
            'branch_id'       => $order->branch_id,
            'order_id'        => $order->id,
            'warehouse_id'    => $warehouseId,
            'invoice_type'    => 'cash',
            'subtotal'        => $order->subtotal,
            'discount_amount' => $order->discount_amount,
            'tax_rate'        => $order->tax_rate,
            'tax_amount'      => $order->tax_amount,
            'shipping_amount' => $order->shipping_amount,
            'total'           => $order->total,
            'paid_amount'     => 0,
            'remaining_amount'=> $order->total,
            'payment_status'  => 'unpaid',
            'status'          => 'issued',
            'notes'           => 'تم إنشاؤها تلقائياً عند تسليم طلب Wuilt رقم '.$order->order_number,
        ]);

        $totalCogs = 0.0;
        foreach ($order->items as $orderItem) {
            SalesInvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'item_id'     => $orderItem->item_id,
                'description' => $orderItem->description,
                'unit_id'     => $orderItem->unit_id,
                'quantity'    => $orderItem->quantity,
                'unit_price'  => $orderItem->unit_price,
                'total'       => $orderItem->total,
            ]);

            if ($orderItem->item_id && $warehouseId) {
                $movement = StockService::adjustStock(
                    $comCode, $orderItem->item_id, $warehouseId, -$orderItem->quantity,
                    'sales_out', 'sales_invoice', $invoice->id, $orderItem->unit_price, $order->date,
                    null, null
                );
                $totalCogs += (float) $movement->total_cost;
            }
        }

        // بعض طلبات Wuilt عندها فرق غير موضّح بين subtotal-discount+tax+shipping والـ total الفعلي
        // (كوبونات/رسوم إضافية مش كل تفاصيلها متاحة في الـ API). عشان القيد يتوازن دايمًا، بنحسب
        // الإيراد كرقم موازن (total - الضريبة - الشحن) بدل الاعتماد على subtotal/discount مباشرة.
        $taxableAmount = (float) $order->total - (float) $order->tax_amount - (float) $order->shipping_amount;
        InvoicePostingService::postInvoiceJournal($invoice, $taxableAmount, $order->tax_amount, $totalCogs, null, (float) $order->shipping_amount);

        $order->update(['status' => 'delivered']);
    }

    /**
     * قيد "تحصيل COD" دقيق لكل طلب لوحده — يتحرّى وجود eventType=CodWalletSettled في orderHistory
     * (اللحظة الفعلية اللي فلوس الدفع عند الاستلام بتتسوّى فيها جوه محفظة Wuilt؛ اكتشفنا إن
     * paymentStatus=PAID وحدها مايعنيش ده فعلياً — الأرقام ما كانتش متطابقة مع رصيد المحفظة الحقيقي).
     * لازم يكون فيه فاتورة مرتبطة بالفعل (AR مرحّل) قبل ما نقفل عليها بقيد التحصيل.
     */
    private function maybeSettleWallet(SalesOrder $order, array $rawOrder): void
    {
        $comCode = $order->com_code;

        if (JournalPostingService::alreadyPosted($comCode, 'wuilt_wallet_collection', $order->id)) {
            return;
        }

        $history    = $rawOrder['orderHistory'] ?? [];
        $codSettled = collect($history)->first(fn ($h) => ($h['eventType'] ?? null) === 'CodWalletSettled');

        if (!$codSettled) {
            return;
        }

        $invoice = $order->invoices()->first();
        if (!$invoice) {
            return;
        }

        $amount = (float) ($rawOrder['paidAmount']['amount'] ?? $invoice->total);
        if ($amount <= 0) {
            return;
        }

        JournalPostingService::post('wuilt_wallet_collection', $comCode, [
            ['role' => 'WUILT_WALLET', 'debit' => $amount, 'credit' => 0],
            ['role' => 'AR_CONTROL',   'debit' => 0, 'credit' => $amount, 'party_type' => 'customer', 'party_id' => $order->customer_id],
        ], [
            'source_module' => 'wuilt_wallet_collection',
            'source_id'     => $order->id,
            'entry_date'    => \Carbon\Carbon::parse($codSettled['timestamp'])->toDateString(),
            'reference'     => $order->order_number,
            'description'   => 'تحصيل دفع عند الاستلام (COD) - طلب '.$order->order_number,
        ]);
    }

    private function nextInvoiceNumber(int $comCode): string
    {
        $last = SalesInvoice::where('com_code', $comCode)->whereYear('created_at', now()->year)->max('invoice_number');
        $num  = $last ? ((int) substr($last, -4)) + 1 : 1;
        return 'INV-'.now()->year.'-'.str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    private function syncItems(SalesOrder $order, array $rawItems): void
    {
        $order->items()->delete();
        $needsMapping = false;

        foreach ($rawItems as $rawItem) {
            // Wuilt Orders API بترجّع معرّف المنتج (productSnapshot.id) بس، مش الـ variant المحدد اللي اتشرى.
            // لو المنتج عنده variant واحد مربوط (الحالة الشائعة) بنطابقه تلقائي؛ لو أكتر من variant، الأمر غامض
            // ولازم مطابقة يدوية (قيد فعلي في Wuilt Orders API، مش نقص في الكود).
            $productId = $rawItem['productSnapshot']['id'] ?? null;
            $item = null;
            if ($productId) {
                $candidates = Item::where('com_code', $order->com_code)->where('external_product_id', $productId)->get();
                $item = $candidates->count() === 1 ? $candidates->first() : null;
            }

            if (!$item) {
                $needsMapping = true;
            }

            $qty   = (float) ($rawItem['quantity'] ?? 1);
            $price = (float) ($rawItem['price']['amount'] ?? 0);

            SalesOrderItem::create([
                'order_id'    => $order->id,
                'item_id'     => $item?->id,
                'description' => $rawItem['title'] ?? null,
                'unit_id'     => $item?->unit_id ?? ItemUnit::where('com_code', $order->com_code)->value('id'),
                'quantity'    => $qty,
                'unit_price'  => $price,
                'total'       => round($qty * $price, 2),
            ]);
        }

        $order->update(['needs_item_mapping' => $needsMapping]);
    }

    private function findOrCreateCustomer(int $comCode, array $rawCustomer): Customer
    {
        $phone = $rawCustomer['phone'] ?? null;
        $name  = $rawCustomer['name'] ?? 'عميل Wuilt';

        if ($phone) {
            $customer = Customer::where('com_code', $comCode)->where('phone', $phone)->first();
            if ($customer) {
                return $customer;
            }
        }

        return Customer::create([
            'com_code' => $comCode,
            'name'     => $name,
            'phone'    => $phone,
            'email'    => $rawCustomer['email'] ?? null,
            'type'     => 'individual',
            'is_active'=> true,
        ]);
    }

    private function mapStatus(?string $externalStatus, string $currentLocalStatus): string
    {
        $s = strtolower((string) $externalStatus);

        return match (true) {
            str_contains($s, 'cancel')                          => 'cancelled',
            str_contains($s, 'deliver')                         => 'delivered',
            str_contains($s, 'partial')                         => 'partial',
            str_contains($s, 'ship') || str_contains($s, 'process') => 'processing',
            default                                              => $currentLocalStatus,
        };
    }

    private function formatAddress(?array $address): ?string
    {
        if (!$address) {
            return null;
        }

        return implode(', ', array_filter($address, fn ($v) => is_string($v) && $v !== ''));
    }
}
