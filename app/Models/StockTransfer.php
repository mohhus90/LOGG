<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    protected $table = 'stock_transfers';
    protected $guarded = [];
    protected $casts = ['date' => 'date'];

    public function fromWarehouse() { return $this->belongsTo(Warehouse::class, 'from_warehouse_id'); }
    public function toWarehouse()   { return $this->belongsTo(Warehouse::class, 'to_warehouse_id'); }
    public function items()        { return $this->hasMany(StockTransferItem::class, 'transfer_id'); }
    public function createdBy()    { return $this->belongsTo(Admin::class, 'created_by'); }

    private static $statusLabels = [
        'draft'     => ['label' => 'مسودة', 'class' => 'secondary'],
        'completed' => ['label' => 'منفذ',  'class' => 'success'],
        'cancelled' => ['label' => 'ملغي',  'class' => 'danger'],
    ];

    public function getStatusLabelAttribute(): string
    {
        $s = self::$statusLabels[$this->status] ?? ['label' => $this->status, 'class' => 'secondary'];
        return '<span class="badge badge-'.$s['class'].'">'.$s['label'].'</span>';
    }

    public static function statusOptions(): array { return self::$statusLabels; }
}
