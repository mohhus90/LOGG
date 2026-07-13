<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeSalaryHistory extends Model
{
    protected $table = 'employee_salary_history';
    protected $guarded = [];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function rule()
    {
        return $this->belongsTo(SalaryIncreaseRule::class, 'salary_increase_rule_id');
    }

    public function addedBy()
    {
        return $this->belongsTo(Admin::class, 'added_by');
    }
}
