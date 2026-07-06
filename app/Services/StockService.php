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
                ]);
            }

            $newQuantity = $balance->quantity + $qtyDelta;
            $balance->update(['quantity' => $newQuantity]);

            return StockMovement::create([
                'com_code'       => $comCode,
                'warehouse_id'   => $warehouseId,
                'item_id'        => $itemId,
                'movement_type'  => $movementType,
                'reference_type' => $refType,
                'reference_id'   => $refId,
                'quantity'       => abs($qtyDelta),
                'unit_cost'      => $unitCost,
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
