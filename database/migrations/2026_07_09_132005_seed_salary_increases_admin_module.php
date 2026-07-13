<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        DB::table('admin_modules')->updateOrInsert(
            ['module_key' => 'salary_increases'],
            [
                'module_key'  => 'salary_increases',
                'module_name' => 'زيادات الرواتب',
                'module_icon' => 'fas fa-arrow-trend-up',
                'sort_order'  => 200,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]
        );
    }

    public function down(): void
    {
        DB::table('admin_modules')->where('module_key', 'salary_increases')->delete();
    }
};
