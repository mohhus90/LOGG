<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $table = 'items';
    protected $guarded = [];

    protected static $typeLabels = [
        'product'       => 'منتج تام',
        'service'       => 'خدمة',
        'raw_material'  => 'مادة خام',
        'semi_finished' => 'نصف مصنع',
    ];

    public function category()      { return $this->belongsTo(ItemCategory::class, 'category_id'); }
    public function unit()          { return $this->belongsTo(ItemUnit::class, 'unit_id'); }
    public function stockBalances() { return $this->hasMany(StockBalance::class, 'item_id'); }

    public function getTypeLabelAttribute(): string
    {
        return self::$typeLabels[$this->type] ?? $this->type;
    }

    public static function typeLabels(): array { return self::$typeLabels; }
}
