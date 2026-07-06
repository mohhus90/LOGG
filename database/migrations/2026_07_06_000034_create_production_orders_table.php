<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->integer('com_code');
            $table->string('order_number', 30);
            $table->unsignedBigInteger('bom_id');
            $table->unsignedBigInteger('item_id');
            $table->decimal('planned_quantity', 15, 4);
            $table->decimal('produced_quantity', 15, 4)->default(0);
            $table->unsignedBigInteger('source_warehouse_id');
            $table->unsignedBigInteger('target_warehouse_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->date('planned_start_date')->nullable();
            $table->date('planned_end_date')->nullable();
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->decimal('labor_cost', 15, 4)->default(0);
            $table->decimal('overhead_cost', 15, 4)->default(0);
            $table->decimal('material_cost', 15, 4)->default(0);
            $table->decimal('total_cost', 15, 4)->default(0);
            $table->enum('status', ['draft', 'in_progress', 'completed', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['com_code', 'order_number']);
            $table->foreign('bom_id')->references('id')->on('bill_of_materials');
        });

        Schema::create('production_order_materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_order_id');
            $table->unsignedBigInteger('item_id');
            $table->decimal('planned_quantity', 15, 4);
            $table->decimal('issued_quantity', 15, 4)->default(0);
            $table->decimal('unit_cost', 15, 4)->nullable();
            $table->decimal('total_cost', 15, 4)->nullable();
            $table->timestamps();

            $table->foreign('production_order_id')->references('id')->on('production_orders')->cascadeOnDelete();
        });

        Schema::create('production_receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_order_id');
            $table->decimal('quantity', 15, 4);
            $table->decimal('unit_cost', 15, 4)->default(0);
            $table->decimal('total_cost', 15, 4)->default(0);
            $table->date('date');
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('production_order_id')->references('id')->on('production_orders')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_receipts');
        Schema::dropIfExists('production_order_materials');
        Schema::dropIfExists('production_orders');
    }
};
