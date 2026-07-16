<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollFactor extends Model
{
    protected $table = 'payroll_factors';
    protected $guarded = [];
    protected $casts = [
        'is_held' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
