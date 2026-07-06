<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionReceipt extends Model
{
    protected $table = 'production_receipts';
    protected $guarded = [];
    protected $casts = ['quantity' => 'float', 'unit_cost' => 'float', 'total_cost' => 'float', 'date' => 'date'];

    public function productionOrder() { return $this->belongsTo(ProductionOrder::class); }
    public function warehouse()       { return $this->belongsTo(Warehouse::class, 'warehouse_id'); }
    public function createdBy()       { return $this->belongsTo(Admin::class, 'created_by'); }
}
