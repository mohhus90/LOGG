<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $table = 'stock_movements';
    protected $guarded = [];
    protected $casts = ['date' => 'date'];

    public function warehouse() { return $this->belongsTo(Warehouse::class, 'warehouse_id'); }
    public function item()      { return $this->belongsTo(Item::class, 'item_id'); }
    public function createdBy() { return $this->belongsTo(Admin::class, 'created_by'); }

    private static $typeLabels = [
        'purchase_in'         => ['label' => 'وارد شراء',       'class' => 'success', 'sign' => 1],
        'purchase_return_out' => ['label' => 'مرتجع شراء',      'class' => 'danger',  'sign' => -1],
        'sales_out'           => ['label' => 'صادر بيع',         'class' => 'primary', 'sign' => -1],
        'sales_return_in'     => ['label' => 'مرتجع بيع',        'class' => 'info',    'sign' => 1],
        'adjustment_in'       => ['label' => 'تسوية زيادة',      'class' => 'success', 'sign' => 1],
        'adjustment_out'      => ['label' => 'تسوية نقص',        'class' => 'danger',  'sign' => -1],
        'transfer_in'         => ['label' => 'تحويل وارد',       'class' => 'secondary','sign' => 1],
        'transfer_out'        => ['label' => 'تحويل صادر',       'class' => 'secondary','sign' => -1],
    ];

    public function getTypeLabelAttribute(): string
    {
        $t = self::$typeLabels[$this->movement_type] ?? ['label' => $this->movement_type, 'class' => 'secondary'];
        return '<span class="badge badge-'.$t['class'].'">'.$t['label'].'</span>';
    }

    public function getSignedQuantityAttribute(): float
    {
        $sign = self::$typeLabels[$this->movement_type]['sign'] ?? 1;
        return $this->quantity * $sign;
    }

    public static function typeOptions(): array { return self::$typeLabels; }
}
