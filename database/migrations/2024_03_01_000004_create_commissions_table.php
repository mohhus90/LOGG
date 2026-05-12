<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * جدول العمولات
     */
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('commission_date')->comment('تاريخ العمولة');
            $table->string('commission_type', 100)->nullable()->comment('نوع العمولة');
            $table->decimal('amount', 10, 2)->comment('قيمة العمولة');
            $table->integer('month')->comment('الشهر المرتبط به (1-12)');
            $table->integer('year')->comment('السنة');
            $table->tinyInteger('status')->default(1)
                  ->comment('(1=معتمدة),(2=معلقة),(3=ملغاة)');
            $table->text('notes')->nullable();
            $table->integer('com_code');
            $table->integer('added_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
