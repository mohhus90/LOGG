<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetTransfer extends Model
{
    protected $table = 'asset_transfers';
    protected $guarded = [];
    protected $casts = ['transfer_date' => 'date'];

    public function fixedAsset()  { return $this->belongsTo(FixedAsset::class); }
    public function fromBranch()  { return $this->belongsTo(Branche::class, 'from_branch_id'); }
    public function toBranch()    { return $this->belongsTo(Branche::class, 'to_branch_id'); }
}
