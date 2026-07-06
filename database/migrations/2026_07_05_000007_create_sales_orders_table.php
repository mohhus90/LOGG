<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('com_code');
            $table->string('order_number');
            $table->date('date');
            $table->date('delivery_date')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('quotation_id')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(14);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            // draft=مسودة, confirmed=مؤكد, processing=جاري التنفيذ, partial=تسليم جزئي, delivered=مسلم, cancelled=ملغي
            $table->enum('status', ['draft','confirmed','processing','partial','delivered','cancelled'])->default('draft');
            $table->text('delivery_address')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('sales_orders'); }
};
