<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * جدول الحضور والانصراف اليومي
     * يتم مقارنة وقت الحضور/الانصراف مع الشيفت لاحتساب:
     * - دقائق التأخير في الحضور
     * - ساعات الأوفرتايم (الإضافي)
     * - الغياب
     */
    public function up(): void
    {
        if (!Schema::hasTable('attendances')) {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('shift_id')->constrained('shifts_types')->onDelete('cascade');
            $table->date('attendance_date')->comment('تاريخ اليوم');
            $table->time('check_in_time')->nullable()->comment('وقت الحضور الفعلي');
            $table->time('check_out_time')->nullable()->comment('وقت الانصراف الفعلي');
            $table->integer('late_minutes')->default(0)->comment('دقائق التأخير');
            $table->decimal('overtime_hours', 5, 2)->default(0)->comment('ساعات الأوفرتايم');
            $table->decimal('overtime_amount', 10, 2)->default(0)->comment('قيمة الأوفرتايم بالمال');
            $table->decimal('late_deduction', 10, 2)->default(0)->comment('خصم التأخير بالمال');
            $table->tinyInteger('status')->default(1)
                  ->comment('(1=حضر),(2=غياب),(3=إجازة),(4=إجازة رسمية),(5=مأمورية)');
            $table->text('notes')->nullable()->comment('ملاحظات');
            $table->integer('com_code');
            $table->integer('added_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'attendance_date']);
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};