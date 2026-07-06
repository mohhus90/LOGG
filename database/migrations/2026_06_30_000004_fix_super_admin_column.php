<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // إضافة عمود is_super_admin إذا لم يكن موجوداً
        if (!Schema::hasColumn('admins', 'is_super_admin')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->tinyInteger('is_super_admin')->default(0)->after('com_code')
                    ->comment('1=سوبر أدمن يتجاوز كل الصلاحيات');
            });
        }

        // إضافة عمود company_id إذا لم يكن موجوداً
        if (!Schema::hasColumn('admins', 'company_id')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id');
            });
        }

        // جعل أول أدمن في النظام سوبر أدمن تلقائياً
        $firstAdmin = DB::table('admins')->orderBy('id')->first();
        if ($firstAdmin) {
            DB::table('admins')->where('id', $firstAdmin->id)
                ->update(['is_super_admin' => 1]);
        }
    }

    public function down(): void
    {
        // لا نتراجع عن صلاحيات السوبر أدمن تلقائياً
    }
};
