<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // بصمة ناقصة: 'in' = حضور بدون انصراف، 'out' = انصراف بدون حضور
            $table->enum('missing_punch', ['in', 'out'])->nullable()->after('check_out_time');

            // حل البصمة الناقصة:
            // 1=خصم ربع يوم، 2=خصم نصف يوم، 3=خصم يوم كامل، 4=نسيان (بدون خصم)، 5=إذن (خصم بالساعات)
            $table->tinyInteger('missing_punch_resolution')->nullable()->after('missing_punch');

            // عدد ساعات الإذن (للحالة 5)
            $table->decimal('missing_punch_hours', 5, 2)->nullable()->after('missing_punch_resolution');

            // شيفت مخصص لسجل الحضور — يُستخدم بدل شيفت الموظف الأساسي عند تغيير الشيفت مؤقتاً
            $table->unsignedBigInteger('shift_override_id')->nullable()->after('shift_id');
            $table->foreign('shift_override_id')->references('id')->on('shifts_types')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['shift_override_id']);
            $table->dropColumn(['missing_punch', 'missing_punch_resolution', 'missing_punch_hours', 'shift_override_id']);
        });
    }
};
