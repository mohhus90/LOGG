<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\EmployeeVacationBalance;


class Employee extends Model
{
    use HasFactory;
    
    protected $table = "employees";
    protected $guarded = [];
 
    public function addedBy(){
        return $this->belongsTo(Admin::class,'added_by');
    }
    public function updatedBy(){
        return $this->belongsTo('App\Models\Admin','updated_by');
    }
    public function department()
    {
        return $this->belongsTo(Department::class, 'emp_departments_id');
    }
    public function jobs_categories()
    {
        return $this->belongsTo(Jobs_categories::class, 'emp_jobs_id');
    }
    public function shifts_type()
    {
        return $this->belongsTo(Shifts_type::class, 'shifts_types_id');
    }
    public function comp()
    {
        return $this->belongsTo(Admin_panel_setting::class, 'com_code');
    }
    public function branches()
    {
        return $this->belongsTo(Branche::class, 'branches_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function vacationBalance()
    {
        return $this->hasMany(EmployeeVacationBalance::class, 'employee_id');
    }

    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class, 'employee_id');
    }

    public function salaryHistory()
    {
        return $this->hasMany(EmployeeSalaryHistory::class, 'employee_id')->latest('effective_date');
    }

    public static function generateLoginPassword(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $password = '';
        for ($i = 0; $i < 4; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $password . random_int(1000, 9999);
    }
}
