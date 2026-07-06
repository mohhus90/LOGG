<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $table = 'purchase_orders';
    protected $guarded = [];
    protected $casts = ['date' => 'date', 'expected_date' => 'date'];

    public function supplier()  { return $this->belongsTo(Supplier::class, 'supplier_id'); }
    public function branch()    { return $this->belongsTo(Branche::class, 'branch_id'); }
    public function request()   { return $this->belongsTo(PurchaseRequest::class, 'request_id'); }
    public function items()     { return $this->hasMany(PurchaseOrderItem::class, 'order_id'); }
    public function invoices()  { return $this->hasMany(PurchaseInvoice::class, 'order_id'); }
    public function createdBy() { return $this->belongsTo(Admin::class, 'created_by'); }

    private static $statusLabels = [
        'draft'     => ['label' => 'مسودة',          'class' => 'secondary'],
        'confirmed' => ['label' => 'مؤكد',            'class' => 'primary'],
        'partial'   => ['label' => 'استلام جزئي',     'class' => 'warning'],
        'received'  => ['label' => 'مستلم',           'class' => 'success'],
        'cancelled' => ['label' => 'ملغي',            'class' => 'danger'],
    ];

    public function getStatusLabelAttribute(): string
    {
        $s = self::$statusLabels[$this->status] ?? ['label' => $this->status, 'class' => 'secondary'];
        return '<span class="badge badge-'.$s['class'].'">'.$s['label'].'</span>';
    }

    public static function statusOptions(): array { return self::$statusLabels; }
}
