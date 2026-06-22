<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'overtime_fixed_daily_amount')) {
                $table->decimal('overtime_fixed_daily_amount', 10, 2)->nullable()->after('custom_overtime_multiplier')
                    ->comment('مبلغ الأوفرتايم اليومي الثابت لكل موظف (override عند overtime_calc_type=2)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'overtime_fixed_daily_amount')) {
                $table->dropColumn('overtime_fixed_daily_amount');
            }
        });
    }
};
