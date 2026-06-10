<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendances';
    protected $guarded = [];

    protected $casts = [
        'attendance_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shifts_type::class, 'shift_id');
    }

    public function shiftOverride()
    {
        return $this->belongsTo(Shifts_type::class, 'shift_override_id');
    }

    public function getEffectiveShiftAttribute(): ?Shifts_type
    {
        return $this->shiftOverride ?? $this->shift;
    }

    public function addedBy()
    {
        return $this->belongsTo(Admin::class, 'added_by');
    }

    /**
     * احتساب التأخير والأوفرتايم والانصراف المبكر
     */
    public function calculateDelayAndOvertime(float $graceMinutes = 0, float $graceEarlyMinutes = 0): void
    {
        $shift = $this->effective_shift;
        if (!$shift || !$this->check_in_time || !$this->check_out_time) return;

        $shiftFrom = Carbon::parse($this->attendance_date->format('Y-m-d') . ' ' . $shift->from_time);
        $shiftTo   = Carbon::parse($this->attendance_date->format('Y-m-d') . ' ' . $shift->to_time);

        if ($shiftTo->lt($shiftFrom)) {
            $shiftTo->addDay();
        }

        $actualIn  = Carbon::parse($this->attendance_date->format('Y-m-d') . ' ' . $this->check_in_time);
        $actualOut = Carbon::parse($this->attendance_date->format('Y-m-d') . ' ' . $this->check_out_time);

        if ($actualOut->lt($actualIn)) {
            $actualOut->addDay();
        }

        // التأخير: صافي بعد دقائق السماح
        $lateMinutes = 0;
        if ($actualIn->gt($shiftFrom)) {
            $raw = $actualIn->diffInMinutes($shiftFrom);
            $lateMinutes = max(0, $raw - $graceMinutes);
        }
        $this->late_minutes = $lateMinutes;

        // الانصراف المبكر: صافي بعد دقائق السماح
        $earlyMinutes = 0;
        if ($actualOut->lt($shiftTo)) {
            $raw = $shiftTo->diffInMinutes($actualOut);
            $earlyMinutes = max(0, $raw - $graceEarlyMinutes);
        }
        $this->early_departure_minutes = $earlyMinutes;

        // الأوفرتايم
        $overtimeHours = 0;
        if ($actualOut->gt($shiftTo)) {
            $overtimeHours = round($actualOut->diffInMinutes($shiftTo) / 60, 2);
        }
        $this->overtime_hours = $overtimeHours;
    }

    /**
     * احتساب القيم المالية — يدعم ثلاثة أوضاع للتأخير:
     *   1 = دقيقة × مضاعف
     *   2 = جزء اليوم (حدود ربع/نصف/يوم)
     *   3 = هرمي مدمج (مرحلة أولى بالدقيقة ثم حدود جزء اليوم)
     *
     * الانصراف المبكر: حدود مستقلة تتجاوز وضع التأخير إذا ضُبطت
     * الأوفرتايم: إما مضاعف × سعر الساعة، أو مبلغ ثابت يومي
     *
     * @param float $delayTier1Minutes        Mode 3: حد المرحلة الأولى (< هذا → دقيقة × مضاعف)
     * @param float $delayHalfDayMinutes      Modes 2+3: حد نصف اليوم
     * @param float $delayFullDayMinutes      Modes 2+3: حد اليوم الكامل
     * @param float $earlyHalfDayMinutes      انصراف مبكر: حد نصف اليوم
     * @param float $earlyFullDayMinutes      انصراف مبكر: حد اليوم الكامل
     * @param float $earlyFullPlusHalfMinutes انصراف مبكر: حد يوم+نصف (عدم إتمام اليوم)
     */
    public function calculateAmounts(
        float $dailyRate,
        float $overtimeMultiplier = 1.5,
        float $sanctionsMultiplier = 1.0,
        bool  $overtimeEnabled = true,
        bool  $lateDeductEnabled = true,
        int   $hourDivisorType = 1,
        float $hourDivisorCustom = 8.0,
        int   $delayCalcMode = 1,
        float $afterMinuteQuarterday = 0,
        float $delayTier1Minutes = 0,
        float $delayHalfDayMinutes = 0,
        float $delayFullDayMinutes = 0,
        float $earlyHalfDayMinutes = 0,
        float $earlyFullDayMinutes = 0,
        float $earlyFullPlusHalfMinutes = 0
    ): void {
        $hourDivisor = match ($hourDivisorType) {
            2 => max(1.0, (float)($this->effective_shift->total_hour ?? 8)),
            3 => max(1.0, $hourDivisorCustom),
            default => 8.0,
        };

        $hourlyRate = $dailyRate / max(1.0, $hourDivisor);
        $minuteRate = $hourlyRate / 60;

        // ─── الأوفرتايم: مضاعف × سعر الساعة ───
        $this->overtime_amount = ($overtimeEnabled && $overtimeMultiplier > 0)
            ? round($this->overtime_hours * $hourlyRate * $overtimeMultiplier, 2)
            : 0.0;

        // ─── خصم التأخير / الانصراف المبكر ───
        if (!$lateDeductEnabled) {
            $this->late_deduction            = 0.0;
            $this->early_departure_deduction = 0.0;
            $this->late_fraction             = null;
            $this->early_departure_fraction  = null;
            return;
        }

        // دقائق صافية بعد طرح الإذن
        $effectiveLate  = max(0, (int)($this->late_minutes            ?? 0) - (int)($this->permission_minutes       ?? 0));
        $effectiveEarly = max(0, (int)($this->early_departure_minutes ?? 0) - (int)($this->permission_early_minutes ?? 0));
        $mult           = $sanctionsMultiplier > 0 ? $sanctionsMultiplier : 1.0;

        // ─── احتساب التأخير ───
        switch ($delayCalcMode) {
            case 3:
                // هرمي مدمج:
                // effectiveLate < tier1 → دقيقة × مضاعف
                // tier1 ≤ effectiveLate < quarterday → دقيقة × مضاعف (tier1 يعني ما دون حد ربع اليوم)
                // quarterday ≤ effectiveLate < halfday → ربع يوم
                // halfday ≤ effectiveLate < fullday → نصف يوم
                // effectiveLate ≥ fullday → يوم كامل
                if ($delayFullDayMinutes > 0 && $effectiveLate >= $delayFullDayMinutes) {
                    $this->late_fraction  = 3;
                    $this->late_deduction = round($dailyRate, 2);
                } elseif ($delayHalfDayMinutes > 0 && $effectiveLate >= $delayHalfDayMinutes) {
                    $this->late_fraction  = 2;
                    $this->late_deduction = round($dailyRate * 0.5, 2);
                } elseif ($afterMinuteQuarterday > 0 && $effectiveLate >= $afterMinuteQuarterday) {
                    $this->late_fraction  = 1;
                    $this->late_deduction = round($dailyRate * 0.25, 2);
                } elseif ($effectiveLate > 0) {
                    // المرحلة الأولى: دقيقة × مضاعف (حتى حد ربع اليوم)
                    $this->late_fraction  = null;
                    $this->late_deduction = round($effectiveLate * $mult * $minuteRate, 2);
                } else {
                    $this->late_fraction  = null;
                    $this->late_deduction = 0.0;
                }
                break;

            case 2:
                // جزء اليوم: مباشر حسب الحدود بدون مرحلة أولى بالدقيقة
                if ($delayFullDayMinutes > 0 && $effectiveLate >= $delayFullDayMinutes) {
                    $this->late_fraction  = 3;
                    $this->late_deduction = round($dailyRate, 2);
                } elseif ($delayHalfDayMinutes > 0 && $effectiveLate >= $delayHalfDayMinutes) {
                    $this->late_fraction  = 2;
                    $this->late_deduction = round($dailyRate * 0.5, 2);
                } elseif ($afterMinuteQuarterday > 0 && $effectiveLate >= $afterMinuteQuarterday) {
                    $this->late_fraction  = 1;
                    $this->late_deduction = round($dailyRate * 0.25, 2);
                } else {
                    $this->late_fraction  = null;
                    $this->late_deduction = 0.0;
                }
                break;

            default:
                // وضع 1: دقيقة × مضاعف
                $this->late_fraction  = null;
                $this->late_deduction = round($effectiveLate * $mult * $minuteRate, 2);
                break;
        }

        // ─── احتساب الانصراف المبكر ───
        // حدود الانصراف مستقلة عن وضع التأخير وتتجاوزه إذا ضُبطت
        if ($earlyFullPlusHalfMinutes > 0 && $effectiveEarly >= $earlyFullPlusHalfMinutes) {
            // عدم إتمام اليوم: يوم + نصف
            $this->early_departure_fraction  = 4;
            $this->early_departure_deduction = round($dailyRate * 1.5, 2);
        } elseif ($earlyFullDayMinutes > 0 && $effectiveEarly >= $earlyFullDayMinutes) {
            $this->early_departure_fraction  = 3;
            $this->early_departure_deduction = round($dailyRate, 2);
        } elseif ($earlyHalfDayMinutes > 0 && $effectiveEarly >= $earlyHalfDayMinutes) {
            $this->early_departure_fraction  = 2;
            $this->early_departure_deduction = round($dailyRate * 0.5, 2);
        } elseif ($delayCalcMode === 2 || $delayCalcMode === 3) {
            // في وضعي الجزء والهرمي: الانصراف يستخدم حد ربع اليوم كاحتياطي
            if ($afterMinuteQuarterday > 0 && $effectiveEarly >= $afterMinuteQuarterday) {
                $this->early_departure_fraction  = 1;
                $this->early_departure_deduction = round($dailyRate * 0.25, 2);
            } else {
                $this->early_departure_fraction  = null;
                $this->early_departure_deduction = 0.0;
            }
        } else {
            // وضع 1: دقيقة × مضاعف
            $this->early_departure_fraction  = null;
            $this->early_departure_deduction = round($effectiveEarly * $mult * $minuteRate, 2);
        }
    }

    // ─── Accessors ───

    public function getStatusLabelAttribute(): string
    {
        $base = match ($this->status) {
            1 => '<span class="badge badge-success">حضر</span>',
            2 => '<span class="badge badge-danger">غياب</span>',
            3 => '<span class="badge badge-warning">إجازة</span>',
            4 => '<span class="badge badge-info">إجازة رسمية</span>',
            5 => '<span class="badge badge-secondary">مأمورية</span>',
            6 => '<span class="badge" style="background:#6f42c1;color:#fff">إجازة أسبوعية</span>',
            default => '-',
        };

        if ($this->missing_punch) {
            $label = $this->missing_punch === 'out' ? 'انصراف مفقود' : 'حضور مفقود';
            $base .= ' <span class="badge badge-warning">' . $label . '</span>';
        }

        return $base;
    }

    public function getLateDisplayAttribute(): string
    {
        return match ((int)($this->late_fraction ?? 0)) {
            1 => 'ربع يوم',
            2 => 'نصف يوم',
            3 => 'يوم كامل',
            default => ($this->late_minutes ?? 0) . ' د',
        };
    }

    public function getEarlyDepartureDisplayAttribute(): string
    {
        return match ((int)($this->early_departure_fraction ?? 0)) {
            1 => 'ربع يوم',
            2 => 'نصف يوم',
            3 => 'يوم كامل',
            4 => 'يوم + نصف',
            default => ($this->early_departure_minutes ?? 0) . ' د',
        };
    }

    public function getMissingPunchResolutionLabelAttribute(): string
    {
        return match ($this->missing_punch_resolution) {
            1 => 'خصم ربع يوم',
            2 => 'خصم نصف يوم',
            3 => 'خصم يوم كامل',
            4 => 'نسيان (بدون خصم)',
            5 => 'إذن (' . ($this->missing_punch_hours ?? 0) . ' ساعة)',
            default => 'لم تُحل بعد',
        };
    }
}
