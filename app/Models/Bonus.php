<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bonus extends Model
{
    use HasFactory;
    protected $table = 'bonuses';
    protected $guarded = [];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            1 => '<span class="badge badge-success">معتمدة</span>',
            2 => '<span class="badge badge-warning">معلقة</span>',
            3 => '<span class="badge badge-danger">ملغاة</span>',
            default => '-',
        };
    }

    public function getTypeNameAttribute(): string
    {
        return $this->bonus_type == 2 ? 'أيام × مضاعف' : 'مبلغ ثابت';
    }

    /**
     * احتساب قيمة المكافأة بناءً على معدل اليوم
     * للنوع 1: المبلغ الثابت مباشرةً
     * للنوع 2: عدد الأيام × معدل اليوم × المضاعف
     */
    public function calcAmount(float $dailyRate): float
    {
        if ($this->bonus_type == 2) {
            return round((float)$this->days * $dailyRate * (float)$this->day_multiplier, 2);
        }
        return round((float)$this->amount, 2);
    }
}
