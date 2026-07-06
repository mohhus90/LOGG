<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advance_deduction_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advance_id')->constrained('advances')->onDelete('cascade');
            $table->foreignId('monthly_payroll_id')->constrained('monthly_payrolls')->onDelete('cascade');
            $table->decimal('amount', 10, 2)->comment('المبلغ المخصوم من السلفة عند اعتماد هذا الكشف');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advance_deduction_logs');
    }
};
