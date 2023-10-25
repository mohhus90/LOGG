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
        Schema::create('admin_panel_settings', function (Blueprint $table) {
            $table->id();
            $table->string('com_name',250);
            $table->tinyInteger('saysem_status')->default(1)->comment('واحد مفعل- صفر معطل');
            $table->string('image',250);
            $table->string('phone',250);
            $table->string('address',250);
            $table->string('email',250)->nullable();
            $table->integer('added_by');
            $table->integer('updated_by')->nullable();
            $table->integer('com_code');
            $table->decimal('after_minute_calc_delay',10,2)->default(0)->comment('بعد كم دقيقة تحسب تأخير حضور');
            $table->decimal('after_minute_calc_early',10,2)->default(0)->comment('بعد كم دقيقة تحسب انصراف مبكر');
            $table->decimal('after_minute_quarterday',10,2)->default(0)->comment('بعد كم دقيقة مجموع الانصراف المبكر والحضور المتأخر تخصم ربع يوم');
            $table->decimal('after_time_half_daycut',10,2)->default(0)->comment('بعد كم مرة تأخير أو انصراف مبكر يخصم نصف يوم');
            $table->decimal('after_time_allday_daycut',10,2)->default(0)->comment('بعد كم مرة تأخير أو انصراف مبكر يخصم يوم');
            $table->decimal('sanctions_value_minute_delay',10,2)->default(0)->comment('قيمة خصم التأخير والانصراف المبكر بالدقيقة');
            $table->decimal('sanctions_value_hour_delay',10,2)->default(0)->comment('قيمة خصم التأخير والانصراف المبكر بالساعة');
            $table->decimal('monthly_vacation_balance',10,2)->default(0)->comment('رصيد اجازات الموظف الشهرى');
            $table->decimal('first_balance_begain_vacation',10,2)->default(0)->comment('رصيد الاجازات الاولى بعد مدة 6 شهور مثلا');
            $table->decimal('after_days_begain_vacation',10,2)->default(0)->comment('بعد كم يوم ينزل للموظف رصيد اجازات');
            $table->decimal('sanctions_value_first_abcence',10,2)->default(0)->comment('قيمة خصم الايام بعد اول مرة غياب');
            $table->decimal('sanctions_value_second_abcence',10,2)->default(0)->comment('قيمة خصم الايام بعد ثانى غياب');
            $table->decimal('sanctions_value_third_abcence',10,2)->default(0)->comment('قيمة خصم الايام بعد ثالث غياب');
            $table->decimal('sanctions_value_forth_abcence',10,2)->default(0)->comment('قيمة خصم الايام بعد رابع غياب');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_panel_settings');
    }
};
