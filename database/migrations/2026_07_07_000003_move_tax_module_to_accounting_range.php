<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * بلوك "الضرائب والفاتورة الإلكترونية" انتقل بصريًا من قائمة HR الجانبية إلى
 * قائمة المحاسبة (sidebar_accounting.blade.php + layout المحاسبة). هذا التحديث
 * يضع module_key الخاص به ضمن نطاق المحاسبة المحجوز أصلًا (56-59) بدل 90.
 */
return new class extends Migration {
    public function up(): void
    {
        DB::table('admin_modules')->where('module_key', 'tax')->update(['sort_order' => 56, 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('admin_modules')->where('module_key', 'tax')->update(['sort_order' => 90, 'updated_at' => now()]);
    }
};
