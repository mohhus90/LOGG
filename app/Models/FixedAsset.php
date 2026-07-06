<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FixedAsset extends Model
{
    protected $table = 'fixed_assets';
    protected $guarded = [];
    protected $casts = [
        'purchase_date' => 'date', 'disposal_date' => 'date',
        'purchase_cost' => 'float', 'salvage_value' => 'float',
        'accumulated_depreciation' => 'float', 'book_value' => 'float', 'disposal_amount' => 'float',
    ];

    private static $statusLabels = [
        'active'             => ['نشط', 'success'],
        'disposed'           => ['تم التخلص منه', 'danger'],
        'transferred'        => ['منقول', 'info'],
        'fully_depreciated'  => ['مستهلك بالكامل', 'secondary'],
    ];

    public function category()          { return $this->belongsTo(AssetCategory::class, 'category_id'); }
    public function branch()            { return $this->belongsTo(Branche::class, 'branch_id'); }
    public function depreciationEntries(){ return $this->hasMany(AssetDepreciationEntry::class); }
    public function transfers()         { return $this->hasMany(AssetTransfer::class); }
    public function createdBy()         { return $this->belongsTo(Admin::class, 'created_by'); }

    public function getStatusLabelAttribute(): string { return self::$statusLabels[$this->status][0] ?? $this->status; }
    public function getStatusColorAttribute(): string { return self::$statusLabels[$this->status][1] ?? 'secondary'; }
}
