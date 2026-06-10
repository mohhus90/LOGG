<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── employees ───
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'weekly_off_day')) {
                $table->tinyInteger('weekly_off_day')->nullable()
                    ->comment('يوم الإجازة الأسبوعي: 0=الأحد،1=الاثنين،2=الثلاثاء،3=الأربعاء،4=الخميس،5=الجمعة،6=السبت،null=لا يوجد');
            }
        });

        // ─── attendances ───
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'early_departure_minutes')) {
                $table->integer('early_departure_minutes')->default(0)
                    ->comment('دقائق الانصراف المبكر (بعد فترة السماح)');
            }
            if (!Schema::hasColumn('attendances', 'early_departure_deduction')) {
                $table->decimal('early_departure_deduction', 10, 2)->default(0)
                    ->comment('قيمة خصم الانصراف المبكر بالمال');
            }
            if (!Schema::hasColumn('attendances', 'permission_minutes')) {
                $table->integer('permission_minutes')->default(0)
                    ->comment('دقائق إذن التأخير المعتمدة (تُخصم من التأخير)');
            }
            if (!Schema::hasColumn('attendances', 'permission_early_minutes')) {
                $table->integer('permission_early_minutes')->default(0)
                    ->comment('دقائق إذن الانصراف المبكر المعتمدة');
            }
            if (!Schema::hasColumn('attendances', 'late_fraction')) {
                $table->tinyInteger('late_fraction')->nullable()
                    ->comment('1=ربع يوم، 2=نصف يوم، 3=يوم كامل (وضع جزء اليوم)');
            }
            if (!Schema::hasColumn('attendances', 'weekly_off_overtime')) {
                $table->tinyInteger('weekly_off_overtime')->nullable()
                    ->comment('1=احتسب ساعات الإجازة الأسبوعية كأوفرتايم، 0=لا تحتسب');
            }
        });

        // ─── admin_panel_settings ───
        Schema::table('admin_panel_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('admin_panel_settings', 'max_permissions_per_day')) {
                $table->integer('max_permissions_per_day')->default(1)
                    ->comment('عدد الإذونات المسموح بها في اليوم الواحد');
            }
            if (!Schema::hasColumn('admin_panel_settings', 'max_permission_minutes_per_day')) {
                $table->integer('max_permission_minutes_per_day')->default(60)
                    ->comment('أقصى مدة للإذونات بالدقائق في اليوم');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'weekly_off_day')) {
                $table->dropColumn('weekly_off_day');
            }
        });

        Schema::table('attendances', function (Blueprint $table) {
            $cols = ['early_departure_minutes', 'early_departure_deduction',
                     'permission_minutes', 'permission_early_minutes',
                     'late_fraction', 'weekly_off_overtime'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('attendances', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('admin_panel_settings', function (Blueprint $table) {
            foreach (['max_permissions_per_day', 'max_permission_minutes_per_day'] as $col) {
                if (Schema::hasColumn('admin_panel_settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
