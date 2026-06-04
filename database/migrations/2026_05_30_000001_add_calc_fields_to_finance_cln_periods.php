<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_cln_periods', function (Blueprint $table) {
            if (!Schema::hasColumn('finance_cln_periods', 'working_days')) {
                $table->unsignedTinyInteger('working_days')->nullable()->comment('أيام العمل الفعلية في الشهر');
            }
            if (!Schema::hasColumn('finance_cln_periods', 'vacation_days_accrual')) {
                $table->decimal('vacation_days_accrual', 5, 2)->nullable()->comment('استحقاق الإجازة بالأيام لهذا الشهر تحديداً — null يعني استخدام الإعداد العام');
            }
        });
    }

    public function down(): void
    {
        Schema::table('finance_cln_periods', function (Blueprint $table) {
            $table->dropColumnIfExists('working_days');
            $table->dropColumnIfExists('vacation_days_accrual');
        });
    }
};
