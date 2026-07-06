<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesReturn extends Model
{
    protected $table = 'sales_returns';
    protected $guarded = [];
    protected $casts = ['date' => 'date'];

    public function customer()  { return $this->belongsTo(Customer::class, 'customer_id'); }
    public function invoice()   { return $this->belongsTo(SalesInvoice::class, 'invoice_id'); }
    public function branch()    { return $this->belongsTo(Branche::class, 'branch_id'); }
    public function items()     { return $this->hasMany(SalesReturnItem::class, 'return_id'); }
    public function createdBy() { return $this->belongsTo(Admin::class, 'created_by'); }

    private static $statusLabels = [
        'draft'    => ['label' => 'مسودة',  'class' => 'secondary'],
        'approved' => ['label' => 'معتمد',  'class' => 'success'],
        'rejected' => ['label' => 'مرفوض', 'class' => 'danger'],
    ];

    public function getStatusLabelAttribute(): string
    {
        $s = self::$statusLabels[$this->status] ?? ['label' => $this->status, 'class' => 'secondary'];
        return '<span class="badge badge-'.$s['class'].'">'.$s['label'].'</span>';
    }

    public static function statusOptions(): array { return self::$statusLabels; }
}
