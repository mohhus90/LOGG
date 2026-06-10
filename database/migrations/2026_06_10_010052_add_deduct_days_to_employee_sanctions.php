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
        Schema::table('employee_sanctions', function (Blueprint $table) {
            // النوع 5: خصم باليوم — يُخزَّن عدد الأيام كرقم عشري (مثل 0.5 = نصف يوم)
            $table->decimal('deduct_days', 8, 2)->default(0)->after('suspension_days');
        });
    }

    public function down(): void
    {
        Schema::table('employee_sanctions', function (Blueprint $table) {
            $table->dropColumn('deduct_days');
        });
    }
};
