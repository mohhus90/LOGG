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
        Schema::create('salary_increase_rules', function (Blueprint $table) {
            $table->id();
            $table->integer('com_code');
            $table->string('scope_type', 20); // global|department|branch|job|client|employee
            $table->unsignedBigInteger('scope_id')->nullable(); // null when scope_type = global
            $table->string('method', 20); // fixed_amount|percentage
            $table->decimal('value', 10, 2);
            $table->date('effective_date');
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(1); // 1 = active, 0 = reverted/cancelled
            $table->unsignedBigInteger('added_by')->nullable();
            $table->timestamps();

            $table->unique(['com_code', 'scope_type', 'scope_id', 'effective_date'], 'salary_rule_scope_date_unique');
            $table->index(['com_code', 'scope_type', 'scope_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_increase_rules');
    }
};
