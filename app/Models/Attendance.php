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
     * احتساب التأخير والأوفرتايم تلقائيًا بالمقارنة مع الشيفت
     */
    public function calculateDelayAndOvertime(): void
    {
        $shift = $this->shift;
        if (!$shift || !$this->check_in_time || !$this->check_out_time) return;

        $shiftFrom  = Carbon::parse($this->attendance_date->format('Y-m-d') . ' ' . $shift->from_time);
        $shiftTo    = Carbon::parse($this->attendance_date->format('Y-m-d') . ' ' . $shift->to_time);

        // الشيفت يمتد لليوم التالي (مثلاً الليل)
        if ($shiftTo->lt($shiftFrom)) {
            $shiftTo->addDay();
        }

        $actualIn  = Carbon::parse($this->attendance_date->format('Y-m-d') . ' ' . $this->check_in_time);
        $actualOut = Carbon::parse($this->attendance_date->format('Y-m-d') . ' ' . $this->check_out_time);

        if ($actualOut->lt($actualIn)) {
            $actualOut->addDay();
        }

        // --- احتساب التأخير ---
        $lateMinutes = 0;
        if ($actualIn->gt($shiftFrom)) {
            $lateMinutes = $actualIn->diffInMinutes($shiftFrom);
        }
        $this->late_minutes = $lateMinutes;

        // --- احتساب الأوفرتايم ---
        $overtimeHours = 0;
        if ($actualOut->gt($shiftTo)) {
            $overtimeMinutes = $actualOut->diffInMinutes($shiftTo);
            $overtimeHours   = round($overtimeMinutes / 60, 2);
        }
        $this->overtime_hours = $overtimeHours;
    }

    /**
     * احتساب قيمة التأخير والأوفرتايم بناءً على الراتب اليومي
     */
    public function calculateAmounts(float $dailyRate): void
    {
        $hourlyRate = $dailyRate / 8; // افتراض 8 ساعات يوم عمل

        // قيمة الأوفرتايم = 1.5 ضعف سعر الساعة
        $this->overtime_amount = round($this->overtime_hours * $hourlyRate * 1.5, 2);

        // خصم التأخير = سعر الدقيقة × عدد دقائق التأخير
        $minuteRate = $hourlyRate / 60;
        $this->late_deduction = round($this->late_minutes * $minuteRate, 2);
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
