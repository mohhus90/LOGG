<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eta_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eta_invoice_id')->constrained()->cascadeOnDelete();
            $table->string('item_code')->nullable();
            $table->string('description')->nullable();
            $table->string('unit_type')->nullable();
            $table->decimal('quantity', 15, 4)->default(1);
            $table->decimal('unit_price', 15, 4)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('net_total', 15, 2)->default(0);
            $table->decimal('vat_rate', 8, 2)->default(14);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('total_with_vat', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eta_invoice_items');
    }
};
