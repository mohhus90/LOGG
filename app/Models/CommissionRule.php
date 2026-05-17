<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionRule extends Model
{
    use HasFactory;

    protected $table   = 'commission_rules';
    protected $guarded = [];
    protected $casts   = ['tiers' => 'array', 'is_active' => 'boolean'];

    public function branch()
    {
        return $this->belongsTo(branches::class, 'branch_id');
    }

    public function getBasisLabelAttribute(): string
    {
        return match ($this->basis) {
            'individual_sales' => 'مبيعات الموظف الفردية',
            'branch_sales'     => 'إجمالي مبيعات الفرع',
            'area_sales'       => 'إجمالي مبيعات المنطقة',
            'company_sales'    => 'إجمالي مبيعات الشركة',
            'fixed'            => 'مبلغ ثابت',
            'kpi_based'        => 'مرتبط بـ KPI',
            default            => $this->basis,
        };
    }

    /**
     * احتساب قيمة العمولة بناءً على مبلغ المبيعات
     */
    public function calculate(float $salesAmount): float
    {
        if ($this->calc_type === 'fixed_amount') {
            return (float) $this->fixed_amount;
        }

        if ($this->calc_type === 'tiered' && !empty($this->tiers)) {
            foreach ($this->tiers as $tier) {
                $from = (float) ($tier['from'] ?? 0);
                $to   = isset($tier['to']) && $tier['to'] !== null
                    ? (float) $tier['to']
                    : PHP_INT_MAX;

                if ($salesAmount >= $from && $salesAmount <= $to) {
                    return round($salesAmount * ((float)($tier['pct'] ?? 0) / 100), 2);
                }
            }
            return 0.0;
        }

        // percentage (default)
        return round($salesAmount * ((float)$this->percentage / 100), 2);
    }
}
