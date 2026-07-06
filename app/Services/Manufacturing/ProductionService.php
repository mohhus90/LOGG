<?php
namespace App\Services\Manufacturing;

use App\Models\BillOfMaterial;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderMaterial;
use App\Models\ProductionReceipt;
use App\Services\Accounting\JournalPostingService;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;

/**
 * دورة الإنتاج: خطة (من BOM) → صرف مواد خام للتشغيل (WIP) → استلام منتج تام
 * (Inventory) بتكلفة الإنتاج الفعلية. يعيد استخدام محرك المتوسط المرجح في
 * StockService بدل نظام تكلفة منفصل للمُصنَّع.
 */
class ProductionService
{
    private static function nextOrderNumber(int $comCode): string
    {
        $last = ProductionOrder::where('com_code', $comCode)->whereYear('created_at', now()->year)->max('order_number');
        $num  = $last ? ((int) substr($last, -4)) + 1 : 1;
        return 'PRO-'.now()->year.'-'.str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    public static function createFromBom(int $comCode, int $bomId, float $plannedQty, array $orderData, int $adminId): ProductionOrder
    {
        return DB::transaction(function () use ($comCode, $bomId, $plannedQty, $orderData, $adminId) {
            $bom = BillOfMaterial::with('lines')->where('com_code', $comCode)->findOrFail($bomId);
            $ratio = $bom->output_quantity > 0 ? $plannedQty / $bom->output_quantity : 0;

            $order = ProductionOrder::create([
                'com_code'             => $comCode,
                'order_number'         => self::nextOrderNumber($comCode),
                'bom_id'               => $bom->id,
                'item_id'              => $bom->item_id,
                'planned_quantity'     => $plannedQty,
                'produced_quantity'    => 0,
                'source_warehouse_id'  => $orderData['source_warehouse_id'],
                'target_warehouse_id'  => $orderData['target_warehouse_id'],
                'branch_id'            => $orderData['branch_id'] ?? null,
                'planned_start_date'   => $orderData['planned_start_date'] ?? null,
                'planned_end_date'     => $orderData['planned_end_date'] ?? null,
                'labor_cost'           => $orderData['labor_cost'] ?? 0,
                'overhead_cost'        => $orderData['overhead_cost'] ?? 0,
                'material_cost'        => 0,
                'total_cost'           => (float) ($orderData['labor_cost'] ?? 0) + (float) ($orderData['overhead_cost'] ?? 0),
                'status'               => 'draft',
                'notes'                => $orderData['notes'] ?? null,
                'created_by'           => $adminId,
            ]);

            foreach ($bom->lines as $line) {
                ProductionOrderMaterial::create([
                    'production_order_id' => $order->id,
                    'item_id'              => $line->component_item_id,
                    'planned_quantity'     => round($line->quantity * $ratio * (1 + $line->scrap_percent / 100), 4),
                    'issued_quantity'      => 0,
                ]);
            }

            return $order;
        });
    }

    /** @param array $issueLines [['material_id'=>ProductionOrderMaterial::id, 'quantity'=>float], ...] فارغ = صرف الكميات المخططة بالكامل */
    public static function issueMaterials(ProductionOrder $order, array $issueLines, int $adminId): void
    {
        DB::transaction(function () use ($order, $issueLines, $adminId) {
            $materials = $order->materials;
            if (empty($issueLines)) {
                $issueLines = $materials->map(fn ($m) => ['material_id' => $m->id, 'quantity' => $m->planned_quantity - $m->issued_quantity])->all();
            }

            $wasDraft = $order->status === 'draft';

            $totalIssued = 0.0;
            foreach ($issueLines as $row) {
                $qty = (float) ($row['quantity'] ?? 0);
                if ($qty <= 0) continue;
                $material = $materials->firstWhere('id', $row['material_id']);
                if (!$material) continue;

                $movement = StockService::adjustStock(
                    $order->com_code, $material->item_id, $order->source_warehouse_id, -$qty,
                    'production_issue', 'production_order', $order->id, null, now()->toDateString(),
                    'صرف مواد لأمر إنتاج '.$order->order_number, $adminId
                );

                $material->update([
                    'issued_quantity' => $material->issued_quantity + $qty,
                    'unit_cost'       => $movement->unit_cost,
                    'total_cost'      => ($material->total_cost ?? 0) + $movement->total_cost,
                ]);

                $totalIssued += (float) $movement->total_cost;
            }

            // تطبيق تكلفة العمالة والتكاليف غير المباشرة المقدّرة على WIP عند أول صرف فعلي للأمر
            // (بدون هذا القيد كانت هذه التكاليف تُحمَّل على تكلفة المنتج التام دون أي مقابل مدين في WIP)
            if ($wasDraft && ($order->labor_cost + $order->overhead_cost) > 0) {
                JournalPostingService::post('production_overhead_applied', $order->com_code, [
                    ['role' => 'WIP', 'debit' => $order->labor_cost + $order->overhead_cost, 'credit' => 0],
                    ['role' => 'MANUFACTURING_OVERHEAD_APPLIED', 'debit' => 0, 'credit' => $order->labor_cost + $order->overhead_cost],
                ], [
                    'source_module' => 'production_order',
                    'source_id'     => $order->id,
                    'entry_date'    => now()->toDateString(),
                    'reference'     => $order->order_number,
                    'description'   => 'تحميل تكلفة العمالة والتكاليف غير المباشرة - أمر '.$order->order_number,
                    'created_by'    => $adminId,
                ]);
            }

            if ($totalIssued > 0) {
                $order->update([
                    'material_cost' => $order->material_cost + $totalIssued,
                    'total_cost'    => $order->material_cost + $totalIssued + $order->labor_cost + $order->overhead_cost,
                    'status'        => $wasDraft ? 'in_progress' : $order->status,
                ]);

                JournalPostingService::post('production_issue', $order->com_code, [
                    ['role' => 'WIP', 'debit' => $totalIssued, 'credit' => 0],
                    ['role' => 'INVENTORY', 'debit' => 0, 'credit' => $totalIssued],
                ], [
                    'source_module' => 'production_order',
                    'source_id'     => $order->id,
                    'entry_date'    => now()->toDateString(),
                    'reference'     => $order->order_number,
                    'description'   => 'صرف مواد خام لأمر الإنتاج '.$order->order_number,
                    'created_by'    => $adminId,
                ]);
            }
        });
    }

    public static function receiveFinishedGoods(ProductionOrder $order, float $quantity, int $adminId): ProductionReceipt
    {
        return DB::transaction(function () use ($order, $quantity, $adminId) {
            $unitCost = $order->planned_quantity > 0 ? $order->total_cost / $order->planned_quantity : 0;
            $totalCost = round($quantity * $unitCost, 4);

            StockService::adjustStock(
                $order->com_code, $order->item_id, $order->target_warehouse_id, $quantity,
                'production_receipt', 'production_order', $order->id, $unitCost, now()->toDateString(),
                'استلام إنتاج تام من أمر '.$order->order_number, $adminId
            );

            $receipt = ProductionReceipt::create([
                'production_order_id' => $order->id,
                'quantity'             => $quantity,
                'unit_cost'            => $unitCost,
                'total_cost'           => $totalCost,
                'date'                 => now()->toDateString(),
                'warehouse_id'         => $order->target_warehouse_id,
                'created_by'           => $adminId,
            ]);

            $newProduced = $order->produced_quantity + $quantity;
            $order->update([
                'produced_quantity' => $newProduced,
                'status'            => $newProduced >= $order->planned_quantity ? 'completed' : 'in_progress',
                'actual_end_date'   => $newProduced >= $order->planned_quantity ? now()->toDateString() : $order->actual_end_date,
            ]);

            if ($totalCost > 0) {
                JournalPostingService::post('production_receipt', $order->com_code, [
                    ['role' => 'INVENTORY', 'debit' => $totalCost, 'credit' => 0],
                    ['role' => 'WIP', 'debit' => 0, 'credit' => $totalCost],
                ], [
                    'source_module' => 'production_order',
                    'source_id'     => $order->id,
                    'entry_date'    => now()->toDateString(),
                    'reference'     => $order->order_number,
                    'description'   => 'استلام إنتاج تام - أمر '.$order->order_number,
                    'created_by'    => $adminId,
                ]);
            }

            return $receipt;
        });
    }

    public static function complete(ProductionOrder $order): void
    {
        $order->update(['status' => 'completed', 'actual_end_date' => $order->actual_end_date ?? now()->toDateString()]);
    }

    public static function cancel(ProductionOrder $order): void
    {
        $order->update(['status' => 'cancelled']);
    }
}
