<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiDefinition extends Model
{
    use HasFactory;

    protected $table   = 'kpi_definitions';
    protected $guarded = [];
    protected $casts   = ['affects_salary' => 'boolean', 'is_active' => 'boolean'];

    public function scores()
    {
        return $this->hasMany(KpiEmployeeScore::class, 'kpi_id');
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'performance' => 'الأداء العام',
            'quality'     => 'الجودة',
            'attendance'  => 'الانضباط',
            'sales'       => 'المبيعات',
            'custom'      => 'مخصص',
            default       => $this->category,
        };
    }

    public function getEffectTypeLabelAttribute(): string
    {
        return match ($this->salary_effect_type) {
            'bonus'     => '<span class="badge badge-success">مكافأة</span>',
            'deduction' => '<span class="badge badge-danger">خصم</span>',
            'both'      => '<span class="badge badge-warning">مكافأة أو خصم</span>',
            default     => '—',
        };
    }
}
