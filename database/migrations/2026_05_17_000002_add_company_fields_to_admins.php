<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * إضافة company_id و is_super_admin لجدول admins
 * يُشغَّل مرة واحدة
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            if (!Schema::hasColumn('admins', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id')
                    ->comment('ربط الأدمن بالشركة');

                // foreign key اختياري — يُضاف فقط إذا وجد جدول companies
                if (Schema::hasTable('companies')) {
                    $table->foreign('company_id')->references('id')
                        ->on('companies')->onDelete('set null');
                }
            }

            if (!Schema::hasColumn('admins', 'is_super_admin')) {
                $table->tinyInteger('is_super_admin')->default(0)->after('company_id')
                    ->comment('(1=سوبر أدمن),(0=أدمن عادي)');
            }
        });

        // ── تحديث البيانات الموجودة: ربط الأدمن الحالي بشركته ──
        // الأدمن رقم 1 يصبح سوبر أدمن تلقائياً
        \DB::table('admins')->where('id', 1)->update(['is_super_admin' => 1]);
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            if (Schema::hasColumn('admins', 'company_id')) {
                if (Schema::hasTable('companies')) {
                    $table->dropForeign(['company_id']);
                }
                $table->dropColumn('company_id');
            }
            if (Schema::hasColumn('admins', 'is_super_admin')) {
                $table->dropColumn('is_super_admin');
            }
        });
    }
};