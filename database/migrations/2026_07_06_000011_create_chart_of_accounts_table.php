<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->integer('com_code');
            $table->string('account_code', 20);
            $table->string('account_name', 150);
            $table->string('account_name_en', 150)->nullable();
            $table->enum('account_type', ['asset', 'liability', 'equity', 'revenue', 'expense']);
            $table->enum('account_nature', ['debit', 'credit']);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedTinyInteger('level')->default(1);
            $table->boolean('is_group')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('allow_cost_center')->default(false);
            $table->decimal('opening_balance', 15, 4)->default(0);
            $table->date('opening_balance_date')->nullable();
            $table->decimal('current_balance', 15, 4)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['com_code', 'account_code']);
            $table->foreign('parent_id')->references('id')->on('chart_of_accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
