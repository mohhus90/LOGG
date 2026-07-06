<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillOfMaterialLine extends Model
{
    protected $table = 'bill_of_material_lines';
    protected $guarded = [];
    protected $casts = ['quantity' => 'float', 'scrap_percent' => 'float'];

    public function bom()           { return $this->belongsTo(BillOfMaterial::class, 'bom_id'); }
    public function componentItem() { return $this->belongsTo(Item::class, 'component_item_id'); }
    public function unit()          { return $this->belongsTo(ItemUnit::class, 'unit_id'); }
}
