<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── إزالة حقول الأوفرتايم الثابت (النوع 2) ───
        Schema::table('admin_panel_settings', function (Blueprint $table) {
            $table->dropColumn(['overtime_calc_type', 'max_monthly_overtime_hours']);
        });
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('overtime_fixed_daily_amount');
        });

        // ─── إضافة حقول بدل الإجازة على الحضور ───
        Schema::table('attendances', function (Blueprint $table) {
            // 0=حضور عادي | 1=يوم راحة عمل فيه
            $table->tinyInteger('is_weekly_off_worked')->default(0)->after('weekly_off_overtime');
            $table->decimal('leave_compensation_amount', 10, 2)->default(0)->after('is_weekly_off_worked');
        });

        // ─── إعدادات بدل الإجازة (سجل واحد لكل شركة) ───
        Schema::create('leave_compensation_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('com_code');
            // 1=مضاعف اليوم | 2=مبلغ ثابت حسب المستوى
            $table->tinyInteger('comp_type')->default(1);
            // للنوع 1: مضاعف سعر اليوم (مثال: 1.5 = يوم ونصف)
            $table->decimal('day_multiplier', 5, 2)->default(1.5);
            // للنوع 2: المستوى المرجعي = job | branch | department
            $table->string('fixed_level', 20)->default('job');
            $table->timestamps();
            $table->unique('com_code');
        });

        // ─── معدلات بدل الإجازة لكل وظيفة/فرع/إدارة ───
        Schema::create('leave_compensation_rates', function (Blueprint $table) {
            $table->id();
            $table->integer('com_code');
            $table->string('level_type', 20); // job | branch | department
            $table->unsignedBigInteger('level_id');
            $table->decimal('amount', 10, 2)->default(0);
            $table->timestamps();
            $table->unique(['com_code', 'level_type', 'level_id']);
        });

        // ─── الجزاءات ───
        Schema::create('employee_sanctions', function (Blueprint $table) {
            $table->id();
            $table->integer('com_code');
            $table->unsignedBigInteger('employee_id');
            // 1=تحذير | 2=إنذار رسمي | 3=خصم مالي | 4=إيقاف عن العمل
            $table->tinyInteger('type');
            $table->decimal('amount', 10, 2)->default(0); // للخصم المالي
            $table->integer('suspension_days')->default(0); // للإيقاف
            $table->decimal('deduct_days', 8, 2)->default(0); // النوع 5: خصم باليوم
            $table->text('description')->nullable();
            $table->date('date');
            $table->unsignedBigInteger('attendance_id')->nullable(); // ربط بسجل حضور
            $table->string('deduct_month', 7)->nullable(); // YYYY-MM للاستقطاع من الراتب
            // 1=فعّال | 0=ملغى
            $table->tinyInteger('status')->default(1);
            $table->unsignedBigInteger('added_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_sanctions');
        Schema::dropIfExists('leave_compensation_rates');
        Schema::dropIfExists('leave_compensation_settings');

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['is_weekly_off_worked', 'leave_compensation_amount']);
        });

        Schema::table('admin_panel_settings', function (Blueprint $table) {
            $table->tinyInteger('overtime_calc_type')->default(1)->after('overtime_multiplier');
            $table->decimal('max_monthly_overtime_hours', 5, 2)->default(0)->after('overtime_calc_type');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('overtime_fixed_daily_amount', 10, 2)->nullable()->after('custom_overtime_multiplier');
        });
    }
};
