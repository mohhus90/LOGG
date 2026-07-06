<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('admin_modules')->updateOrInsert(
            ['module_key' => 'bi_dashboard'],
            ['module_name' => 'التقارير والتحليلات', 'module_icon' => 'fas fa-chart-pie', 'sort_order' => 91, 'created_at' => now(), 'updated_at' => now()]
        );
    }

    public function down(): void
    {
        DB::table('admin_modules')->where('module_key', 'bi_dashboard')->delete();
    }
};
