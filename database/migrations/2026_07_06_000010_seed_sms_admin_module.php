<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('admin_modules')->updateOrInsert(
            ['module_key' => 'sms'],
            [
                'module_key'  => 'sms',
                'module_name' => 'رسائل SMS',
                'module_icon' => 'fas fa-sms',
                'sort_order'  => 46,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('admin_modules')->where('module_key', 'sms')->delete();
    }
};
