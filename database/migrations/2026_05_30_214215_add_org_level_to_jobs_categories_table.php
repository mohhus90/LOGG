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
        Schema::table('jobs_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('org_level_id')->nullable()->after('job_name');
            $table->foreign('org_level_id')->references('id')->on('org_levels')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('jobs_categories', function (Blueprint $table) {
            $table->dropForeign(['org_level_id']);
            $table->dropColumn('org_level_id');
        });
    }
};
