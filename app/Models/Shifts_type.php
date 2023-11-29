<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shifts_type extends Model
{
    use HasFactory;
    protected $table = "shifts_types";
    protected $fillable = [
        'type', 'from_time', 'to_time', 'total_hour', 'com_code', 'added_by', 'updated_by', 'created_at', 'updated_at'
    ];
    
    public function added(){
        return $this->belongsTo('App\Models\Admin','added_by');
    }
    public function updatedby(){
        return $this->belongsTo('App\Models\Admin','updated_by');
    }

}
