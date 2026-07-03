<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        DB::table('admin_modules')->updateOrInsert(
            ['module_key' => 'bonuses'],
            [
                'module_key'  => 'bonuses',
                'module_name' => 'المكافآت',
                'module_icon' => 'fas fa-gift',
                'sort_order'  => 10,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]
        );

        // إعادة ترتيب الوحدات التي تأتي بعد الترتيب 10
        DB::table('admin_modules')
            ->whereNotIn('module_key', ['bonuses'])
            ->where('sort_order', '>=', 10)
            ->increment('sort_order');
    }

    public function down(): void
    {
        DB::table('admin_modules')->where('module_key', 'bonuses')->delete();
    }
};
