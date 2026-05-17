<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin_panel_setting extends Model
{
    use HasFactory;

    protected $table = 'admin_panel_settings';

    /**
     * ✅ FIX: إضافة كل الحقول الجديدة للـ fillable + إصلاح 'upated_by' → 'updated_by'
     */
    protected $fillable = [
        'com_name',
        'saysem_status',
        'image',
        'logo',           // بعض التنسيقات تستخدم logo
        'phone',
        'address',
        'email',
        'added_by',
        'updated_by',     // ✅ FIX: كان 'upated_by' (خطأ إملائي)
        'com_code',
        'company_id',     // ✅ مضاف للـ companies migration
        'delay_calc_mode', // ✅ مضاف
        'after_minute_calc_delay',
        'after_minute_calc_early',
        'after_minute_quarterday',
        'after_time_half_daycut',
        'after_time_allday_daycut',
        'sanctions_value_minute_delay', // ✅ مضاف
        'sanctions_value_hour_delay',   // ✅ مضاف
        'monthly_vacation_balance',
        'first_balance_begain_vacation',
        'after_days_begain_vacation',
        'sanctions_value_first_abcence',
        'sanctions_value_second_abcence',
        'sanctions_value_third_abcence',
        'sanctions_value_forth_abcence',
        'annual_vacation_days',   // ✅ مضاف
        'casual_vacation_days',   // ✅ مضاف
        'created_at',
        'updated_at',
    ];

    // ─────────────────────────────────────────────
    // Helper: جلب إعداد حسب com_code
    // ─────────────────────────────────────────────
    public static function getByComCode(int $comCode): ?self
    {
        return self::where('com_code', $comCode)->first();
    }
}