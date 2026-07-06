<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlPostingRule extends Model
{
    protected $table = 'gl_posting_rules';
    protected $guarded = [];

    protected $casts = ['is_active' => 'boolean'];

    public function account() { return $this->belongsTo(ChartOfAccount::class, 'account_id'); }
}
