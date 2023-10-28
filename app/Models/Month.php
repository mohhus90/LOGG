<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Month extends Model
{
    use HasFactory;
    protected $table ="monthes";
    protected $fillable = [
        'monthe_name', 'monthe_name_en'
    ];

}
