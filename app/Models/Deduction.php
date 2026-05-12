<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deduction extends Model
{
    use HasFactory;
    protected $table = 'deductions';
    protected $guarded = [];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            1 => '<span class="badge badge-danger">معتمدة</span>',
            2 => '<span class="badge badge-warning">معلقة</span>',
            3 => '<span class="badge badge-secondary">ملغاة</span>',
            default => '-',
        };
    }
}