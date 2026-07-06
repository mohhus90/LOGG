<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    protected $table = 'stock_adjustments';
    protected $guarded = [];
    protected $casts = ['date' => 'date'];

    public function warehouse()  { return $this->belongsTo(Warehouse::class, 'warehouse_id'); }
    public function items()      { return $this->hasMany(StockAdjustmentItem::class, 'adjustment_id'); }
    public function createdBy()  { return $this->belongsTo(Admin::class, 'created_by'); }

    private static $statusLabels = [
        'draft'    => ['label' => 'مسودة', 'class' => 'secondary'],
        'approved' => ['label' => 'معتمد', 'class' => 'success'],
        'rejected' => ['label' => 'مرفوض', 'class' => 'danger'],
    ];

    public function getStatusLabelAttribute(): string
    {
        $s = self::$statusLabels[$this->status] ?? ['label' => $this->status, 'class' => 'secondary'];
        return '<span class="badge badge-'.$s['class'].'">'.$s['label'].'</span>';
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'increase' ? 'زيادة' : 'نقص';
    }

    public static function statusOptions(): array { return self::$statusLabels; }
}
