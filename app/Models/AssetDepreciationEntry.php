<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetDepreciationEntry extends Model
{
    protected $table = 'asset_depreciation_entries';
    protected $guarded = [];
    protected $casts = ['depreciation_amount' => 'float'];

    public function fixedAsset()   { return $this->belongsTo(FixedAsset::class); }
    public function journalEntry() { return $this->belongsTo(JournalEntry::class); }
    public function runBy()        { return $this->belongsTo(Admin::class, 'run_by'); }
}
