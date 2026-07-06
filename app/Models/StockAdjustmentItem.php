<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustmentItem extends Model
{
    protected $table = 'stock_adjustment_items';
    protected $guarded = [];

    public function adjustment() { return $this->belongsTo(StockAdjustment::class, 'adjustment_id'); }
    public function item()       { return $this->belongsTo(Item::class, 'item_id'); }
}
