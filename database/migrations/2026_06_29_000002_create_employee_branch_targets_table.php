<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // التارجت الفردي لكل موظف داخل خطة الفرع
        Schema::create('employee_branch_targets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plan_id');
            $table->unsignedBigInteger('employee_id');
            $table->tinyInteger('month');
            $table->smallInteger('year');
            $table->decimal('target_amount', 14, 2)->default(0);
            $table->integer('com_code');
            $table->unsignedBigInteger('added_by')->nullable();
            $table->timestamps();
            $table->unique(['plan_id', 'employee_id', 'month', 'year', 'com_code'], 'emp_target_unique');
        });

        // أحداث منتصف الشهر (مغادرة / بديل / توزيع)
        Schema::create('employee_target_events', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('month');
            $table->smallInteger('year');
            $table->unsignedBigInteger('branch_id');

            // الموظف الذي غادر أو غاب
            $table->unsignedBigInteger('employee_id');

            // آخر يوم حضر فيه (مثال: شادي آخر يوم له = 19)
            $table->tinyInteger('last_day_present');

            // البديل في هذا الفرع (إن وجد) وحصته من التارجت تعادل الأيام المتبقية
            $table->unsignedBigInteger('replacement_employee_id')->nullable();

            // هل يتم توزيع أيام الغياب على باقي الزملاء بنسب تارجتهم؟
            // (يستخدم عندما لا يوجد بديل)
            $table->boolean('redistribute_target')->default(false);

            $table->text('notes')->nullable();
            $table->integer('com_code');
            $table->unsignedBigInteger('added_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_target_events');
        Schema::dropIfExists('employee_branch_targets');
    }
};
