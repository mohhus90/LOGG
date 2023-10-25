<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Finance_calender extends Model
{
    use HasFactory;
    protected $table ="Finance_calenders";
    protected $fillable = [
        'finance_yr', 'finance_yr_desc', 'start_date', 'end_date', 'is_open', 'com_code', 'added_by', 'updated_by', 'created_at', 'updated_at'
    ];
    public function added(){
        return $this->belongsTo('App\Models\Admin','added_by');
    }
    public function updatedby(){
        return $this->belongsTo('App\Models\Admin','updated_by');
    }
}
