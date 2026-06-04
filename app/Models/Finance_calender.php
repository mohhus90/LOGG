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

    public function setting()
    {
        return $this->hasOne(Admin_panel_setting::class, 'com_code', 'com_code');
    }

    /** الشهر الجاري المفتوح لهذه السنة */
    public function currentPeriod()
    {
        return $this->periods()->where('is_open', 0)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date',   '>=', now())
            ->first();
    }
}
