<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeVacationBalance extends Model
{
    use HasFactory;

    protected $table   = 'employee_vacation_balances';
    protected $guarded = [];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * إضافة الاستحقاق الشهري للرصيد
     * يُستدعى من Scheduler أول كل شهر
     */
    public function addMonthlyAccrual(): void
    {
        if ($this->monthly_accrual > 0) {
            $this->annual_balance    = round($this->annual_balance   + $this->monthly_accrual, 2);
            $this->annual_remaining  = round($this->annual_remaining + $this->monthly_accrual, 2);
            $this->save();
        }
    }

    /**
     * خصم إجازة مطلوبة من الرصيد
     * @return bool نجح الخصم أم لا يوجد رصيد كافٍ
     */
    public function deductVacation(string $type, float $days): bool
    {
        if ($type === 'annual_vacation') {
            if ($this->annual_remaining < $days) return false;
            $this->annual_used      = round($this->annual_used      + $days, 2);
            $this->annual_remaining = round($this->annual_remaining - $days, 2);
        } else {
            // casual_vacation
            if ($this->casual_remaining < $days) return false;
            $this->casual_used      = round($this->casual_used      + $days, 2);
            $this->casual_remaining = round($this->casual_remaining - $days, 2);
        }
        $this->save();
        return true;
    }

    /**
     * إنشاء رصيد سنوي جديد للموظف بناءً على إعدادات الشركة
     */
    public static function createForEmployee(Employee $employee, int $year, Admin_panel_setting $settings): self
    {
        $annual        = $settings->annual_vacation_days  ?? 21;
        $casual        = $settings->casual_vacation_days  ?? 6;
        $monthlyAccrual = $settings->monthly_vacation_balance ?? 1.75;

        return self::firstOrCreate(
            ['employee_id' => $employee->id, 'year' => $year],
            [
                'annual_balance'    => $annual,
                'annual_used'       => 0,
                'annual_remaining'  => $annual,
                'casual_balance'    => $casual,
                'casual_used'       => 0,
                'casual_remaining'  => $casual,
                'monthly_accrual'   => $monthlyAccrual,
                'com_code'          => $employee->com_code,
            ]
        );
    }
}
