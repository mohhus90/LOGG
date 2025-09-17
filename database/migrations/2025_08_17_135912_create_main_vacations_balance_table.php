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
        Schema::create('main_vacations_balance', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id');
            $table->string('year_and_month')->comment('السنة والشهر')->nullable()->default('0');
            $table->integer('finance_yr')->comment('السنةالمالية')->nullable()->default('0');
            $table->decimal('carryover_from_previous_month',10,2)->comment('الرصيد المرحل من الشهر السابق')->nullable()->default('0');
            $table->decimal('currentmonth_balance',10,2)->comment('رصيد  الشهر الحالى')->nullable()->default('0');
            $table->decimal('total_available_balance',10,2)->comment('اجمالى الرصيد المتاح')->nullable()->default('0');
            $table->decimal('spent_balance',10,2)->comment('الرصيد المستهلك')->nullable()->default('0');
            $table->decimal('net_balance',10,2)->comment('صافى الرصيد')->nullable()->default('0');
            $table->integer('com_code');
            $table->integer('added_by')->refrences('id')->on('admins')->onupdate('cascade');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('main_vacations_balance');
    }
};
