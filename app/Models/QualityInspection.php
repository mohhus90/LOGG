<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QualityInspection extends Model
{
    protected $table = 'quality_inspections';
    protected $guarded = [];
    protected $casts = ['date' => 'date'];

    private static $resultLabels = [
        'pass'        => ['ناجح', 'success'],
        'fail'        => ['مرفوض', 'danger'],
        'conditional' => ['مقبول بشرط', 'warning'],
    ];
    private static $sourceTypeLabels = ['production_order' => 'أمر إنتاج', 'purchase_invoice' => 'فاتورة شراء'];

    public function checklist() { return $this->belongsTo(QualityChecklist::class, 'checklist_id'); }
    public function items()     { return $this->hasMany(QualityInspectionItem::class, 'inspection_id'); }
    public function inspector() { return $this->belongsTo(Admin::class, 'inspector_id'); }
    public function createdBy() { return $this->belongsTo(Admin::class, 'created_by'); }

    public function getResultLabelAttribute(): string { return self::$resultLabels[$this->overall_result][0] ?? $this->overall_result; }
    public function getResultColorAttribute(): string { return self::$resultLabels[$this->overall_result][1] ?? 'secondary'; }
    public function getSourceTypeLabelAttribute(): string { return self::$sourceTypeLabels[$this->source_type] ?? $this->source_type; }

    /** السجل المصدر (أمر إنتاج أو فاتورة شراء) دون علاقة morphTo حقيقية */
    public function getSourceAttribute()
    {
        return $this->source_type === 'production_order'
            ? ProductionOrder::find($this->source_id)
            : PurchaseInvoice::find($this->source_id);
    }
}
