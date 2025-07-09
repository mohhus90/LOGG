<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Employee extends Model
{
    use HasFactory;
    
    protected $table = "employees";
    protected $guarded = [];
 
    public function added(){
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
        return $this->belongsTo(Admin::class, 'com_code');
    }
    public function branches()
    {
        return $this->belongsTo(Branche::class, 'branches_id');
    }
}
