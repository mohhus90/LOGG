<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * جدول مسير الرواتب الشهري
     * يتم احتساب الراتب الصافي من:
     * - الراتب الأساسي (محتسب على أيام الحضور الفعلية من بداية الفترة لنهايتها)
     * - + الإضافات الثابتة
     * - + الأوفرتايم
     * - + العمولات
     * - - خصم التأخيرات
     * - - الغيابات
     * - - الخصومات
     * - - قسط السلفة الشهري
     * - - التأمينات
     */
    public function up(): void
    {
        Schema::create('monthly_payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->integer('month')->comment('الشهر (1-12)');
            $table->integer('year')->comment('السنة');
            $table->date('period_from')->comment('بداية فترة الاحتساب');
            $table->date('period_to')->comment('نهاية فترة الاحتساب');

            // أيام العمل
            $table->integer('total_days')->default(0)->comment('إجمالي أيام الفترة');
            $table->integer('work_days')->default(0)->comment('أيام العمل الفعلية');
            $table->integer('absence_days')->default(0)->comment('أيام الغياب');
            $table->integer('leave_days')->default(0)->comment('أيام الإجازة');

            // الراتب الأساسي والمستحقات
            $table->decimal('basic_salary', 10, 2)->default(0)->comment('الراتب الأساسي كامل الشهر');
            $table->decimal('daily_rate', 10, 2)->default(0)->comment('قيمة اليوم الواحد');
            $table->decimal('earned_salary', 10, 2)->default(0)->comment('الراتب المستحق بعد الحضور');
            $table->decimal('fixed_allowances', 10, 2)->default(0)->comment('الإضافات الثابتة');
            $table->decimal('overtime_amount', 10, 2)->default(0)->comment('إجمالي الأوفرتايم');
            $table->decimal('commissions_amount', 10, 2)->default(0)->comment('إجمالي العمولات');

            // الخصومات
            $table->decimal('late_deductions', 10, 2)->default(0)->comment('خصومات التأخير');
            $table->decimal('absence_deductions', 10, 2)->default(0)->comment('خصومات الغياب');
            $table->decimal('deductions_amount', 10, 2)->default(0)->comment('إجمالي الخصومات الأخرى');
            $table->decimal('advance_installment', 10, 2)->default(0)->comment('قسط السلفة');
            $table->decimal('insurance_deduction', 10, 2)->default(0)->comment('خصم التأمينات');

            // الصافي
            $table->decimal('gross_salary', 10, 2)->default(0)->comment('الراتب الإجمالي قبل الخصومات');
            $table->decimal('net_salary', 10, 2)->default(0)->comment('الراتب الصافي');

            $table->tinyInteger('status')->default(1)
                  ->comment('(1=مسودة),(2=معتمد),(3=مدفوع)');
            $table->text('notes')->nullable();
            $table->integer('com_code');
            $table->integer('added_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_payrolls');
    }
};
