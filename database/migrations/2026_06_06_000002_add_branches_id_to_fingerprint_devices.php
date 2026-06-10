<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fingerprint_devices', function (Blueprint $table) {
            $table->unsignedBigInteger('branches_id')->nullable()->after('location');
            $table->foreign('branches_id')->references('id')->on('branches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('fingerprint_devices', function (Blueprint $table) {
            $table->dropForeign(['branches_id']);
            $table->dropColumn('branches_id');
        });
    }
};
