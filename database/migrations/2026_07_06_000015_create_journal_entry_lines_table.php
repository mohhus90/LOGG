<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('journal_entry_id');
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('cost_center_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->decimal('debit', 15, 4)->default(0);
            $table->decimal('credit', 15, 4)->default(0);
            $table->string('description', 255)->nullable();
            $table->string('party_type', 20)->nullable();
            $table->unsignedBigInteger('party_id')->nullable();
            $table->timestamps();

            $table->index(['account_id']);
            $table->index(['party_type', 'party_id']);
            $table->foreign('journal_entry_id')->references('id')->on('journal_entries')->cascadeOnDelete();
            $table->foreign('account_id')->references('id')->on('chart_of_accounts');
            $table->foreign('cost_center_id')->references('id')->on('cost_centers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entry_lines');
    }
};
