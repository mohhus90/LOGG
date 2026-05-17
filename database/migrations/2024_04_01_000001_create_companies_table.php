<?php
// ============================================================
// FILE: database/migrations/2024_04_01_000001_create_companies_table.php
// استبدال com_code بجدول شركات حقيقي
// ============================================================
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // جدول الشركات
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200)->comment('اسم الشركة');
            $table->string('slug', 200)->unique()->comment('المعرف الفريد للشركة');
            $table->string('phone', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('address', 300)->nullable();
            $table->string('logo', 300)->nullable()->comment('مسار اللوجو');
            $table->tinyInteger('is_active')->default(1)->comment('(1=نشطة),(0=معطلة)');
            $table->timestamps();
        });

        // ربط الأدمن بالشركة
        Schema::table('admins', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')
                ->constrained('companies')->onDelete('cascade');
            $table->tinyInteger('is_super_admin')->default(0)->after('company_id')
                ->comment('(1=سوبر أدمن),(0=أدمن عادي)');
        });

        // ربط الضبط العام بالشركة (بدلاً من com_code)
        Schema::table('admin_panel_settings', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')
                ->constrained('companies')->onDelete('cascade');
            $table->string('logo', 300)->nullable()->change();
            // حقول إضافية لنظام التأخير بالدقيقة
            // $table->decimal('sanctions_value_minute_delay', 10, 2)->default(0)
            //     ->comment('قيمة خصم الدقيقة (تأخير أو انصراف مبكر)');
            $table->tinyInteger('delay_calc_mode')->default(1)
                ->comment('(1=بالدقيقة),(2=نصف يوم بعد X مرة),(3=يوم بعد X مرة)');
        });
    }

    public function down(): void
    {
        Schema::table('admin_panel_settings', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn(['company_id', 'sanctions_value_minute_delay', 'delay_calc_mode']);
        });
        Schema::table('admins', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn(['company_id', 'is_super_admin']);
        });
        Schema::dropIfExists('companies');
    }
};
