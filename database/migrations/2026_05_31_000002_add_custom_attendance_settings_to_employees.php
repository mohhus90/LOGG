<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'custom_overtime_multiplier')) {
                $table->decimal('custom_overtime_multiplier', 5, 2)->nullable()->after('emp_sal_insurance')
                    ->comment('مضاعف أوفرتايم مخصص (null = استخدام إعداد الشركة)');
            }
            if (!Schema::hasColumn('employees', 'overtime_enabled')) {
                $table->tinyInteger('overtime_enabled')->default(1)->after('custom_overtime_multiplier')
                    ->comment('(1=يُحتسب الأوفرتايم),(0=لا يُحتسب)');
            }
            if (!Schema::hasColumn('employees', 'late_deduction_enabled')) {
                $table->tinyInteger('late_deduction_enabled')->default(1)->after('overtime_enabled')
                    ->comment('(1=يُحتسب خصم التأخير),(0=معفى من خصم التأخير)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('employees', 'custom_overtime_multiplier') ? 'custom_overtime_multiplier' : null,
                Schema::hasColumn('employees', 'overtime_enabled')           ? 'overtime_enabled'           : null,
                Schema::hasColumn('employees', 'late_deduction_enabled')     ? 'late_deduction_enabled'     : null,
            ]));
        });
    }
};
