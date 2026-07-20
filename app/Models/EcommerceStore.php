<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EcommerceStore extends Model
{
    protected $table = 'ecommerce_stores';
    protected $guarded = [];

    protected $hidden = ['api_key'];

    protected $casts = [
        'is_active'      => 'boolean',
        'last_synced_at' => 'datetime',
    ];
}
