<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QualityChecklist extends Model
{
    protected $table = 'quality_checklists';
    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean'];

    private static $appliesToLabels = ['production' => 'إنتاج', 'purchase' => 'شراء', 'both' => 'إنتاج وشراء'];

    public function items() { return $this->hasMany(QualityChecklistItem::class, 'checklist_id'); }

    public function getAppliesToLabelAttribute(): string
    {
        return self::$appliesToLabels[$this->applies_to] ?? $this->applies_to;
    }
}
