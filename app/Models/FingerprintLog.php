<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FingerprintLog extends Model
{
    use HasFactory;

    protected $table = 'fingerprint_logs';
    protected $guarded = [];

    protected $casts = [
        'punch_time' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(FingerprintDevice::class, 'device_id');
    }
}
