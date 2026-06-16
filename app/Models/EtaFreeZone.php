<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EtaFreeZone extends Model
{
    protected $table    = 'eta_free_zones';
    protected $fillable = ['com_code', 'tax_id', 'name'];
}
