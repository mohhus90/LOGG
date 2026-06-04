<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrgTemplate extends Model
{
    use HasFactory;

    protected $fillable = ['template_name', 'company_type', 'levels_data', 'is_default'];

    protected $casts = [
        'levels_data' => 'array',
        'is_default'  => 'boolean',
    ];

    public function getCompanyTypeLabelAttribute(): string
    {
        return match ($this->company_type) {
            'retail'        => 'تجارية (بيع بالتجزئة)',
            'wholesale'     => 'تجارية (بيع بالجملة)',
            'manufacturing' => 'صناعية / تصنيع',
            'services'      => 'خدمية',
            'contracting'   => 'مقاولات',
            'medical'       => 'طبية / صحية',
            'education'     => 'تعليمية',
            'tech'          => 'تكنولوجيا',
            'hospitality'   => 'ضيافة / فنادق',
            default         => $this->company_type,
        };
    }
}
