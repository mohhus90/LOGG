<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrderItem extends Model
{
    protected $table = 'sales_order_items';
    protected $guarded = [];

    public function order() { return $this->belongsTo(SalesOrder::class, 'order_id'); }
    public function item()  { return $this->belongsTo(Item::class, 'item_id'); }
    public function unit()  { return $this->belongsTo(ItemUnit::class, 'unit_id'); }

    public function getRemainingQtyAttribute(): float
    {
        return max(0, $this->quantity - $this->delivered_qty);
    }
}
