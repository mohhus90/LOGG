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
            $table->integer('employee_id');
            $table->integer('fiinger_id');
            $table->string('employee_name');
            $table->string('employee_adress')->nullable();
            $table->tinyInteger('emp_gender')->comment('(1=ذكر),(2= انثى)')->nullable();
            $table->tinyInteger('emp_social_status')->comment('(1=اعزب),(2= متزوج),(3= متزوج ويعول)')->nullable();
            $table->tinyInteger('emp_military_status')->comment('(1=ادى الخدمة),(2= اعفاء),(3= مؤجل)')->nullable();
            $table->string('emp_qualification')->nullable();
            $table->string('qualification_yaear')->nullable();
            $table->tinyInteger('qualification_grade')->comment('(1=امتياز),(2= جيد جدا),(3= جيد مرتفع),(4= جيد),(4= مقبول)')->nullable();
            $table->date('emp_start_date')->nullable();
            $table->string('qualification_yaear')->nullable();
            $table->tinyInteger('functional_status')->default(1)->comment('(1=يعمل),(2= لايعمل)')->nullable();
            $table->tinyInteger('resignation_status')->default(1)->comment('(1=استقالة),(2= فصل),(3= ترك العمل),(4= سن المعاش),(5= الوفاة)')->nullable();
            $table->date('resignation_date')->nullable();
            $table->string('resignation_cause')->nullable()->comment('سبب ترك العمل');

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
