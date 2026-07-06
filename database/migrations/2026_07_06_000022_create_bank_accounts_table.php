<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->integer('com_code');
            $table->string('bank_name', 150);
            $table->string('account_name', 150);
            $table->string('account_number', 50)->nullable();
            $table->string('iban', 50)->nullable();
            $table->string('swift_code', 30)->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('gl_account_id')->nullable();
            $table->decimal('opening_balance', 15, 4)->default(0);
            $table->decimal('current_balance', 15, 4)->default(0);
            $table->string('currency_code', 10)->default('EGP');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('gl_account_id')->references('id')->on('chart_of_accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
