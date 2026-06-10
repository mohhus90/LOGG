<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EtaCredential extends Model
{
    protected $fillable = [
        'com_code', 'auth_type', 'client_id', 'client_secret', 'taxpayer_id',
        'taxpayer_name', 'access_token', 'token_expires_at', 'is_active',
    ];

    // auth_type='portal' → client_id=email, client_secret=password (ROPC)
    // auth_type='api'    → client_id=client_id, client_secret=client_secret (client_credentials)

    protected $casts = [
        'token_expires_at' => 'datetime',
        'is_active'        => 'boolean',
    ];

    protected $hidden = ['client_secret', 'access_token'];

    public function isTokenValid(): bool
    {
        return $this->access_token
            && $this->token_expires_at
            && $this->token_expires_at->isFuture();
    }
}
