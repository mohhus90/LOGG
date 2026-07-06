<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    protected $table = 'sales_orders';
    protected $guarded = [];
    protected $casts = ['date' => 'date', 'delivery_date' => 'date'];

    public function customer()   { return $this->belongsTo(Customer::class, 'customer_id'); }
    public function branch()     { return $this->belongsTo(Branche::class, 'branch_id'); }
    public function quotation()  { return $this->belongsTo(SalesQuotation::class, 'quotation_id'); }
    public function items()      { return $this->hasMany(SalesOrderItem::class, 'order_id'); }
    public function invoices()   { return $this->hasMany(SalesInvoice::class, 'order_id'); }
    public function createdBy()  { return $this->belongsTo(Admin::class, 'created_by'); }

    private static $statusLabels = [
        'draft'      => ['label' => 'مسودة',          'class' => 'secondary'],
        'confirmed'  => ['label' => 'مؤكد',            'class' => 'primary'],
        'processing' => ['label' => 'جاري التنفيذ',    'class' => 'info'],
        'partial'    => ['label' => 'تسليم جزئي',     'class' => 'warning'],
        'delivered'  => ['label' => 'مسلّم',           'class' => 'success'],
        'cancelled'  => ['label' => 'ملغي',            'class' => 'danger'],
    ];

    public function getStatusLabelAttribute(): string
    {
        $s = self::$statusLabels[$this->status] ?? ['label' => $this->status, 'class' => 'secondary'];
        return '<span class="badge badge-'.$s['class'].'">'.$s['label'].'</span>';
    }

    public static function statusOptions(): array { return self::$statusLabels; }
}
