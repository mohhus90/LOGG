<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmActivity extends Model
{
    protected $table = 'crm_activities';
    protected $guarded = [];
    protected $casts = ['activity_date' => 'date'];

    private static $typeLabels = ['call' => 'مكالمة', 'meeting' => 'اجتماع', 'note' => 'ملاحظة'];

    public function createdBy() { return $this->belongsTo(Admin::class, 'created_by'); }

    public function getTypeLabelAttribute(): string { return self::$typeLabels[$this->type] ?? $this->type; }
}
