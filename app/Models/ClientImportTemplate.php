<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientImportTemplate extends Model
{
    protected $table = 'client_import_templates';
    protected $guarded = [];
    protected $casts = [
        'mapping' => 'array',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
