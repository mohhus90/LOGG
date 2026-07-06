<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionOrderMaterial extends Model
{
    protected $table = 'production_order_materials';
    protected $guarded = [];
    protected $casts = ['planned_quantity' => 'float', 'issued_quantity' => 'float', 'unit_cost' => 'float', 'total_cost' => 'float'];

    public function productionOrder() { return $this->belongsTo(ProductionOrder::class); }
    public function item()            { return $this->belongsTo(Item::class, 'item_id'); }
}
