<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeTargetEvent extends Model
{
    protected $table   = 'employee_target_events';
    protected $guarded = [];
    protected $casts   = ['redistribute_target' => 'boolean'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function replacement()
    {
        return $this->belongsTo(Employee::class, 'replacement_employee_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branche::class, 'branch_id');
    }
}
