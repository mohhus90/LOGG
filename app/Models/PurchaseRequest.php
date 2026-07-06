<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    protected $table = 'purchase_requests';
    protected $guarded = [];
    protected $casts = ['date' => 'date', 'needed_by_date' => 'date'];

    public function branch()    { return $this->belongsTo(Branche::class, 'branch_id'); }
    public function items()     { return $this->hasMany(PurchaseRequestItem::class, 'request_id'); }
    public function orders()    { return $this->hasMany(PurchaseOrder::class, 'request_id'); }
    public function createdBy() { return $this->belongsTo(Admin::class, 'created_by'); }

    private static $statusLabels = [
        'draft'     => ['label' => 'مسودة',   'class' => 'secondary'],
        'submitted' => ['label' => 'مقدم',    'class' => 'info'],
        'approved'  => ['label' => 'معتمد',   'class' => 'success'],
        'rejected'  => ['label' => 'مرفوض',   'class' => 'danger'],
        'converted' => ['label' => 'تم التحويل', 'class' => 'primary'],
    ];

    public function getStatusLabelAttribute(): string
    {
        $s = self::$statusLabels[$this->status] ?? ['label' => $this->status, 'class' => 'secondary'];
        return '<span class="badge badge-'.$s['class'].'">'.$s['label'].'</span>';
    }

    public static function statusOptions(): array { return self::$statusLabels; }
}
