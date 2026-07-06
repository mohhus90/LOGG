<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'missing_punch_deduction')) {
                $table->decimal('missing_punch_deduction', 10, 2)->default(0)->after('late_deduction')
                      ->comment('خصم حل البصمة الناقصة (منفصل عن خصم التأخير)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'missing_punch_deduction')) {
                $table->dropColumn('missing_punch_deduction');
            }
        });
    }
};
