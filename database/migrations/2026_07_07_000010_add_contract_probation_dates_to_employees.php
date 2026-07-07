<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->date('probation_end_date')->nullable()->comment('نهاية فترة الاختبار');
            $table->date('contract_end_date')->nullable()->comment('نهاية العقد محدد المدة (فارغ = غير محدد المدة)');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['probation_end_date', 'contract_end_date']);
        });
    }
};
