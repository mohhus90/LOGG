<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesQuotation extends Model
{
    protected $table = 'sales_quotations';
    protected $guarded = [];
    protected $casts = ['date' => 'date', 'valid_until' => 'date'];

    public function customer() { return $this->belongsTo(Customer::class, 'customer_id'); }
    public function branch()   { return $this->belongsTo(Branche::class, 'branch_id'); }
    public function items()    { return $this->hasMany(SalesQuotationItem::class, 'quotation_id')->orderBy('sort_order'); }
    public function createdBy(){ return $this->belongsTo(Admin::class, 'created_by'); }

    private static $statusLabels = [
        'draft'    => ['label' => 'مسودة',   'class' => 'secondary'],
        'sent'     => ['label' => 'مرسل',    'class' => 'info'],
        'accepted' => ['label' => 'مقبول',   'class' => 'success'],
        'rejected' => ['label' => 'مرفوض',   'class' => 'danger'],
        'expired'  => ['label' => 'منتهي',   'class' => 'warning'],
    ];

    public function getStatusLabelAttribute(): string
    {
        $s = self::$statusLabels[$this->status] ?? ['label' => $this->status, 'class' => 'secondary'];
        return '<span class="badge badge-'.$s['class'].'">'.$s['label'].'</span>';
    }

    public static function statusOptions(): array { return self::$statusLabels; }
}
