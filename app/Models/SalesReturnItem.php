<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesReturnItem extends Model
{
    protected $table = 'sales_return_items';
    protected $guarded = [];

    public function salesReturn() { return $this->belongsTo(SalesReturn::class, 'return_id'); }
    public function item()        { return $this->belongsTo(Item::class, 'item_id'); }
    public function unit()        { return $this->belongsTo(ItemUnit::class, 'unit_id'); }
}
