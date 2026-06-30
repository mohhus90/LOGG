<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchCommissionPlan extends Model
{
    protected $table   = 'branch_commission_plans';
    protected $guarded = [];
    protected $casts   = ['tiers' => 'array', 'is_active' => 'boolean'];

    public function branch()
    {
        return $this->belongsTo(Branche::class, 'branch_id');
    }

    public function members()
    {
        return $this->hasMany(BranchCommissionPlanMember::class, 'plan_id');
    }

    /**
     * إيجاد الشريحة المطابقة لنسبة تحقيق التارجت.
     *
     * القواعد:
     *  - إذا كان to_pct محدد:  from_pct <= achievement <= to_pct
     *  - إذا كان to_pct فارغ:  achievement > from_pct (أكثر من الحد)
     */
    public function matchTier(float $achievementPct): ?array
    {
        if (empty($this->tiers)) {
            return null;
        }

        foreach ($this->tiers as $tier) {
            $from = (float) ($tier['from_pct'] ?? 0);
            $to   = isset($tier['to_pct']) && $tier['to_pct'] !== null
                        ? (float) $tier['to_pct']
                        : null;

            if ($to !== null) {
                if ($achievementPct >= $from && $achievementPct <= $to) {
                    return $tier;
                }
            } else {
                // شريحة مفتوحة النهاية: أكثر من from_pct
                if ($achievementPct > $from) {
                    return $tier;
                }
            }
        }

        return null;
    }

    /** ملخص الشرائح للعرض */
    public function getTiersSummaryAttribute(): string
    {
        if (empty($this->tiers)) {
            return '—';
        }
        return collect($this->tiers)->map(function ($t) {
            $from = $t['from_pct'];
            $to   = isset($t['to_pct']) && $t['to_pct'] !== null ? $t['to_pct'] . '%' : '∞';
            $sel  = $t['seller_rate']  ?? 0;
            $mgr  = $t['manager_rate'] ?? 0;
            return "{$from}%-{$to} → بائع {$sel}% / مدير {$mgr}%";
        })->implode(' | ');
    }
}
