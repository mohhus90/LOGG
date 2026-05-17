<?php
// ============================================================
// FILE: database/migrations/2024_04_01_000002_create_vacations_system.php
// نظام الإجازات: اعتيادي + عارض + طلبات الموظفين
// ============================================================
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── رصيد إجازات الموظف (سنوي) ──
        Schema::create('employee_vacation_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->integer('year')->comment('السنة');
            $table->decimal('annual_balance', 8, 2)->default(0)
                ->comment('رصيد الإجازة الاعتيادية (21 يوم قانون مصري)');
            $table->decimal('annual_used', 8, 2)->default(0)->comment('المستخدم من الاعتيادية');
            $table->decimal('annual_remaining', 8, 2)->default(0)->comment('المتبقي من الاعتيادية');
            $table->decimal('casual_balance', 8, 2)->default(0)
                ->comment('رصيد الإجازة العارضة (6 أيام قانون مصري)');
            $table->decimal('casual_used', 8, 2)->default(0)->comment('المستخدم من العارضة');
            $table->decimal('casual_remaining', 8, 2)->default(0)->comment('المتبقي من العارضة');
            $table->decimal('monthly_accrual', 8, 2)->default(0)
                ->comment('الاستحقاق الشهري = الرصيد السنوي / 12');
            $table->integer('com_code');
            $table->timestamps();
            $table->unique(['employee_id', 'year']);
        });

        // ── طلبات الموظفين (إجازة / تأخير / انصراف مبكر) ──
        Schema::create('employee_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('request_type', 30)
                ->comment('annual_vacation | casual_vacation | late_permission | early_leave | mission');
            $table->date('request_date')->comment('تاريخ الطلب');
            $table->date('start_date')->comment('بداية الإجازة أو موعد التأخير');
            $table->date('end_date')->nullable()->comment('نهاية الإجازة');
            $table->time('time_from')->nullable()->comment('وقت الإذن من');
            $table->time('time_to')->nullable()->comment('وقت الإذن إلى');
            $table->decimal('days_count', 5, 1)->default(1)->comment('عدد الأيام');
            $table->text('reason')->nullable()->comment('سبب الطلب');
            $table->tinyInteger('status')->default(0)
                ->comment('(0=قيد الانتظار),(1=مقبول),(2=مرفوض),(3=ملغي)');
            $table->integer('reviewed_by')->nullable()->comment('من راجع الطلب');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->integer('com_code');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_requests');
        Schema::dropIfExists('employee_vacation_balances');
    }
};
