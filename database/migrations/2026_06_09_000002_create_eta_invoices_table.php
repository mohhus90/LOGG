<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eta_invoices', function (Blueprint $table) {
            $table->id();
            $table->integer('com_code')->index();
            $table->string('direction')->comment('Sent=مبيعات, Received=مشتريات');

            // بيانات من ETA
            $table->string('uuid')->unique();
            $table->string('long_id')->nullable();
            $table->string('internal_id')->nullable()->comment('الرقم الداخلي');
            $table->string('document_type')->default('I')->comment('I=فاتورة, C=إشعار دائن, D=إشعار مدين');
            $table->string('document_type_version')->nullable();

            // بيانات الطرفين
            $table->string('issuer_id')->nullable()->comment('رقم ضريبي المُصدر');
            $table->string('issuer_name')->nullable();
            $table->string('receiver_id')->nullable()->comment('رقم ضريبي المستلم');
            $table->string('receiver_name')->nullable();

            // التواريخ
            $table->timestamp('date_issued')->nullable();
            $table->timestamp('date_received')->nullable();

            // القيم المالية
            $table->decimal('total_sales', 15, 2)->default(0);
            $table->decimal('total_discount', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2)->default(0);
            $table->decimal('total_vat', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);

            // الحالة
            $table->string('status')->default('Valid');
            $table->string('activity_code')->nullable();

            // المعالجة المحاسبية
            $table->boolean('is_posted')->default(false)->comment('تم الترحيل محاسبياً');
            $table->timestamp('posted_at')->nullable();
            $table->integer('posted_by')->nullable();
            $table->text('posting_notes')->nullable();

            // البيانات الخام
            $table->json('raw_data')->nullable();

            $table->timestamps();

            $table->index(['com_code', 'direction']);
            $table->index(['com_code', 'date_issued']);
            $table->index(['com_code', 'is_posted']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eta_invoices');
    }
};
