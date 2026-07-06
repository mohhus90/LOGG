<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QualityInspectionItem extends Model
{
    protected $table = 'quality_inspection_items';
    protected $guarded = [];

    public function inspection()   { return $this->belongsTo(QualityInspection::class, 'inspection_id'); }
    public function checklistItem(){ return $this->belongsTo(QualityChecklistItem::class, 'checklist_item_id'); }
}
