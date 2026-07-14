<?php

namespace App\Services;

use App\Models\Admin_panel_setting;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;

/**
 * Shared late/overtime/deduction calculation for an attendance record, used by
 * both the admin attendance screens and the Employee Self-Service mobile check-in.
 * Extracted from Admin\HR\AttendanceController so both call sites stay in sync.
 */
class AttendanceCalculationService
{
    public function resolveRates(Employee $employee, Admin_panel_setting $settings, string $date): array
    {
        $dayDivisor = match ((int)($settings->day_rate_divisor_type ?? 1)) {
            2 => 30,
            3 => Carbon::parse($date)->daysInMonth,
            4 => max(1, (float)($settings->day_rate_divisor_custom ?? 26)),
            default => 26,
        };

        return [
            'dailyRate'         => $employee->emp_sal ? ($employee->emp_sal / $dayDivisor) : 0,
            'hourDivisorType'   => (int)($settings->hour_rate_divisor_type ?? 1),
            'hourDivisorCustom' => max(1.0, (float)($settings->hour_rate_divisor_custom ?? 8)),
        ];
    }

    public function resolveOvertimeMultiplier(Employee $employee, Admin_panel_setting $settings): float
    {
        $settingsRate = (float)($settings->overtime_multiplier ?? 1.5);
        if ($settingsRate == 0.0) return 0.0;
        if (!($employee->overtime_enabled ?? 1)) return 0.0;
        return (float)($employee->custom_overtime_multiplier ?? $settingsRate);
    }

    public function buildCalcParams(Employee $employee, Admin_panel_setting $settings, string $date): array
    {
        $rates = $this->resolveRates($employee, $settings, $date);

        $sanctionsMultiplier = (float)($settings->sanctions_value_minute_delay ?? 1);
        if ($sanctionsMultiplier == 0) $sanctionsMultiplier = 1.0;

        return array_merge($rates, [
            'overtimeMultiplier'        => $this->resolveOvertimeMultiplier($employee, $settings),
            'sanctionsMultiplier'       => $sanctionsMultiplier,
            'overtimeEnabled'           => (bool)($employee->overtime_enabled ?? 1),
            'lateDeductEnabled'         => (bool)($employee->late_deduction_enabled ?? 1),
            'graceMinutes'              => (float)($settings->after_minute_calc_delay ?? 0),
            'graceEarlyMinutes'         => (float)($settings->after_minute_calc_early ?? 0),
            'delayCalcMode'             => (int)($settings->delay_calc_mode ?? 1),
            'afterMinuteQuarterday'     => (float)($settings->after_minute_quarterday ?? 0),
            'delayTier1Minutes'         => (float)($settings->delay_tier1_minutes ?? 0),
            'delayHalfDayMinutes'       => (float)($settings->delay_halfday_minutes ?? 0),
            'delayFullDayMinutes'       => (float)($settings->delay_fullday_minutes ?? 0),
            'earlyHalfDayMinutes'       => (float)($settings->early_departure_halfday_minutes ?? 0),
            'earlyFullDayMinutes'       => (float)($settings->early_departure_fullday_minutes ?? 0),
            'earlyFullPlusHalfMinutes'  => (float)($settings->early_departure_fullplushalf_minutes ?? 0),
        ]);
    }

    public function apply(Attendance $attendance, Employee $employee, Admin_panel_setting $settings, string $date): void
    {
        $p = $this->buildCalcParams($employee, $settings, $date);

        $attendance->calculateDelayAndOvertime($p['graceMinutes'], $p['graceEarlyMinutes']);
        $attendance->calculateAmounts(
            $p['dailyRate'],
            $p['overtimeMultiplier'],
            $p['sanctionsMultiplier'],
            $p['overtimeEnabled'],
            $p['lateDeductEnabled'],
            $p['hourDivisorType'],
            $p['hourDivisorCustom'],
            $p['delayCalcMode'],
            $p['afterMinuteQuarterday'],
            $p['delayTier1Minutes'],
            $p['delayHalfDayMinutes'],
            $p['delayFullDayMinutes'],
            $p['earlyHalfDayMinutes'],
            $p['earlyFullDayMinutes'],
            $p['earlyFullPlusHalfMinutes']
        );
    }
}
