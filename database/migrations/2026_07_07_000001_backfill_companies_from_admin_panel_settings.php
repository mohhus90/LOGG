<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * يُكمل استبدال com_code بجدول companies الحقيقي (كان مؤجَّلاً منذ migration
 * 2024_04_01_000001): لكل شركة قائمة فعليًا (عبر admin_panel_settings) بدون
 * company_id، يُنشأ صف companies مطابق، ثم يُربط admin_panel_settings.company_id
 * و admins.company_id به. لا تعديل على أعمدة admin_panel_settings الحالية.
 */
return new class extends Migration
{
    public function up(): void
    {
        $settings = DB::table('admin_panel_settings')
            ->whereNull('company_id')
            ->get(['id', 'com_code', 'com_name', 'phone', 'email', 'address', 'image', 'saysem_status']);

        foreach ($settings as $setting) {
            $baseSlug = Str::slug($setting->com_name ?: ('company-' . $setting->com_code));
            $slug     = ($baseSlug ?: 'company') . '-' . $setting->com_code;

            $companyId = DB::table('companies')->where('slug', $slug)->value('id');

            if (!$companyId) {
                $companyId = DB::table('companies')->insertGetId([
                    'name'       => $setting->com_name ?: ('شركة ' . $setting->com_code),
                    'slug'       => $slug,
                    'phone'      => $setting->phone,
                    'email'      => $setting->email,
                    'address'    => $setting->address,
                    'logo'       => $setting->image,
                    'is_active'  => $setting->saysem_status ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('admin_panel_settings')->where('id', $setting->id)->update(['company_id' => $companyId]);
            DB::table('admins')->where('com_code', $setting->com_code)->whereNull('company_id')->update(['company_id' => $companyId]);
        }
    }

    public function down(): void
    {
        // بيانات تاريخية مُدمجة — بدون تراجع تلقائي لتفادي حذف شركات قد تُستخدم لاحقًا.
    }
};
