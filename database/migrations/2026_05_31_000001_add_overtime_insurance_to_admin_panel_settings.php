<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_panel_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('admin_panel_settings', 'overtime_multiplier')) {
                $table->decimal('overtime_multiplier', 5, 2)->default(1.50)->after('delay_calc_mode')
                    ->comment('مضاعف الأوفرتايم (1.5 = مرة ونصف، 2 = مرتين)');
            }
            if (!Schema::hasColumn('admin_panel_settings', 'employee_insurance_rate')) {
                $table->decimal('employee_insurance_rate', 5, 2)->default(11.00)->after('overtime_multiplier')
                    ->comment('نسبة اشتراك الموظف في التأمينات الاجتماعية %');
            }
            if (!Schema::hasColumn('admin_panel_settings', 'company_insurance_rate')) {
                $table->decimal('company_insurance_rate', 5, 2)->default(18.75)->after('employee_insurance_rate')
                    ->comment('نسبة اشتراك الشركة في التأمينات الاجتماعية %');
            }
        });
    }

    public function down(): void
    {
        Schema::table('admin_panel_settings', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('admin_panel_settings', 'overtime_multiplier')      ? 'overtime_multiplier'      : null,
                Schema::hasColumn('admin_panel_settings', 'employee_insurance_rate')  ? 'employee_insurance_rate'  : null,
                Schema::hasColumn('admin_panel_settings', 'company_insurance_rate')   ? 'company_insurance_rate'   : null,
            ]));
        });
    }
};
