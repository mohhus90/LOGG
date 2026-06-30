<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // أهداف المبيعات الشهرية لكل فرع
        Schema::create('branch_targets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->tinyInteger('month');
            $table->smallInteger('year');
            $table->decimal('target_amount', 14, 2)->default(0);
            $table->integer('com_code');
            $table->unsignedBigInteger('added_by')->nullable();
            $table->timestamps();
            $table->unique(['branch_id', 'month', 'year', 'com_code'], 'branch_target_unique');
        });

        // خطط عمولات الفروع (مبنية على نسبة تحقيق التارجت)
        Schema::create('branch_commission_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->unsignedBigInteger('branch_id');
            $table->text('description')->nullable();
            $table->json('tiers')->nullable();
            // tiers JSON structure:
            // [{"from_pct":60,"to_pct":70,"seller_rate":0.5,"manager_rate":0.25}, ...]
            // to_pct = null means "greater than from_pct" (no upper limit)
            $table->boolean('is_active')->default(true);
            $table->integer('com_code');
            $table->unsignedBigInteger('added_by')->nullable();
            $table->timestamps();
        });

        // أعضاء كل خطة (بائعون ومديرو فروع)
        Schema::create('branch_commission_plan_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plan_id');
            $table->unsignedBigInteger('employee_id');
            $table->string('role', 20)->default('seller'); // seller | manager
            $table->boolean('also_as_seller')->default(false); // مدير يُحتسب أيضاً كبائع
            $table->timestamps();
            $table->unique(['plan_id', 'employee_id'], 'plan_member_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_commission_plan_members');
        Schema::dropIfExists('branch_commission_plans');
        Schema::dropIfExists('branch_targets');
    }
};
