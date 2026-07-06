<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gl_posting_rules', function (Blueprint $table) {
            $table->id();
            $table->integer('com_code');
            $table->string('event_type', 50);
            $table->string('line_role', 50);
            $table->unsignedBigInteger('account_id');
            $table->enum('side', ['debit', 'credit']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['com_code', 'event_type', 'line_role']);
            $table->foreign('account_id')->references('id')->on('chart_of_accounts');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gl_posting_rules');
    }
};
