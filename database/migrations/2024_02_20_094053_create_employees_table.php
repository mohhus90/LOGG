<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id')->unique();
            $table->integer('finger_id');
            $table->string('employee_name_A');
            $table->string('employee_name_E');
            $table->string('employee_address')->nullable();
            $table->tinyInteger('emp_gender')->comment('(1=ذكر),(2= انثى)')->nullable();
            $table->tinyInteger('emp_social_status')->comment('(1=اعزب),(2= متزوج),(3= متزوج ويعول)')->nullable();
            $table->tinyInteger('emp_military_status')->comment('(1=ادى الخدمة),(2= اعفاء),(3= مؤجل)')->nullable();
            $table->string('emp_qualification')->nullable();
            $table->string('qualification_year')->nullable();
            $table->tinyInteger('qualification_grade')->comment('(1=امتياز),(2= جيد جدا),(3= جيد مرتفع),(4= جيد),(4= مقبول)')->nullable();
            $table->date('emp_start_date')->nullable();
            $table->tinyInteger('insurance_status')->default(1)->comment('(1=يعمل),(2= لايعمل)')->nullable();
            $table->tinyInteger('resignation_status')->default(1)->comment('(1=استقالة),(2= فصل),(3= ترك العمل),(4= سن المعاش),(5= الوفاة)')->nullable();
            $table->date('resignation_date')->nullable();
            $table->string('resignation_cause')->nullable()->comment('سبب ترك العمل');
            $table->tinyInteger('motivation_type')->default(0)->comment('(1=ثابت),(2= متغير),(0= لايوجد)')->nullable();
            $table->decimal('motivation',10,2)->default(0)->nullable();
            $table->tinyInteger('sal_cash_visa')->default(0)->comment('(1=كاش),(2= فيزا)')->nullable();
            $table->string('bank_name',50)->nullable();
            $table->string('bank_account',50)->nullable()->unique();
            $table->string('bank_ID',50)->nullable();
            $table->string('bank_branch',50)->nullable();
            $table->decimal('daily_work_hours',10,2)->nullable();
            $table->foreignId('emp_jobs_id')->references('id')->on('jobs_categories')->onUpdate('cascade');
            $table->string('national_id',50)->nullable()->unique();
            $table->string('insurance_no',50)->nullable()->unique();
            $table->foreignId('emp_departments_id')->references('id')->on('departments')->onUpdate('cascade');
            $table->string('emp_home_tel',50)->nullable();
            $table->string('emp_mobile',50)->nullable()->unique();
            $table->string('emp_email',50)->nullable()->unique();
            $table->string('emp_photo',100)->nullable();
            $table->date('birth_date')->nullable();
            $table->decimal('emp_sal',10,2)->nullable();
            $table->decimal('emp_fixed_allowances',10,2)->nullable();
            $table->decimal('emp_sal_insurance',10,2)->default(0)->nullable();
            $table->decimal('medical_insurance',10,2)->default(0)->nullable();
            $table->tinyInteger('is_has_fixed_shift')->default(1)->comment('(1=يوجد),(2= لايوجد)')->nullable();
            $table->foreignId('shifts_types_id')->references('id')->on('shifts_types')->onUpdate('cascade');
            $table->tinyInteger('is_has_finger')->default(1)->comment('(1=يوجد),(2= لايوجد)')->nullable();
            $table->tinyInteger('vacation_formula')->default(1)->comment('(1=يوجد),(2= لايوجد)')->nullable();
            $table->tinyInteger('sensitive_data')->default(1)->comment('(1=يوجد),(2= لايوجد)')->nullable();
            $table->foreignId('branches_id')->references('id')->on('branches')->onUpdate('cascade');
            $table->integer('com_code');
            $table->integer('added_by')->refrences('id')->on('admins')->onupdate('cascade');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};    