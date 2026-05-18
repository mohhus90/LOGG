<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ✅ FIX: اسم الجدول في الـ migration هو 'finance_calenders' (lowercase)
 * لكن الـ model كان يستخدم 'Finance_calenders' (يسبب مشكلة على Linux)
 */
class Finance_calender extends Model
{
    use HasFactory;

    // ✅ FIX: lowercase اسم الجدول
    protected $table = "finance_calenders";

    protected $fillable = [
        'finance_yr',
        'finance_yr_desc',
        'start_date',
        'end_date',
        'is_open',
        'com_code',
        'added_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    public function added()
    {
        return $this->belongsTo(Admin::class, 'added_by');
    }

    public function updatedby()
    {
        return $this->belongsTo(Admin::class, 'updated_by');
    }

    public function periods()
    {
        return $this->hasMany(Finance_cln_period::class, 'finance_calenders_id');
    }
}
