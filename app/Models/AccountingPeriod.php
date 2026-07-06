<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AccountingPeriod extends Model
{
    protected $table = 'accounting_periods';
    protected $guarded = [];

    protected $casts = ['is_closed' => 'boolean'];

    public function closedBy() { return $this->belongsTo(Admin::class, 'closed_by'); }

    public static function forDate(int $comCode, $date): ?self
    {
        $date = Carbon::parse($date);
        return self::where('com_code', $comCode)
            ->where('fiscal_year', $date->year)
            ->where('period_month', $date->month)
            ->first();
    }

    public static function isClosedFor(int $comCode, $date): bool
    {
        $period = self::forDate($comCode, $date);
        return $period ? $period->is_closed : false;
    }
}
