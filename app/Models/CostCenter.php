<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CostCenter extends Model
{
    protected $table = 'cost_centers';
    protected $guarded = [];

    protected $casts = ['is_active' => 'boolean'];

    public function parent()   { return $this->belongsTo(CostCenter::class, 'parent_id'); }
    public function children() { return $this->hasMany(CostCenter::class, 'parent_id'); }
    public function branch()   { return $this->belongsTo(Branche::class, 'branch_id'); }
}
