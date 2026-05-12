<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * جدول صلاحيات الأدمن على كل قسم
     * لكل أدمن يتم تحديد صلاحيات: قراءة، إضافة، تعديل، حذف
     * لكل قسم من أقسام التطبيق
     */
    public function up(): void
    {
        // تعديل جدول الأدمن لإضافة حقل is_super_admin
        Schema::table('admins', function (Blueprint $table) {
            $table->tinyInteger('is_super_admin')->default(0)->after('com_code')
                  ->comment('(1=سوبر ادمن),(0=ادمن عادي)');
        });

        // جدول الأقسام/الموديولات
        Schema::create('admin_modules', function (Blueprint $table) {
            $table->id();
            $table->string('module_key', 50)->unique()->comment('مفتاح القسم مثل employees, attendance ...');
            $table->string('module_name', 100)->comment('اسم القسم بالعربي');
            $table->string('module_icon', 50)->nullable()->comment('أيقونة FontAwesome');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // جدول صلاحيات الأدمن
        Schema::create('admin_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');
            $table->foreignId('module_id')->constrained('admin_modules')->onDelete('cascade');
            $table->tinyInteger('can_read')->default(0)->comment('صلاحية القراءة');
            $table->tinyInteger('can_create')->default(0)->comment('صلاحية الإضافة');
            $table->tinyInteger('can_update')->default(0)->comment('صلاحية التعديل');
            $table->tinyInteger('can_delete')->default(0)->comment('صلاحية الحذف');
            $table->integer('com_code');
            $table->timestamps();

            $table->unique(['admin_id', 'module_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_permissions');
        Schema::dropIfExists('admin_modules');
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('is_super_admin');
        });
    }
};
