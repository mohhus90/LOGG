<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillOfMaterial extends Model
{
    protected $table = 'bill_of_materials';
    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean', 'output_quantity' => 'float'];

    public function item()  { return $this->belongsTo(Item::class, 'item_id'); }
    public function lines() { return $this->hasMany(BillOfMaterialLine::class, 'bom_id'); }
}
