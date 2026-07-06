<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QualityChecklistItem extends Model
{
    protected $table = 'quality_checklist_items';
    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean'];

    public function checklist() { return $this->belongsTo(QualityChecklist::class, 'checklist_id'); }
}
