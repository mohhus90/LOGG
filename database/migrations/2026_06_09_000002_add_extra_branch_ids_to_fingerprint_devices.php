<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fingerprint_devices', function (Blueprint $table) {
            // فروع إضافية تخدمها هذا الجهاز (موظفو هذه الفروع يبصمون على هذا الجهاز)
            $table->json('extra_branch_ids')->nullable()->after('branches_id');
        });
    }

    public function down(): void
    {
        Schema::table('fingerprint_devices', function (Blueprint $table) {
            $table->dropColumn('extra_branch_ids');
        });
    }
};
