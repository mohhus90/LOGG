<?php

namespace App\Services;

use App\Models\EcommerceStore;
use App\Models\Item;
use Illuminate\Support\Facades\Log;

/**
 * يزامن كتالوج منتجات Wuilt مع جدول items، صنف منفصل لكل variant (حسب اختيار المستخدم)،
 * عشان تتم مطابقة بنود الطلبات تلقائياً بدل الإدخال اليدوي.
 */
class WuiltProductSyncService
{
    private const PAGE_SIZE = 50;

    public function sync(EcommerceStore $store): array
    {
        $stats = ['created' => 0, 'linked' => 0, 'updated' => 0, 'errors' => 0, 'error_details' => []];
        $service = new WuiltService($store);
        $offset = 0;

        do {
            $page = $service->fetchProducts(self::PAGE_SIZE, $offset);

            foreach ($page['nodes'] as $rawProduct) {
                foreach ($rawProduct['variants']['nodes'] ?? [] as $rawVariant) {
                    try {
                        $result = $this->upsertVariant($store->com_code, $rawProduct, $rawVariant);
                        $stats[$result]++;
                    } catch (\Throwable $e) {
                        Log::error("Wuilt product sync error (store #{$store->id}, variant {$rawVariant['id']}): {$e->getMessage()}");
                        $stats['errors']++;
                        $stats['error_details'][] = "{$rawProduct['title']}: {$e->getMessage()}";
                    }
                }
            }

            $offset += self::PAGE_SIZE;
        } while ($offset < $page['totalCount']);

        return $stats;
    }

    private function upsertVariant(int $comCode, array $rawProduct, array $rawVariant): string
    {
        $variantId = $rawVariant['id'];
        $productId = $rawProduct['id'];
        $name      = $this->buildVariantName($rawProduct['title'], $rawVariant);
        $price     = (float) ($rawVariant['price']['amount'] ?? 0);
        $cost      = (float) ($rawVariant['cost']['amount'] ?? 0);

        $item = Item::where('com_code', $comCode)->where('external_sku', $variantId)->first();
        if ($item) {
            $item->update([
                'name'          => $name,
                'selling_price' => $price,
                'cost_price'    => $cost,
            ]);
            return 'updated';
        }

        // مفيش صنف مربوط بالـ variant ده لسه — لو فيه صنف بنفس الاسم بالظبط وغير مربوط بأي منتج تاني، نربطه بدل ما نكرر
        $existingByName = Item::where('com_code', $comCode)
            ->whereNull('external_sku')
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower(trim($name))])
            ->first();

        if ($existingByName) {
            $existingByName->update([
                'external_sku'         => $variantId,
                'external_product_id'  => $productId,
                'selling_price'        => $price,
                'cost_price'           => $cost ?: $existingByName->cost_price,
            ]);
            return 'linked';
        }

        Item::create([
            'com_code'             => $comCode,
            'name'                 => $name,
            'type'                 => 'product',
            'selling_price'        => $price,
            'cost_price'           => $cost,
            'external_sku'         => $variantId,
            'external_product_id'  => $productId,
            'is_active'            => true,
        ]);

        return 'created';
    }

    private function buildVariantName(string $productTitle, array $rawVariant): string
    {
        $options = $rawVariant['selectedOptions'] ?? [];
        if (empty($options)) {
            return $productTitle;
        }

        $values = array_map(fn ($o) => $o['value']['name'] ?? '', $options);
        $values = array_filter($values, fn ($v) => $v !== '');

        return $values ? $productTitle . ' - ' . implode(' / ', $values) : $productTitle;
    }
}
