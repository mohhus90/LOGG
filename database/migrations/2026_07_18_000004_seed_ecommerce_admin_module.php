<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now = now();

        DB::table('admin_modules')->updateOrInsert(
            ['module_key' => 'sales_ecommerce_stores'],
            [
                'module_key'  => 'sales_ecommerce_stores',
                'module_name' => 'ربط المتاجر الإلكترونية',
                'module_icon' => 'fas fa-store',
                'sort_order'  => 30,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]
        );
    }

    public function down(): void
    {
        DB::table('admin_modules')->where('module_key', 'sales_ecommerce_stores')->delete();
    }
};
