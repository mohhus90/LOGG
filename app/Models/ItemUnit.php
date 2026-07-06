<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemUnit extends Model
{
    protected $table = 'item_units';
    protected $guarded = [];

    public function items() { return $this->hasMany(Item::class, 'unit_id'); }
}
