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
        Schema::table('commission_rules', function (Blueprint $table) {
            // ربط قاعدة العمولة بمستوى وظيفي محدد في الهيكل
            // null = تنطبق على جميع المستويات
            $table->unsignedBigInteger('org_level_id')->nullable()->after('recipient_type');
            $table->foreign('org_level_id')->references('id')->on('org_levels')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('commission_rules', function (Blueprint $table) {
            $table->dropForeign(['org_level_id']);
            $table->dropColumn('org_level_id');
        });
    }
};
