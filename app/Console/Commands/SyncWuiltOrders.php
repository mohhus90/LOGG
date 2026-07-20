<?php

namespace App\Console\Commands;

use App\Models\EcommerceStore;
use App\Services\WuiltOrderSyncService;
use App\Services\WuiltProductSyncService;
use App\Services\WuiltWalletService;
use Illuminate\Console\Command;

class SyncWuiltOrders extends Command
{
    protected $signature = 'sales:sync-wuilt-orders {--store=}';
    protected $description = 'مزامنة الطلبات من متاجر Wuilt المفعّلة إلى Sales Orders';

    public function handle(WuiltOrderSyncService $service, WuiltProductSyncService $productService, WuiltWalletService $walletService): int
    {
        $query = EcommerceStore::where('provider', 'wuilt')->where('is_active', true);

        if ($store = $this->option('store')) {
            $query->where('id', $store);
        }

        $stores = $query->get();

        if ($stores->isEmpty()) {
            $this->warn('لا توجد متاجر Wuilt مفعّلة للمزامنة.');
            return self::SUCCESS;
        }

        foreach ($stores as $store) {
            $this->info("مزامنة المتجر #{$store->id} ({$store->name})...");

            try {
                $productStats = $productService->sync($store);
                $this->line("  منتجات: جديد {$productStats['created']} | مربوط {$productStats['linked']} | محدّث {$productStats['updated']} | أخطاء {$productStats['errors']}");
            } catch (\Throwable $e) {
                $this->error("  فشلت مزامنة المنتجات: {$e->getMessage()}");
            }

            $stats = $service->sync($store);
            $this->line("  جديد: {$stats['created']} | محدّث: {$stats['updated']} | أخطاء: {$stats['errors']}");

            if ($stats['errors'] > 0) {
                foreach ($stats['error_details'] as $detail) {
                    $this->error("  - {$detail}");
                }
            }

            try {
                $wallet = $walletService->syncBalance($store);
                $this->line("  رصيد المحفظة: {$wallet['balance']} (فرق التسوية: {$wallet['diff']})");
            } catch (\Throwable $e) {
                $this->error("  فشلت مزامنة رصيد المحفظة: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
