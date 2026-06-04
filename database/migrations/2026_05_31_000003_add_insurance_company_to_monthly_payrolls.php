<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_payrolls', function (Blueprint $table) {
            if (!Schema::hasColumn('monthly_payrolls', 'company_insurance_contribution')) {
                $table->decimal('company_insurance_contribution', 10, 2)->default(0)->after('insurance_deduction')
                    ->comment('حصة الشركة في التأمينات الاجتماعية');
            }
            if (!Schema::hasColumn('monthly_payrolls', 'total_insurance')) {
                $table->decimal('total_insurance', 10, 2)->default(0)->after('company_insurance_contribution')
                    ->comment('إجمالي التأمينات المدفوعة (الموظف + الشركة)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('monthly_payrolls', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('monthly_payrolls', 'company_insurance_contribution') ? 'company_insurance_contribution' : null,
                Schema::hasColumn('monthly_payrolls', 'total_insurance')                ? 'total_insurance'                : null,
            ]));
        });
    }
};
