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
        Schema::create('finance_cln_periods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('finance_calenders_id');
            $table->foreign('finance_calenders_id')->references('id')->on('finance_calenders')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('number_of_days');
            $table->string('year_of_month',10);
            $table->integer('finance_year');
            $table->integer('month_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->tinyInteger('is_open')->default(0);
            $table->date('start_date_finger_print');
            $table->date('end_date_finger_print');
            $table->integer('com_code');
            $table->integer('added_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_cln_periods');
    }
};
