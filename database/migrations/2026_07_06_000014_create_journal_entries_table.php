<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->integer('com_code');
            $table->string('entry_number', 30);
            $table->date('entry_date');
            $table->enum('entry_type', ['manual', 'auto'])->default('manual');
            $table->string('source_module', 50)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('reference', 100)->nullable();
            $table->text('description')->nullable();
            $table->decimal('total_debit', 15, 4)->default(0);
            $table->decimal('total_credit', 15, 4)->default(0);
            $table->enum('status', ['draft', 'posted', 'reversed'])->default('draft');
            $table->unsignedBigInteger('reversed_entry_id')->nullable();
            $table->unsignedBigInteger('period_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('posted_by')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->unique(['com_code', 'entry_number']);
            $table->index(['com_code', 'source_module', 'source_id']);
            $table->index(['com_code', 'entry_date']);
            $table->foreign('reversed_entry_id')->references('id')->on('journal_entries')->nullOnDelete();
            $table->foreign('period_id')->references('id')->on('accounting_periods')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
