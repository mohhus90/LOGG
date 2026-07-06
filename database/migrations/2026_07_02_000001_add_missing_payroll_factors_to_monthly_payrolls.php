<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_payrolls', function (Blueprint $table) {
            if (!Schema::hasColumn('monthly_payrolls', 'weekly_off_days')) {
                $table->integer('weekly_off_days')->default(0)->after('leave_days')
                      ->comment('أيام الإجازة الأسبوعية المدفوعة');
            }
            if (!Schema::hasColumn('monthly_payrolls', 'leave_compensation_amount')) {
                $table->decimal('leave_compensation_amount', 10, 2)->default(0)->after('bonuses_amount')
                      ->comment('بدل العمل في الإجازة الأسبوعية');
            }
            if (!Schema::hasColumn('monthly_payrolls', 'kpi_bonus_amount')) {
                $table->decimal('kpi_bonus_amount', 10, 2)->default(0)->after('leave_compensation_amount')
                      ->comment('مكافأة مؤشرات الأداء KPI');
            }
            if (!Schema::hasColumn('monthly_payrolls', 'kpi_deduction_amount')) {
                $table->decimal('kpi_deduction_amount', 10, 2)->default(0)->after('insurance_deduction')
                      ->comment('خصم مؤشرات الأداء KPI');
            }
            if (!Schema::hasColumn('monthly_payrolls', 'sanctions_deduction')) {
                $table->decimal('sanctions_deduction', 10, 2)->default(0)->after('kpi_deduction_amount')
                      ->comment('خصم الجزاءات (مالي / باليوم / إيقاف عن العمل)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('monthly_payrolls', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('monthly_payrolls', 'weekly_off_days')            ? 'weekly_off_days'            : null,
                Schema::hasColumn('monthly_payrolls', 'leave_compensation_amount')  ? 'leave_compensation_amount'  : null,
                Schema::hasColumn('monthly_payrolls', 'kpi_bonus_amount')           ? 'kpi_bonus_amount'           : null,
                Schema::hasColumn('monthly_payrolls', 'kpi_deduction_amount')       ? 'kpi_deduction_amount'       : null,
                Schema::hasColumn('monthly_payrolls', 'sanctions_deduction')        ? 'sanctions_deduction'        : null,
            ]));
        });
    }
};
