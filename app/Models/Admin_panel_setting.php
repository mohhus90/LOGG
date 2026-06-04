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
        'logo',
        'phone',
        'address',
        'email',
        'added_by',
        'updated_by',
        'com_code',
        'company_id',
        'delay_calc_mode',
        'after_minute_calc_delay',
        'after_minute_calc_early',
        'after_minute_quarterday',
        'after_time_half_daycut',
        'after_time_allday_daycut',
        'sanctions_value_minute_delay',
        // sanctions_value_hour_delay محذوف — غير مستخدم، تم استبداله بالدقيقة
        'overtime_multiplier',
        'employee_insurance_rate',
        'company_insurance_rate',
        'monthly_vacation_balance',
        'first_balance_begain_vacation',
        'after_days_begain_vacation',
        'sanctions_value_first_abcence',
        'sanctions_value_second_abcence',
        'sanctions_value_third_abcence',
        'sanctions_value_forth_abcence',
        'annual_vacation_days',
        'casual_vacation_days',
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