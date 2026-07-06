<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $table = 'documents';
    protected $guarded = [];
    protected $casts = ['approved_at' => 'datetime'];

    private static $statusLabels = [
        'draft'    => ['مسودة', 'secondary'],
        'pending'  => ['قيد المراجعة', 'warning'],
        'approved' => ['معتمدة', 'success'],
        'rejected' => ['مرفوضة', 'danger'],
    ];

    public function category()  { return $this->belongsTo(DocumentCategory::class, 'category_id'); }
    public function approver()  { return $this->belongsTo(Admin::class, 'approved_by'); }
    public function uploadedBy(){ return $this->belongsTo(Admin::class, 'uploaded_by'); }

    public function getStatusLabelAttribute(): string { return self::$statusLabels[$this->status][0] ?? $this->status; }
    public function getStatusColorAttribute(): string { return self::$statusLabels[$this->status][1] ?? 'secondary'; }
}
