<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treasury_vouchers', function (Blueprint $table) {
            $table->id();
            $table->integer('com_code');
            $table->string('voucher_number', 30);
            $table->enum('voucher_type', ['receipt', 'payment']);
            $table->date('date');
            $table->enum('payment_method', ['cash', 'bank', 'cheque']);
            $table->unsignedBigInteger('cash_box_id')->nullable();
            $table->unsignedBigInteger('bank_account_id')->nullable();
            $table->enum('party_type', ['customer', 'supplier', 'employee', 'other']);
            $table->unsignedBigInteger('party_id')->nullable();
            $table->decimal('amount', 15, 4);
            $table->unsignedBigInteger('gl_account_id')->nullable();
            $table->string('linked_type', 50)->nullable();
            $table->unsignedBigInteger('linked_id')->nullable();
            // بدون FK صريح على cheques لتفادي الاعتماد الدائري بين الجدولين، يُقرأ عبر العلاقة فقط
            $table->unsignedBigInteger('cheque_id')->nullable();
            $table->string('reference_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'posted', 'cancelled'])->default('posted');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('posted_by')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->unique(['com_code', 'voucher_number']);
            $table->index(['com_code', 'party_type', 'party_id']);
            $table->index(['linked_type', 'linked_id']);
            $table->foreign('cash_box_id')->references('id')->on('cash_boxes')->nullOnDelete();
            $table->foreign('bank_account_id')->references('id')->on('bank_accounts')->nullOnDelete();
            $table->foreign('gl_account_id')->references('id')->on('chart_of_accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treasury_vouchers');
    }
};
