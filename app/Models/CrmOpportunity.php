<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmOpportunity extends Model
{
    protected $table = 'crm_opportunities';
    protected $guarded = [];
    protected $casts = ['value' => 'float', 'expected_close_date' => 'date'];

    private static $stageLabels = [
        'prospecting' => ['استكشاف', 'secondary'],
        'proposal'    => ['عرض سعر', 'info'],
        'negotiation' => ['تفاوض', 'warning'],
        'won'         => ['ناجحة', 'success'],
        'lost'        => ['خاسرة', 'danger'],
    ];

    public function lead()      { return $this->belongsTo(CrmLead::class, 'lead_id'); }
    public function customer()  { return $this->belongsTo(Customer::class, 'customer_id'); }
    public function createdBy() { return $this->belongsTo(Admin::class, 'created_by'); }

    public function activities()
    {
        return CrmActivity::where('linked_type', 'opportunity')->where('linked_id', $this->id)->orderByDesc('activity_date');
    }

    public function getStageLabelAttribute(): string { return self::$stageLabels[$this->stage][0] ?? $this->stage; }
    public function getStageColorAttribute(): string { return self::$stageLabels[$this->stage][1] ?? 'secondary'; }

    public static function stageOptions(): array { return self::$stageLabels; }
}
