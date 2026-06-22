<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_panel_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('admin_panel_settings', 'overtime_calc_type')) {
                $table->tinyInteger('overtime_calc_type')->default(1)->after('hour_rate_divisor_custom')
                    ->comment('(1=ساعات ثابتة),(2=ما يزيد عن جدول الشيفت)');
            }
            if (!Schema::hasColumn('admin_panel_settings', 'max_monthly_overtime_hours')) {
                $table->decimal('max_monthly_overtime_hours', 6, 2)->default(0)->after('overtime_calc_type')
                    ->comment('الحد الأقصى لساعات الأوفرتايم الشهرية (0 = بلا حد)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('admin_panel_settings', function (Blueprint $table) {
            $cols = ['overtime_calc_type', 'max_monthly_overtime_hours'];
            $table->dropColumn(array_filter($cols, fn($c) => Schema::hasColumn('admin_panel_settings', $c)));
        });
    }
};
