<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_depreciation_entries', function (Blueprint $table) {
            $table->id();
            $table->integer('com_code');
            $table->unsignedBigInteger('fixed_asset_id');
            $table->unsignedSmallInteger('period_year');
            $table->unsignedTinyInteger('period_month');
            $table->decimal('depreciation_amount', 15, 4);
            $table->unsignedBigInteger('journal_entry_id')->nullable();
            $table->timestamp('run_at')->nullable();
            $table->unsignedBigInteger('run_by')->nullable();
            $table->timestamps();

            $table->unique(['fixed_asset_id', 'period_year', 'period_month'], 'asset_dep_period_unique');
            $table->foreign('fixed_asset_id')->references('id')->on('fixed_assets')->cascadeOnDelete();
            $table->foreign('journal_entry_id')->references('id')->on('journal_entries')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_depreciation_entries');
    }
};
