<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_panel_settings', function (Blueprint $table) {
            // ── مقسوم سعر اليوم ──
            if (!Schema::hasColumn('admin_panel_settings', 'day_rate_divisor_type')) {
                $table->tinyInteger('day_rate_divisor_type')->default(1)->after('sanctions_value_minute_delay')
                    ->comment('(1=26يوم),(2=30يوم),(3=أيام الشهر الفعلية),(4=مخصص)');
            }
            if (!Schema::hasColumn('admin_panel_settings', 'day_rate_divisor_custom')) {
                $table->decimal('day_rate_divisor_custom', 6, 2)->default(26)->after('day_rate_divisor_type')
                    ->comment('القيمة المخصصة لمقسوم اليوم — يُستخدم عند النوع 4');
            }

            // ── مقسوم سعر الساعة ──
            if (!Schema::hasColumn('admin_panel_settings', 'hour_rate_divisor_type')) {
                $table->tinyInteger('hour_rate_divisor_type')->default(1)->after('day_rate_divisor_custom')
                    ->comment('(1=8ساعات),(2=ساعات الشيفت),(3=مخصص)');
            }
            if (!Schema::hasColumn('admin_panel_settings', 'hour_rate_divisor_custom')) {
                $table->decimal('hour_rate_divisor_custom', 6, 2)->default(8)->after('hour_rate_divisor_type')
                    ->comment('القيمة المخصصة لمقسوم الساعة — يُستخدم عند النوع 3');
            }
        });
    }

    public function down(): void
    {
        Schema::table('admin_panel_settings', function (Blueprint $table) {
            $cols = ['day_rate_divisor_type', 'day_rate_divisor_custom', 'hour_rate_divisor_type', 'hour_rate_divisor_custom'];
            $table->dropColumn(array_filter($cols, fn($c) => Schema::hasColumn('admin_panel_settings', $c)));
        });
    }
};
