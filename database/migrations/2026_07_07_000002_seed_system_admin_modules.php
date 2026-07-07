<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now  = now();
        $next = (int) DB::table('admin_modules')->max('sort_order') + 1;

        DB::table('admin_modules')->updateOrInsert(
            ['module_key' => 'companies'],
            ['module_name' => 'سجل الشركات', 'module_icon' => 'fas fa-city', 'sort_order' => $next, 'created_at' => $now, 'updated_at' => $now]
        );

        DB::table('admin_modules')->updateOrInsert(
            ['module_key' => 'company_profile'],
            ['module_name' => 'بيانات شركتي', 'module_icon' => 'fas fa-building', 'sort_order' => $next + 1, 'created_at' => $now, 'updated_at' => $now]
        );
    }

    public function down(): void
    {
        DB::table('admin_modules')->whereIn('module_key', ['companies', 'company_profile'])->delete();
    }
};
