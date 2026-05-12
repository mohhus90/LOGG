<?php
// ============================================================
// نموذج السلف
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advance extends Model
{
    use HasFactory;
    protected $table = 'advances';
    protected $guarded = [];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            1 => '<span class="badge badge-primary">جارية</span>',
            2 => '<span class="badge badge-success">مسددة</span>',
            3 => '<span class="badge badge-danger">ملغاة</span>',
            default => '-',
        };
    }
}

// ============================================================
// نموذج العمولات — في ملف منفصل: app/Models/Commission.php
// ============================================================
// namespace App\Models;
// class Commission extends Model { ... }

// ============================================================
// نموذج الخصومات — في ملف منفصل: app/Models/Deduction.php
// ============================================================
// namespace App\Models;
// class Deduction extends Model { ... }

// ============================================================
// نموذج مسير الرواتب — في ملف منفصل: app/Models/MonthlyPayroll.php
// ============================================================
// namespace App\Models;
// class MonthlyPayroll extends Model { ... }
