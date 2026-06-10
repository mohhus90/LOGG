<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveCompensationRate extends Model
{
    protected $table   = 'leave_compensation_rates';
    protected $guarded = [];

    public static function getAmount(int $comCode, string $levelType, int $levelId): float
    {
        $rate = self::where('com_code', $comCode)
            ->where('level_type', $levelType)
            ->where('level_id', $levelId)
            ->first();

        return $rate ? (float)$rate->amount : 0.0;
    }
}
