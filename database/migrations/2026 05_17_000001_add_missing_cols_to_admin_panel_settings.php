<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * إضافة الأعمدة المفقودة لجدول admin_panel_settings
 * يُشغَّل مرة واحدة بعد التثبيت
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_panel_settings', function (Blueprint $table) {
            // ── عمود image (اللوجو) ──
            if (!Schema::hasColumn('admin_panel_settings', 'image')) {
                $table->string('image', 300)->nullable()->after('logo')->comment('مسار اللوجو المرفوع');
            }

            // ── وضع احتساب التأخير ──
            if (!Schema::hasColumn('admin_panel_settings', 'delay_calc_mode')) {
                $table->tinyInteger('delay_calc_mode')->default(1)->after('sanctions_value_forth_abcence')
                    ->comment('(1=بالدقيقة),(2=نصف يوم بعد X مرة),(3=مدمج)');
            }

            // ── أيام الإجازات السنوية (القانون المصري) ──
            if (!Schema::hasColumn('admin_panel_settings', 'annual_vacation_days')) {
                $table->decimal('annual_vacation_days', 8, 1)->default(21)->after('delay_calc_mode')
                    ->comment('رصيد الإجازة الاعتيادية السنوية (قانون مصري: 21 يوم)');
            }

            if (!Schema::hasColumn('admin_panel_settings', 'casual_vacation_days')) {
                $table->decimal('casual_vacation_days', 8, 1)->default(6)->after('annual_vacation_days')
                    ->comment('رصيد الإجازة العارضة السنوية (قانون مصري: 6 أيام)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('admin_panel_settings', function (Blueprint $table) {
            $table->dropColumn([
                'delay_calc_mode',
                'annual_vacation_days',
                'casual_vacation_days',
            ]);

            if (Schema::hasColumn('admin_panel_settings', 'image')) {
                $table->dropColumn('image');
            }
        });
    }
};