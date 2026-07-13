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
        Schema::create('employee_salary_history', function (Blueprint $table) {
            $table->id();
            $table->integer('com_code');
            $table->unsignedBigInteger('employee_id');
            $table->decimal('old_salary', 10, 2);
            $table->decimal('new_salary', 10, 2);
            $table->date('effective_date');
            $table->string('method', 20)->nullable(); // manual|fixed_amount|percentage
            $table->decimal('change_value', 10, 2)->nullable();
            $table->unsignedBigInteger('salary_increase_rule_id')->nullable();
            $table->text('reason')->nullable();
            $table->string('source', 20)->default('manual_edit'); // manual_edit|excel_import|bulk_increase
            $table->unsignedBigInteger('added_by')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('salary_increase_rule_id')->references('id')->on('salary_increase_rules')->onDelete('set null');
            $table->index(['com_code', 'employee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_salary_history');
    }
};
