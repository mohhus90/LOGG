<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // تغيير النوع من TINYINT(1) — الذي يُعامله PDO كـ boolean — إلى SMALLINT
        DB::statement('ALTER TABLE admin_panel_settings MODIFY day_rate_divisor_type SMALLINT NOT NULL DEFAULT 1');
        DB::statement('ALTER TABLE admin_panel_settings MODIFY hour_rate_divisor_type SMALLINT NOT NULL DEFAULT 1');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE admin_panel_settings MODIFY day_rate_divisor_type TINYINT NOT NULL DEFAULT 1');
        DB::statement('ALTER TABLE admin_panel_settings MODIFY hour_rate_divisor_type TINYINT NOT NULL DEFAULT 1');
    }
};
