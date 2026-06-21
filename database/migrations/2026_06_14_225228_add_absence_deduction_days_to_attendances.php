<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'absence_deduction_days')) {
                $table->decimal('absence_deduction_days', 5, 2)
                      ->nullable()
                      ->after('status')
                      ->comment('عدد أيام الخصم عند الغياب — null يعني استخدام الضبط العام');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'absence_deduction_days')) {
                $table->dropColumn('absence_deduction_days');
            }
        });
    }
};
