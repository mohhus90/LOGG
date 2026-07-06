<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('com_code');
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('item_id');
            // purchase_in, purchase_return_out, sales_out, sales_return_in, adjustment_in, adjustment_out, transfer_in, transfer_out
            $table->string('movement_type');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('quantity', 15, 4);
            $table->decimal('unit_cost', 15, 4)->nullable();
            $table->decimal('balance_after', 15, 4)->default(0);
            $table->date('date');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['warehouse_id', 'item_id']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void { Schema::dropIfExists('stock_movements'); }
};
