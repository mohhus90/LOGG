<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReturnItem extends Model
{
    protected $table = 'purchase_return_items';
    protected $guarded = [];

    public function return_() { return $this->belongsTo(PurchaseReturn::class, 'return_id'); }
    public function item()    { return $this->belongsTo(Item::class, 'item_id'); }
    public function unit()    { return $this->belongsTo(ItemUnit::class, 'unit_id'); }
}
