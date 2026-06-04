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

    public function addedBy()
    {
        return $this->belongsTo(Admin::class, 'added_by');
    }

    /**
     * احتساب التأخير والأوفرتايم بالمقارنة مع الشيفت
     * @param float $graceMinutes دقائق السماح قبل احتساب التأخير (من الضبط العام)
     */
    public function calculateDelayAndOvertime(float $graceMinutes = 0): void
    {
        $shift = $this->shift;
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

        // احتساب التأخير مع مراعاة دقائق السماح
        $lateMinutes = 0;
        if ($actualIn->gt($shiftFrom)) {
            $raw = $actualIn->diffInMinutes($shiftFrom);
            $lateMinutes = $raw > $graceMinutes ? $raw : 0;
        }
        $this->late_minutes = $lateMinutes;

        // احتساب الأوفرتايم
        $overtimeHours = 0;
        if ($actualOut->gt($shiftTo)) {
            $overtimeHours = round($actualOut->diffInMinutes($shiftTo) / 60, 2);
        }
        $this->overtime_hours = $overtimeHours;
    }

    /**
     * احتساب قيمة التأخير والأوفرتايم
     * @param float      $dailyRate         سعر اليوم
     * @param float      $overtimeMultiplier مضاعف الأوفرتايم (من الضبط أو مخصص للموظف)
     * @param float|null $minuteRate        سعر الدقيقة الثابت (null = يُحتسب من الراتب)
     * @param bool       $overtimeEnabled   هل يُحتسب الأوفرتايم لهذا الموظف
     * @param bool       $lateDeductEnabled هل يُحتسب خصم التأخير لهذا الموظف
     */
    public function calculateAmounts(
        float $dailyRate,
        float $overtimeMultiplier = 1.5,
        ?float $minuteRate = null,
        bool $overtimeEnabled = true,
        bool $lateDeductEnabled = true
    ): void {
        $hourlyRate = $dailyRate / 8;

        $this->overtime_amount = $overtimeEnabled
            ? round($this->overtime_hours * $hourlyRate * $overtimeMultiplier, 2)
            : 0.0;

        $rate = $minuteRate ?? ($hourlyRate / 60);
        $this->late_deduction = $lateDeductEnabled
            ? round($this->late_minutes * $rate, 2)
            : 0.0;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            1 => '<span class="badge badge-success">حضر</span>',
            2 => '<span class="badge badge-danger">غياب</span>',
            3 => '<span class="badge badge-warning">إجازة</span>',
            4 => '<span class="badge badge-info">إجازة رسمية</span>',
            5 => '<span class="badge badge-secondary">مأمورية</span>',
            default => '-',
        };
    }
}
