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
        'day_rate_divisor_type',
        'day_rate_divisor_custom',
        'hour_rate_divisor_type',
        'hour_rate_divisor_custom',
        'max_permissions_per_day',
        'max_permission_minutes_per_day',
        // وضع التأخير الهرمي
        'delay_tier1_minutes',
        'delay_halfday_minutes',
        'delay_fullday_minutes',
        // حدود الانصراف المبكر
        'early_departure_halfday_minutes',
        'early_departure_fullday_minutes',
        'early_departure_fullplushalf_minutes',
        // إعدادات SMS
        'sms_enabled',
        'sms_api_url',
        'sms_username',
        'sms_password',
        'sms_sender',
        'sms_on_employee_create',
        'sms_on_payroll_approve',
        'sms_on_request_approve',
        'sms_on_request_reject',
        'sms_on_advance_create',
        'sms_on_sanction_create',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'day_rate_divisor_type'  => 'integer',
        'hour_rate_divisor_type' => 'integer',
        'delay_calc_mode'        => 'integer',
    ];

    // ─────────────────────────────────────────────
    // Helper: جلب إعداد حسب com_code
    // ─────────────────────────────────────────────
    public static function getByComCode(int $comCode): ?self
    {
        return self::where('com_code', $comCode)->first();
    }
}