<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Finance_cln_period extends Model
{
    use HasFactory;
    protected $table ="finance_cln_periods";
    protected $fillable = [
        'finance_calenders_id', 'number_of_days', 'year_of_month', 'finance_year', 'month_id', 'start_date', 'end_date', 'is_open', 'start_date_finger_print', 'end_date_finger_print', 'com_code', 'added_by', 'updated_by', 'created_at', 'updated_at'    ];

    public function added(){
        return $this->belongsTo('App\Models\Admin','added_by');
    }
    public function updatedby(){
        return $this->belongsTo('App\Models\Admin','updated_by');
    }
    public function Month(){
        return $this->belongsTo('App\Models\Month','month_id');
    }
}
