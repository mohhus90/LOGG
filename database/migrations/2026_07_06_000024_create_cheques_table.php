<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cheques', function (Blueprint $table) {
            $table->id();
            $table->integer('com_code');
            $table->enum('direction', ['received', 'issued']);
            $table->string('cheque_number', 50);
            $table->string('bank_name', 150)->nullable();
            $table->date('cheque_date');
            $table->date('due_date');
            $table->decimal('amount', 15, 4);
            $table->enum('party_type', ['customer', 'supplier']);
            $table->unsignedBigInteger('party_id');
            $table->unsignedBigInteger('bank_account_id')->nullable();
            $table->unsignedBigInteger('treasury_voucher_id');
            $table->enum('status', ['under_collection', 'collected', 'bounced', 'cancelled'])->default('under_collection');
            $table->date('collected_at')->nullable();
            $table->date('bounced_at')->nullable();
            $table->text('bounce_reason')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['com_code', 'direction', 'status']);
            $table->foreign('bank_account_id')->references('id')->on('bank_accounts')->nullOnDelete();
            $table->foreign('treasury_voucher_id')->references('id')->on('treasury_vouchers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cheques');
    }
};
