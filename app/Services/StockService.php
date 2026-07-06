<?php
namespace App\Services;

use App\Models\StockBalance;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Apply a stock movement and update the running balance atomically.
     * $qtyDelta is signed: positive increases the warehouse balance, negative decreases it.
     */
    /**
     * تقييم المخزون بالمتوسط المرجح (Weighted Average): عند الإضافة يُعاد حساب متوسط
     * تكلفة الوحدة، وعند الخصم يُستخدم المتوسط الحالي كتكلفة فعلية (COGS) بدل التكلفة
     * المُمرَّرة من المستدعي (التي غالبًا ما تكون سعر بيع لا تكلفة). القيمة المُرجعة
     * StockMovement تحمل unit_cost/total_cost الفعليين، فمن يحتاج تكلفة COGS للترحيل
     * المحاسبي (Phase 3) يقرأها من الكائن المُرجع دون تغيير في توقيع الدالة.
     */
    public static function adjustStock(
        int $comCode,
        int $itemId,
        int $warehouseId,
        float $qtyDelta,
        string $movementType,
        ?string $refType = null,
        ?int $refId = null,
        ?float $unitCost = null,
        ?string $date = null,
        ?string $notes = null,
        ?int $createdBy = null
    ): StockMovement {
        return DB::transaction(function () use ($comCode, $itemId, $warehouseId, $qtyDelta, $movementType, $refType, $refId, $unitCost, $date, $notes, $createdBy) {
            $balance = StockBalance::where('warehouse_id', $warehouseId)
                ->where('item_id', $itemId)
                ->lockForUpdate()
                ->first();

            if (!$balance) {
                $balance = StockBalance::create([
                    'com_code'     => $comCode,
                    'warehouse_id' => $warehouseId,
                    'item_id'      => $itemId,
                    'quantity'     => 0,
                    'avg_cost'     => 0,
                    'total_value'  => 0,
                ]);
            }

            $newQuantity = $balance->quantity + $qtyDelta;
            $effectiveUnitCost = $balance->avg_cost;

            if ($qtyDelta > 0) {
                // إضافة: إعادة حساب المتوسط المرجح بناءً على التكلفة الفعلية للوارد
                $incomingCost = $unitCost ?? $balance->avg_cost;
                $newAvgCost   = $newQuantity > 0
                    ? (($balance->quantity * $balance->avg_cost) + ($qtyDelta * $incomingCost)) / $newQuantity
                    : $balance->avg_cost;
                $effectiveUnitCost = $incomingCost;
                $balance->update([
                    'quantity'    => $newQuantity,
                    'avg_cost'    => $newAvgCost,
                    'total_value' => $newQuantity * $newAvgCost,
                ]);
            } else {
                // خصم: التكلفة الفعلية (COGS) هي المتوسط الحالي، بصرف النظر عن أي سعر بيع مُمرَّر
                $balance->update([
                    'quantity'    => $newQuantity,
                    'total_value' => $newQuantity * $balance->avg_cost,
                ]);
            }

            return StockMovement::create([
                'com_code'       => $comCode,
                'warehouse_id'   => $warehouseId,
                'item_id'        => $itemId,
                'movement_type'  => $movementType,
                'reference_type' => $refType,
                'reference_id'   => $refId,
                'quantity'       => abs($qtyDelta),
                'unit_cost'      => $effectiveUnitCost,
                'total_cost'     => abs($qtyDelta) * $effectiveUnitCost,
                'balance_after'  => $newQuantity,
                'date'           => $date ?? today(),
                'notes'          => $notes,
                'created_by'     => $createdBy,
            ]);
        });
    }

    public static function currentStock(int $comCode, int $itemId, ?int $warehouseId = null): float
    {
        $query = StockBalance::where('com_code', $comCode)->where('item_id', $itemId);
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }
        return (float) $query->sum('quantity');
    }
}
