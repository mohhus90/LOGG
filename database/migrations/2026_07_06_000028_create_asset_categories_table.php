<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->integer('com_code');
            $table->string('name', 150);
            $table->unsignedTinyInteger('default_useful_life_years')->default(5);
            $table->string('default_depreciation_method', 20)->default('straight_line');
            $table->unsignedBigInteger('asset_gl_account_id')->nullable();
            $table->unsignedBigInteger('accum_depreciation_gl_account_id')->nullable();
            $table->unsignedBigInteger('depreciation_expense_gl_account_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('asset_gl_account_id')->references('id')->on('chart_of_accounts')->nullOnDelete();
            $table->foreign('accum_depreciation_gl_account_id')->references('id')->on('chart_of_accounts')->nullOnDelete();
            $table->foreign('depreciation_expense_gl_account_id')->references('id')->on('chart_of_accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_categories');
    }
};
