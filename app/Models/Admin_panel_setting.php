<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin_panel_setting extends Model
{
    use HasFactory;
    protected $table ="admin_panel_settings";
    protected $fillable = [
        'com_name', 'saysem_status', 'image', 'phone', 'address', 'email', 'addes_by', 'upated_by', 'com_code', 'after_minute_calc_delay', 'after_minute_calc_early', 'after_minute_quarterday', 'after_time_half_daycut', 'after_time_allday_daycut', 'monthly_vacation_balance', 'first_balance_begain_vacation', 'after_days_begain_vacation', 'sanctions_value_first_abcence', 'sanctions_value_second_abcence', 'sanctions_value_third_abcence', 'sanctions_value_forth_abcence', 'created_at', 'updated_at'
    ];

}
