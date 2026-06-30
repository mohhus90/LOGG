<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeBranchTarget extends Model
{
    protected $table   = 'employee_branch_targets';
    protected $guarded = [];

    public function plan()
    {
        return $this->belongsTo(BranchCommissionPlan::class, 'plan_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
