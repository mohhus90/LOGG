<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminModule extends Model
{
    use HasFactory;

    protected $table = 'admin_modules';
    protected $guarded = [];

    public function permissions()
    {
        return $this->hasMany(AdminPermission::class, 'module_id');
    }
}
