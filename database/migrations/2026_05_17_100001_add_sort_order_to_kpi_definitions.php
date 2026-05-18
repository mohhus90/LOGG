<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * إضافة عمود sort_order إلى جدول kpi_definitions
 * يحل مشكلة: Unknown column 'sort_order' in 'field list'
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kpi_definitions', function (Blueprint $table) {
            if (!Schema::hasColumn('kpi_definitions', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('is_active')
                    ->comment('ترتيب العرض');
            }
        });
    }

    public function down(): void
    {
        Schema::table('kpi_definitions', function (Blueprint $table) {
            if (Schema::hasColumn('kpi_definitions', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });
    }
};
