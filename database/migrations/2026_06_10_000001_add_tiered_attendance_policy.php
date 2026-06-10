<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── admin_panel_settings: وضع التأخير الهرمي + أوفرتايم ثابت ───
        Schema::table('admin_panel_settings', function (Blueprint $table) {
            // Mode 3: حد المرحلة الأولى (0 .. X دقيقة: دقيقة × مضاعف)
            $table->integer('delay_tier1_minutes')->default(0)->after('after_minute_quarterday');
            // حد نصف اليوم — modes 2 و 3
            $table->integer('delay_halfday_minutes')->default(0)->after('delay_tier1_minutes');
            // حد اليوم الكامل — modes 2 و 3
            $table->integer('delay_fullday_minutes')->default(0)->after('delay_halfday_minutes');

            // حدود الانصراف المبكر (مستقلة عن وضع التأخير)
            $table->integer('early_departure_halfday_minutes')->default(0)->after('delay_fullday_minutes');
            $table->integer('early_departure_fullday_minutes')->default(0)->after('early_departure_halfday_minutes');
            // عدم إتمام اليوم = يوم + نصف
            $table->integer('early_departure_fullplushalf_minutes')->default(0)->after('early_departure_fullday_minutes');

            // نوع احتساب الأوفرتايم: 1=مضاعف (كالحالي) | 2=مبلغ ثابت يومي لكل موظف
            $table->tinyInteger('overtime_calc_type')->default(1)->after('overtime_multiplier');
            // حد أقصى للأوفرتايم الشهري (0=لا حد)
            $table->decimal('max_monthly_overtime_hours', 5, 2)->default(0)->after('overtime_calc_type');
        });

        // ─── attendances: كسر الانصراف المبكر ───
        Schema::table('attendances', function (Blueprint $table) {
            // null=بالدقيقة | 1=ربع | 2=نصف | 3=يوم | 4=يوم+نصف
            $table->tinyInteger('early_departure_fraction')->nullable()->after('early_departure_deduction');
        });

        // ─── employees: مبلغ الأوفرتايم اليومي الثابت (override للوضع 2) ───
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('overtime_fixed_daily_amount', 10, 2)->nullable()->after('custom_overtime_multiplier');
        });
    }

    public function down(): void
    {
        Schema::table('admin_panel_settings', function (Blueprint $table) {
            $table->dropColumn([
                'delay_tier1_minutes',
                'delay_halfday_minutes',
                'delay_fullday_minutes',
                'early_departure_halfday_minutes',
                'early_departure_fullday_minutes',
                'early_departure_fullplushalf_minutes',
                'overtime_calc_type',
                'max_monthly_overtime_hours',
            ]);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('early_departure_fraction');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('overtime_fixed_daily_amount');
        });
    }
};
