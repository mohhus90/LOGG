<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiEmployeeScore extends Model
{
    use HasFactory;

    protected $table   = 'kpi_employee_scores';
    protected $guarded = [];

    public function kpi()
    {
        return $this->belongsTo(KpiDefinition::class, 'kpi_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * احتساب نسبة الإنجاز والتأثير المالي على الراتب
     * يُستدعى بعد تعيين actual_value
     */
    public function calculate(float $basicSalary): void
    {
        $kpi    = $this->kpi;
        if (!$kpi) return;

        $target = $kpi->target_value ?: 1;
        $pct    = ($this->actual_value / $target) * 100;

        $this->achievement_pct = round($pct, 2);
        $this->score           = round($pct * $kpi->weight / 100, 2);

        if ($kpi->affects_salary && $basicSalary > 0) {
            if ($pct >= 100 && $kpi->max_bonus_pct > 0
                && in_array($kpi->salary_effect_type, ['bonus', 'both'])) {
                // مكافأة: الزيادة فوق 100% تُحوَّل إلى مكافأة
                $bonusPct = min(
                    ($pct - 100) / 100 * $kpi->max_bonus_pct,
                    $kpi->max_bonus_pct
                );
                $this->salary_effect_amount = round($basicSalary * $bonusPct / 100, 2);
                $this->effect_direction     = 1;

            } elseif ($pct < 100 && $kpi->max_deduction_pct > 0
                && in_array($kpi->salary_effect_type, ['deduction', 'both'])) {
                // خصم: النقص عن 100% يُحوَّل إلى خصم
                $deductPct = min(
                    (100 - $pct) / 100 * $kpi->max_deduction_pct,
                    $kpi->max_deduction_pct
                );
                $this->salary_effect_amount = round($basicSalary * $deductPct / 100, 2);
                $this->effect_direction     = 2;
            } else {
                $this->salary_effect_amount = 0;
                $this->effect_direction     = 1;
            }
        } else {
            $this->salary_effect_amount = 0;
        }
    }
}
