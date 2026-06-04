<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_panel_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('admin_panel_settings', 'annual_vacation_days')) {
                $table->decimal('annual_vacation_days', 8, 1)->default(21)
                    ->after('after_days_begain_vacation')
                    ->comment('رصيد الإجازة الاعتيادية السنوية (قانون مصري: 21 يوم عادي، 30 فوق 50 سنة)');
            }
            if (!Schema::hasColumn('admin_panel_settings', 'casual_vacation_days')) {
                $table->decimal('casual_vacation_days', 8, 1)->default(6)
                    ->after('annual_vacation_days')
                    ->comment('رصيد الإجازة العارضة السنوية (قانون مصري: 6 أيام)');
            }
            if (!Schema::hasColumn('admin_panel_settings', 'image')) {
                $table->string('image', 300)->nullable()->after('com_name')
                    ->comment('مسار لوجو الشركة');
            }
        });

        // تحديث الرصيد الشهري للشركات الموجودة
        // 21 ÷ 12 = 1.75
        \DB::table('admin_panel_settings')
            ->whereRaw('monthly_vacation_balance = 0 OR monthly_vacation_balance IS NULL')
            ->update(['monthly_vacation_balance' => 1.75]);
    }

    public function down(): void
    {
        Schema::table('admin_panel_settings', function (Blueprint $table) {
            foreach (['annual_vacation_days', 'casual_vacation_days', 'image'] as $col) {
                if (Schema::hasColumn('admin_panel_settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
