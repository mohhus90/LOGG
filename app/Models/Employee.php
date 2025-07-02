<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Employee extends Model
{
    use HasFactory;
    
    protected $table = "employees";
    protected $guarded = [];
 
    public function addedBy(){
        return $this->belongsTo('App\Models\Admin','added_by');
    }
    public function updatedBy(){
        return $this->belongsTo('App\Models\Admin','updated_by');
    }
}
