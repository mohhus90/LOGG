<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FingerprintDevice extends Model
{
    use HasFactory;

    protected $table = 'fingerprint_devices';
    protected $guarded = [];

    protected $casts = [
        'last_sync_at' => 'datetime',
    ];

    public function logs()
    {
        return $this->hasMany(FingerprintLog::class, 'device_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            1 => '<span class="badge badge-success">نشط</span>',
            2 => '<span class="badge badge-secondary">معطل</span>',
            3 => '<span class="badge badge-danger">خطأ</span>',
            default => '-',
        };
    }

    public function getProtocolLabelAttribute(): string
    {
        return match ($this->protocol) {
            'zkteco'    => 'ZKTeco / ZKLib',
            'suprema'   => 'Suprema',
            'anviz'     => 'Anviz',
            'hikvision' => 'Hikvision',
            'dahua'     => 'Dahua',
            'generic'   => 'Generic HTTP/API',
            default     => $this->protocol,
        };
    }
}
