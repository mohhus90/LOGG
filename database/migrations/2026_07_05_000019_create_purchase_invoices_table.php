<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchase_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('com_code');
            $table->string('invoice_number');
            $table->date('date');
            $table->date('due_date')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('supplier_invoice_no')->nullable(); // رقم فاتورة المورد نفسه
            $table->enum('invoice_type', ['cash', 'credit'])->default('credit'); // نقدي / آجل
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(14);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2)->default(0);
            // unpaid=غير مسدد, partial=مسدد جزئياً, paid=مسدد بالكامل
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            // draft=مسودة, received=مستلمة, cancelled=ملغاة
            $table->enum('status', ['draft', 'received', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('purchase_invoices'); }
};
