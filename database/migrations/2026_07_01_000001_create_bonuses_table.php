<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * جدول المكافآت
     * bonus_type: 1=مبلغ ثابت، 2=أيام × مضاعف
     */
    public function up(): void
    {
        Schema::create('bonuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('bonus_date')->comment('تاريخ المكافأة');
            $table->tinyInteger('bonus_type')->default(1)->comment('(1=مبلغ ثابت),(2=أيام × مضاعف)');
            $table->decimal('amount', 10, 2)->nullable()->comment('المبلغ الثابت (للنوع 1)');
            $table->decimal('days', 8, 2)->nullable()->comment('عدد الأيام (للنوع 2)');
            $table->decimal('day_multiplier', 8, 4)->default(1)->comment('مضاعف اليوم (للنوع 2، افتراضي 1)');
            $table->integer('month')->comment('الشهر المرتبط (1-12)');
            $table->integer('year')->comment('السنة');
            $table->tinyInteger('status')->default(1)->comment('(1=معتمدة),(2=معلقة),(3=ملغاة)');
            $table->text('notes')->nullable();
            $table->integer('com_code');
            $table->integer('added_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bonuses');
    }
};
