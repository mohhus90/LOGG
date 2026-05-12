<?php
// =====================================================================
// هذا الملف يحتوي على 4 migrations منفصلة — يجب تقسيمها لملفات مستقلة
// =====================================================================

// ========= MIGRATION 1: جدول السلف ==========
// File: 2024_03_01_000003_create_advances_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('advance_date')->comment('تاريخ السلفة');
            $table->decimal('amount', 10, 2)->comment('قيمة السلفة');
            $table->integer('installments')->default(1)->comment('عدد الأقساط');
            $table->decimal('monthly_installment', 10, 2)->comment('القسط الشهري');
            $table->decimal('remaining_amount', 10, 2)->comment('المبلغ المتبقي');
            $table->tinyInteger('status')->default(1)
                  ->comment('(1=جارية),(2=مسددة),(3=ملغاة)');
            $table->text('notes')->nullable();
            $table->integer('com_code');
            $table->integer('added_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advances');
    }
};
