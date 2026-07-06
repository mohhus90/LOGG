<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->integer('com_code');
            $table->string('asset_number', 30);
            $table->unsignedBigInteger('category_id');
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('location', 150)->nullable();
            $table->date('purchase_date');
            $table->decimal('purchase_cost', 15, 4);
            $table->unsignedTinyInteger('useful_life_years');
            $table->decimal('salvage_value', 15, 4)->default(0);
            $table->string('depreciation_method', 20)->default('straight_line');
            $table->decimal('accumulated_depreciation', 15, 4)->default(0);
            $table->decimal('book_value', 15, 4)->default(0);
            $table->enum('status', ['active', 'disposed', 'transferred', 'fully_depreciated'])->default('active');
            $table->date('disposal_date')->nullable();
            $table->decimal('disposal_amount', 15, 4)->nullable();
            $table->text('disposal_notes')->nullable();
            $table->unsignedBigInteger('source_purchase_invoice_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['com_code', 'asset_number']);
            $table->foreign('category_id')->references('id')->on('asset_categories');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixed_assets');
    }
};
