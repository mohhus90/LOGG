<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionOrder extends Model
{
    protected $table = 'production_orders';
    protected $guarded = [];
    protected $casts = [
        'planned_quantity' => 'float', 'produced_quantity' => 'float',
        'labor_cost' => 'float', 'overhead_cost' => 'float', 'material_cost' => 'float', 'total_cost' => 'float',
        'planned_start_date' => 'date', 'planned_end_date' => 'date', 'actual_start_date' => 'date', 'actual_end_date' => 'date',
    ];

    private static $statusLabels = [
        'draft'       => ['مسودة', 'secondary'],
        'in_progress' => ['قيد التنفيذ', 'warning'],
        'completed'   => ['مكتمل', 'success'],
        'cancelled'   => ['ملغي', 'danger'],
    ];

    public function bom()             { return $this->belongsTo(BillOfMaterial::class, 'bom_id'); }
    public function item()            { return $this->belongsTo(Item::class, 'item_id'); }
    public function sourceWarehouse()  { return $this->belongsTo(Warehouse::class, 'source_warehouse_id'); }
    public function targetWarehouse()  { return $this->belongsTo(Warehouse::class, 'target_warehouse_id'); }
    public function branch()          { return $this->belongsTo(Branche::class, 'branch_id'); }
    public function materials()       { return $this->hasMany(ProductionOrderMaterial::class); }
    public function receipts()        { return $this->hasMany(ProductionReceipt::class); }
    public function createdBy()       { return $this->belongsTo(Admin::class, 'created_by'); }

    public function getStatusLabelAttribute(): string { return self::$statusLabels[$this->status][0] ?? $this->status; }
    public function getStatusColorAttribute(): string { return self::$statusLabels[$this->status][1] ?? 'secondary'; }
}
