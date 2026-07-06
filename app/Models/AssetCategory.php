<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetCategory extends Model
{
    protected $table = 'asset_categories';
    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean'];

    public function assetGlAccount()               { return $this->belongsTo(ChartOfAccount::class, 'asset_gl_account_id'); }
    public function accumDepreciationGlAccount()    { return $this->belongsTo(ChartOfAccount::class, 'accum_depreciation_gl_account_id'); }
    public function depreciationExpenseGlAccount()  { return $this->belongsTo(ChartOfAccount::class, 'depreciation_expense_gl_account_id'); }
    public function assets()                        { return $this->hasMany(FixedAsset::class, 'category_id'); }
}
