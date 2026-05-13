<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyPayroll extends Model
{
    use HasFactory;
    protected $table = 'monthly_payrolls';
    protected $guarded = [];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            1 => '<span class="badge badge-secondary">مسودة</span>',
            2 => '<span class="badge badge-primary">معتمد</span>',
            3 => '<span class="badge badge-success">مدفوع</span>',
            default => '-',
        };
    }

    public function getMonthNameAttribute(): string
    {
        $months = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر',
        ];
        return $months[$this->month] ?? '';
    }
}
