<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * يربط سجل الفاتورة الإلكترونية (المسحوبة من ETA) بالفاتورة الداخلية المقابلة
 * (فاتورة بيع لو direction=Sent، أو فاتورة شراء لو direction=Received)، حتى
 * يمكن التحقق أن الفاتورة لها قيد محاسبي فعلي قبل تأكيد "المطابقة" (Phase 7).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eta_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('sales_invoice_id')->nullable()->after('internal_id');
            $table->unsignedBigInteger('purchase_invoice_id')->nullable()->after('sales_invoice_id');
        });
    }

    public function down(): void
    {
        Schema::table('eta_invoices', function (Blueprint $table) {
            $table->dropColumn(['sales_invoice_id', 'purchase_invoice_id']);
        });
    }
};
