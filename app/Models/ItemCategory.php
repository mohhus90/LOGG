<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemCategory extends Model
{
    protected $table = 'item_categories';
    protected $guarded = [];

    public function parent()   { return $this->belongsTo(ItemCategory::class, 'parent_id'); }
    public function children() { return $this->hasMany(ItemCategory::class, 'parent_id'); }
    public function items()    { return $this->hasMany(Item::class, 'category_id'); }
}
