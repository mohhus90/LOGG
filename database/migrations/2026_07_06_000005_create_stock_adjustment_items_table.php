<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_adjustment_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('adjustment_id');
            $table->unsignedBigInteger('item_id');
            $table->decimal('quantity', 15, 4);
            $table->decimal('unit_cost', 15, 4)->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->foreign('adjustment_id')->references('id')->on('stock_adjustments')->onDelete('cascade');
        });
    }

    public function down(): void { Schema::dropIfExists('stock_adjustment_items'); }
};
