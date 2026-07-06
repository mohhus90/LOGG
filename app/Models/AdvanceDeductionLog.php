<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvanceDeductionLog extends Model
{
    protected $table = 'advance_deduction_logs';
    protected $guarded = [];

    public function advance()
    {
        return $this->belongsTo(Advance::class);
    }

    public function payroll()
    {
        return $this->belongsTo(MonthlyPayroll::class, 'monthly_payroll_id');
    }
}
