<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_panel_settings', function (Blueprint $table) {
            $table->decimal('income_tax_exemption_monthly', 12, 2)->default(0)
                ->comment('الإعفاء الضريبي الشهري قبل تطبيق الشرائح');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->boolean('apply_income_tax')->default(false)
                ->comment('هل يُخصم من هذا الموظف ضريبة كسب عمل؟ (اختياري لكل موظف)');
        });

        Schema::table('monthly_payrolls', function (Blueprint $table) {
            $table->decimal('income_tax_deduction', 12, 2)->default(0)->after('insurance_deduction');
        });
    }

    public function down(): void
    {
        Schema::table('admin_panel_settings', function (Blueprint $table) {
            $table->dropColumn('income_tax_exemption_monthly');
        });
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('apply_income_tax');
        });
        Schema::table('monthly_payrolls', function (Blueprint $table) {
            $table->dropColumn('income_tax_deduction');
        });
    }
};
