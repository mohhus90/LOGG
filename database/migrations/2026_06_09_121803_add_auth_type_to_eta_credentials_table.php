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
        Schema::table('eta_credentials', function (Blueprint $table) {
            // portal = إيميل + باسوورد (ROPC) | api = client_id + client_secret (client_credentials)
            $table->string('auth_type')->default('portal')->after('com_code');
        });
    }

    public function down(): void
    {
        Schema::table('eta_credentials', function (Blueprint $table) {
            $table->dropColumn('auth_type');
        });
    }
};
