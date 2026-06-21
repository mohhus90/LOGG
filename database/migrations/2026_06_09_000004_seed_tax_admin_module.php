<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // تسجيل موديول الضرائب في جدول صلاحيات الأدمن
        $exists = DB::table('admin_modules')->where('module_key', 'tax')->exists();
        if (!$exists) {
            DB::table('admin_modules')->insert([
                'module_key'  => 'tax',
                'module_name' => 'الضرائب والفواتير الإلكترونية',
                'module_icon' => 'fas fa-file-invoice-dollar',
                'sort_order'  => 90,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('admin_modules')->where('module_key', 'tax')->delete();
    }
};
