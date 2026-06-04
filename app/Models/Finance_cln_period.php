<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Finance_cln_period extends Model
{
    use HasFactory;
    protected $table = 'finance_cln_periods';
    protected $fillable = [
        'finance_calenders_id', 'number_of_days', 'working_days', 'vacation_days_accrual',
        'year_of_month', 'finance_year', 'month_id', 'start_date', 'end_date', 'is_open',
        'start_date_finger_print', 'end_date_finger_print', 'com_code', 'added_by', 'updated_by',
        'created_at', 'updated_at',
    ];

    public function added()
    {
        return $this->belongsTo(Admin::class, 'added_by');
    }

    public function updatedby()
    {
        return $this->belongsTo(Admin::class, 'updated_by');
    }

    public function Month()
    {
        return $this->belongsTo(Month::class, 'month_id');
    }

    public function financeCalender()
    {
        return $this->belongsTo(Finance_calender::class, 'finance_calenders_id');
    }

    /**
     * قيمة اليوم الواحد بناءً على عدد أيام الشهر.
     * الاستخدام: $period->dailyRate($employee->emp_sal)
     */
    public function dailyRate(float $monthlySalary): float
    {
        $days = $this->working_days ?: $this->number_of_days;
        return $days > 0 ? round($monthlySalary / $days, 4) : 0;
    }

    /**
     * استحقاق الإجازة لهذا الشهر بالأيام.
     * يُعيد القيمة الخاصة بالشهر إن وجدت، وإلا يرجع للإعداد العام.
     */
    public function vacationAccrual(): float
    {
        if (!is_null($this->vacation_days_accrual)) {
            return (float) $this->vacation_days_accrual;
        }

        $setting = Admin_panel_setting::where('com_code', $this->com_code)->first();
        return $setting ? (float) $setting->monthly_vacation_balance : 1.75;
    }
}
