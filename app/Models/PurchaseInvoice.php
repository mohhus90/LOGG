<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseInvoice extends Model
{
    protected $table = 'purchase_invoices';
    protected $guarded = [];
    protected $casts = ['date' => 'date', 'due_date' => 'date'];

    public function supplier()  { return $this->belongsTo(Supplier::class, 'supplier_id'); }
    public function branch()    { return $this->belongsTo(Branche::class, 'branch_id'); }
    public function order()     { return $this->belongsTo(PurchaseOrder::class, 'order_id'); }
    public function items()     { return $this->hasMany(PurchaseInvoiceItem::class, 'invoice_id'); }
    public function payments()  { return $this->hasMany(PurchasePayment::class, 'invoice_id'); }
    public function createdBy() { return $this->belongsTo(Admin::class, 'created_by'); }

    private static $paymentStatusLabels = [
        'unpaid'  => ['label' => 'غير مسدد',      'class' => 'danger'],
        'partial' => ['label' => 'مسدد جزئياً',   'class' => 'warning'],
        'paid'    => ['label' => 'مسدد بالكامل',  'class' => 'success'],
    ];

    private static $statusLabels = [
        'draft'     => ['label' => 'مسودة',   'class' => 'secondary'],
        'received'  => ['label' => 'مستلمة',  'class' => 'primary'],
        'cancelled' => ['label' => 'ملغاة',   'class' => 'danger'],
    ];

    public function getPaymentStatusLabelAttribute(): string
    {
        $s = self::$paymentStatusLabels[$this->payment_status] ?? ['label' => $this->payment_status, 'class' => 'secondary'];
        return '<span class="badge badge-'.$s['class'].'">'.$s['label'].'</span>';
    }

    public function getStatusLabelAttribute(): string
    {
        $s = self::$statusLabels[$this->status] ?? ['label' => $this->status, 'class' => 'secondary'];
        return '<span class="badge badge-'.$s['class'].'">'.$s['label'].'</span>';
    }

    public static function paymentStatusOptions(): array { return self::$paymentStatusLabels; }
    public static function statusOptions(): array        { return self::$statusLabels; }
}
