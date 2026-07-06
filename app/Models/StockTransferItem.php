<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransferItem extends Model
{
    protected $table = 'stock_transfer_items';
    protected $guarded = [];

    public function transfer() { return $this->belongsTo(StockTransfer::class, 'transfer_id'); }
    public function item()     { return $this->belongsTo(Item::class, 'item_id'); }
}
