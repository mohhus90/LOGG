<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrgLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'name_en', 'level_order', 'parent_id', 'level_type',
        'is_management', 'is_sales_role', 'receives_seller_commission',
        'receives_manager_commission', 'description', 'com_code',
        'added_by', 'updated_by',
    ];

    protected $casts = [
        'is_management' => 'boolean',
        'is_sales_role' => 'boolean',
        'receives_seller_commission' => 'boolean',
        'receives_manager_commission' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(OrgLevel::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(OrgLevel::class, 'parent_id')->orderBy('level_order');
    }

    public function jobs()
    {
        return $this->hasMany(Jobs_categories::class, 'org_level_id');
    }

    public function getLevelTypeLabelAttribute(): string
    {
        return match ($this->level_type) {
            'top_management'    => 'إدارة عليا',
            'middle_management' => 'إدارة وسطى',
            'supervisor'        => 'مشرف',
            'sales'             => 'مبيعات',
            'operational'       => 'تشغيلي',
            'support'           => 'دعم',
            default             => 'أخرى',
        };
    }

    public function getLevelTypeBadgeAttribute(): string
    {
        $colors = [
            'top_management'    => 'danger',
            'middle_management' => 'warning',
            'supervisor'        => 'info',
            'sales'             => 'success',
            'operational'       => 'primary',
            'support'           => 'secondary',
            'other'             => 'dark',
        ];
        $color = $colors[$this->level_type] ?? 'dark';
        return "<span class='badge bg-{$color}'>{$this->level_type_label}</span>";
    }
}
