<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashBox extends Model
{
    protected $table = 'cash_boxes';
    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean', 'opening_balance' => 'float', 'current_balance' => 'float'];

    public function branch()    { return $this->belongsTo(Branche::class, 'branch_id'); }
    public function glAccount() { return $this->belongsTo(ChartOfAccount::class, 'gl_account_id'); }
}
