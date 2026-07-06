<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockBalance extends Model
{
    protected $table = 'stock_balances';
    protected $guarded = [];

    protected $casts = [
        'quantity'    => 'float',
        'avg_cost'    => 'float',
        'total_value' => 'float',
    ];

    public function warehouse() { return $this->belongsTo(Warehouse::class, 'warehouse_id'); }
    public function item()      { return $this->belongsTo(Item::class, 'item_id'); }
}
