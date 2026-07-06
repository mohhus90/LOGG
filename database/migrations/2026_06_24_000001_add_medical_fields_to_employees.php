<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('medical_id')->nullable()->after('client_notes');
            $table->string('medical_status')->nullable()->after('medical_id');
            $table->string('medical_progress')->nullable()->after('medical_status');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['medical_id', 'medical_status', 'medical_progress']);
        });
    }
};
