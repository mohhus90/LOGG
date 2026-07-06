<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchasePayment extends Model
{
    protected $table = 'purchase_payments';
    protected $guarded = [];
    protected $casts = ['date' => 'date', 'cheque_date' => 'date'];

    public function supplier()  { return $this->belongsTo(Supplier::class, 'supplier_id'); }
    public function invoice()   { return $this->belongsTo(PurchaseInvoice::class, 'invoice_id'); }
    public function branch()    { return $this->belongsTo(Branche::class, 'branch_id'); }
    public function createdBy() { return $this->belongsTo(Admin::class, 'created_by'); }

    private static $methodLabels = [
        'cash'   => ['label' => 'نقدي',          'class' => 'success', 'icon' => 'fa-money-bill'],
        'bank'   => ['label' => 'تحويل بنكي',    'class' => 'primary', 'icon' => 'fa-university'],
        'cheque' => ['label' => 'شيك',            'class' => 'warning', 'icon' => 'fa-file-invoice'],
        'pos'    => ['label' => 'بطاقة ائتمان',  'class' => 'info',    'icon' => 'fa-credit-card'],
    ];

    public function getMethodLabelAttribute(): string
    {
        $s = self::$methodLabels[$this->payment_method] ?? ['label' => $this->payment_method, 'class' => 'secondary', 'icon' => 'fa-circle'];
        return '<span class="badge badge-'.$s['class'].'"><i class="fas '.$s['icon'].' ml-1"></i>'.$s['label'].'</span>';
    }

    public static function methodOptions(): array { return self::$methodLabels; }
}
