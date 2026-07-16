<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_factors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained('clients')->onUpdate('cascade')->onDelete('set null');
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');

            // ── مؤثرات مُدخلة شهريًا (تقابل أعمدة شيت "Sheet1" الخاص بكل عميل) ──
            $table->decimal('working_days', 6, 2)->default(0)->comment('أيام العمل الفعلية');
            $table->decimal('overtime_hours', 6, 2)->default(0);
            $table->decimal('absence_hours', 6, 2)->default(0)->comment('يُخصم بقسمة الراتب الثابت/240');
            $table->decimal('leave_days', 6, 2)->default(0)->comment('يُخصم بقسمة الراتب الثابت/30');
            $table->decimal('no_show_days', 6, 2)->default(0);
            $table->decimal('unpaid_leave_days', 6, 2)->default(0);
            $table->decimal('sick_leave_days', 6, 2)->default(0);
            $table->decimal('sick_leave_balance', 6, 2)->default(0);
            $table->decimal('penalty_days', 6, 2)->default(0);
            $table->decimal('settlement_hours', 6, 2)->default(0);
            $table->decimal('settlement_days', 6, 2)->default(0);
            $table->decimal('settlement_amount', 10, 2)->default(0);
            $table->decimal('bonus_amount', 10, 2)->default(0);
            $table->decimal('other_allowance', 10, 2)->default(0);
            $table->decimal('other_deduction', 10, 2)->default(0);
            $table->decimal('monthly_stamp_tax', 10, 2)->default(0);

            $table->boolean('is_held')->default(false)->comment('إيقاف عن الصرف هذا الشهر');
            $table->string('hold_reason')->nullable();
            $table->text('notes')->nullable();

            $table->integer('com_code');
            $table->integer('added_by')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_factors');
    }
};
