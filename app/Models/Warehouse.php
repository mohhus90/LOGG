<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $table = 'warehouses';
    protected $guarded = [];

    public function branch()   { return $this->belongsTo(Branche::class, 'branch_id'); }
    public function balances() { return $this->hasMany(StockBalance::class, 'warehouse_id'); }

    public static function defaultId(int $comCode): ?int
    {
        $warehouse = static::where('com_code', $comCode)->where('is_default', true)->first()
            ?? static::where('com_code', $comCode)->where('is_active', true)->orderBy('id')->first();
        return $warehouse?->id;
    }
}
