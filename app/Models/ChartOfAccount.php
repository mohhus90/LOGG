<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    protected $table = 'chart_of_accounts';
    protected $guarded = [];

    protected $casts = [
        'is_group'          => 'boolean',
        'is_active'         => 'boolean',
        'allow_cost_center' => 'boolean',
        'opening_balance'   => 'float',
        'current_balance'   => 'float',
    ];

    protected static $typeLabels = [
        'asset'     => 'أصول',
        'liability' => 'التزامات',
        'equity'    => 'حقوق ملكية',
        'revenue'   => 'إيرادات',
        'expense'   => 'مصروفات',
    ];

    public function parent()   { return $this->belongsTo(ChartOfAccount::class, 'parent_id'); }
    public function children() { return $this->hasMany(ChartOfAccount::class, 'parent_id'); }
    public function lines()    { return $this->hasMany(JournalEntryLine::class, 'account_id'); }

    public function getTypeLabelAttribute(): string
    {
        return self::$typeLabels[$this->account_type] ?? $this->account_type;
    }

    public static function typeLabels(): array { return self::$typeLabels; }

    public function getFullCodeNameAttribute(): string
    {
        return $this->account_code.' - '.$this->account_name;
    }
}
