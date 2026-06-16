<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('employee_sanctions') && !Schema::hasColumn('employee_sanctions', 'deduct_days')) {
            Schema::table('employee_sanctions', function (Blueprint $table) {
                $table->decimal('deduct_days', 8, 2)->default(0)->after('suspension_days');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('employee_sanctions') && Schema::hasColumn('employee_sanctions', 'deduct_days')) {
            Schema::table('employee_sanctions', function (Blueprint $table) {
                $table->dropColumn('deduct_days');
            });
        }
    }
};
