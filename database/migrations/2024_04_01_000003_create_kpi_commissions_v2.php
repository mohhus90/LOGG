<?php
// ============================================================
// FILE: database/migrations/2024_04_01_000003_create_kpi_commissions_v2.php
// ============================================================
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── تعريف مؤشرات الأداء KPIs ──
        Schema::create('kpi_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->comment('اسم المؤشر');
            $table->string('code', 50)->unique()->comment('كود المؤشر');
            $table->string('category', 50)->default('performance')
                ->comment('performance | quality | attendance | sales | custom');
            $table->string('measurement_unit', 50)->nullable()
                ->comment('% | رقم | ريال | نقطة');
            $table->decimal('target_value', 10, 2)->default(100)
                ->comment('القيمة المستهدفة');
            $table->decimal('weight', 5, 2)->default(100)
                ->comment('الوزن النسبي للمؤشر من 100');
            $table->tinyInteger('affects_salary')->default(0)
                ->comment('(1=يؤثر على الراتب),(0=للإحصاء فقط)');
            $table->string('salary_effect_type', 20)->default('bonus')
                ->comment('bonus=مكافأة | deduction=خصم | both');
            $table->decimal('max_bonus_pct', 5, 2)->default(0)
                ->comment('أقصى نسبة مكافأة من الراتب %');
            $table->decimal('max_deduction_pct', 5, 2)->default(0)
                ->comment('أقصى نسبة خصم من الراتب %');
            $table->tinyInteger('is_active')->default(1);
            $table->text('description')->nullable();
            $table->integer('com_code');
            $table->integer('added_by');
            $table->timestamps();
        });

        // ── قراءات KPI للموظف (شهرية) ──
        Schema::create('kpi_employee_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_id')->constrained('kpi_definitions')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->integer('month');
            $table->integer('year');
            $table->decimal('actual_value', 10, 2)->default(0)->comment('القيمة الفعلية المحققة');
            $table->decimal('achievement_pct', 6, 2)->default(0)
                ->comment('نسبة الإنجاز = actual / target × 100');
            $table->decimal('score', 6, 2)->default(0)
                ->comment('النقاط = achievement_pct × weight / 100');
            $table->decimal('salary_effect_amount', 10, 2)->default(0)
                ->comment('قيمة التأثير المالي على الراتب');
            $table->tinyInteger('effect_direction')->default(1)
                ->comment('(1=مكافأة),(2=خصم)');
            $table->text('notes')->nullable();
            $table->integer('com_code');
            $table->integer('added_by');
            $table->timestamps();
            $table->unique(['kpi_id', 'employee_id', 'month', 'year']);
        });

        // ── قواعد العمولات المرنة ──
        Schema::create('commission_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->comment('اسم قاعدة العمولة');
            $table->string('code', 50)->unique();
            $table->string('basis', 30)
                ->comment('individual_sales | branch_sales | area_sales | company_sales | fixed | kpi_based');
            $table->string('recipient_type', 30)
                ->comment('employee | branch_manager | area_manager | sales_manager | all_branch');
            $table->string('calc_type', 20)->default('percentage')
                ->comment('percentage | fixed_amount | tiered');
            $table->decimal('percentage', 6, 3)->default(0)->comment('النسبة المئوية');
            $table->decimal('fixed_amount', 10, 2)->default(0)->comment('المبلغ الثابت');
            $table->json('tiers')->nullable()
                ->comment('[{"from":0,"to":10000,"pct":1},{"from":10001,"to":null,"pct":2}]');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->tinyInteger('is_active')->default(1);
            $table->text('description')->nullable();
            $table->integer('com_code');
            $table->integer('added_by');
            $table->timestamps();
        });

        // ── سجلات المبيعات لاحتساب العمولة ──
        Schema::create('sales_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->integer('month');
            $table->integer('year');
            $table->decimal('sales_amount', 12, 2)->default(0)->comment('قيمة المبيعات');
            $table->string('sales_type', 50)->nullable()->comment('نوع المبيعات');
            $table->text('notes')->nullable();
            $table->integer('com_code');
            $table->integer('added_by');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_records');
        Schema::dropIfExists('commission_rules');
        Schema::dropIfExists('kpi_employee_scores');
        Schema::dropIfExists('kpi_definitions');
    }
};
