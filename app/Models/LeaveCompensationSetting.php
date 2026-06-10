<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveCompensationSetting extends Model
{
    protected $table    = 'leave_compensation_settings';
    protected $guarded  = [];

    public static function getByComCode(int $comCode): ?self
    {
        return self::where('com_code', $comCode)->first();
    }

    public static function firstOrCreateForCompany(int $comCode): self
    {
        return self::firstOrCreate(
            ['com_code' => $comCode],
            ['comp_type' => 1, 'day_multiplier' => 1.5, 'fixed_level' => 'job']
        );
    }
}
