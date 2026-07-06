<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequestItem extends Model
{
    protected $table = 'purchase_request_items';
    protected $guarded = [];

    public function request() { return $this->belongsTo(PurchaseRequest::class, 'request_id'); }
    public function item()    { return $this->belongsTo(Item::class, 'item_id'); }
    public function unit()    { return $this->belongsTo(ItemUnit::class, 'unit_id'); }
}
