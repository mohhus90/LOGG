<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeSanction extends Model
{
    protected $table   = 'employee_sanctions';
    protected $guarded = [];

    protected $casts = ['date' => 'date'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function addedBy()
    {
        return $this->belongsTo(Admin::class, 'added_by');
    }

    public function getTypeLabel(): string
    {
        return match ((int)$this->type) {
            1 => 'تحذير',
            2 => 'إنذار رسمي',
            3 => 'خصم مالي',
            4 => 'إيقاف عن العمل',
            5 => 'خصم باليوم',
            default => '-',
        };
    }

    public function getTypeBadge(): string
    {
        return match ((int)$this->type) {
            1 => '<span class="badge badge-warning">تحذير</span>',
            2 => '<span class="badge badge-orange" style="background:#fd7e14;color:#fff">إنذار رسمي</span>',
            3 => '<span class="badge badge-danger">خصم مالي</span>',
            4 => '<span class="badge badge-dark">إيقاف عن العمل</span>',
            5 => '<span class="badge badge-info">خصم باليوم</span>',
            default => '<span class="badge badge-secondary">-</span>',
        };
    }
}
