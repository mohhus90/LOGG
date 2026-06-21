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
        Schema::table('attendances', function (Blueprint $table) {
            $table->tinyInteger('is_before_hire')->default(0)->after('com_code')
                  ->comment('1 = التاريخ قبل تاريخ تعيين الموظف — لا يُحتسب في الراتب');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('is_before_hire');
        });
    }
};
