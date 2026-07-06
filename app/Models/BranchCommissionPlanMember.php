<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchCommissionPlanMember extends Model
{
    protected $table   = 'branch_commission_plan_members';
    protected $guarded = [];
    protected $casts   = ['also_as_seller' => 'boolean'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function plan()
    {
        return $this->belongsTo(BranchCommissionPlan::class, 'plan_id');
    }
}
