<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $table = 'purchase_order_items';
    protected $guarded = [];

    public function order() { return $this->belongsTo(PurchaseOrder::class, 'order_id'); }
    public function item()  { return $this->belongsTo(Item::class, 'item_id'); }
    public function unit()  { return $this->belongsTo(ItemUnit::class, 'unit_id'); }

    public function getRemainingQtyAttribute(): float
    {
        return max(0, $this->quantity - $this->received_qty);
    }
}
