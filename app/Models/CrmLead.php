<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmLead extends Model
{
    protected $table = 'crm_leads';
    protected $guarded = [];

    private static $statusLabels = [
        'new'       => ['جديد', 'secondary'],
        'contacted' => ['تم التواصل', 'info'],
        'qualified' => ['مؤهّل', 'primary'],
        'converted' => ['تم التحويل', 'success'],
        'lost'      => ['خسارة', 'danger'],
    ];

    public function convertedCustomer() { return $this->belongsTo(Customer::class, 'converted_customer_id'); }
    public function opportunities()     { return $this->hasMany(CrmOpportunity::class, 'lead_id'); }
    public function createdBy()         { return $this->belongsTo(Admin::class, 'created_by'); }

    public function activities()
    {
        return CrmActivity::where('linked_type', 'lead')->where('linked_id', $this->id)->orderByDesc('activity_date');
    }

    public function getStatusLabelAttribute(): string { return self::$statusLabels[$this->status][0] ?? $this->status; }
    public function getStatusColorAttribute(): string { return self::$statusLabels[$this->status][1] ?? 'secondary'; }
}
