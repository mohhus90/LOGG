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
        Schema::table('employees', function (Blueprint $table) {
            // Reference-only login credentials for the employee (not wired to any real auth guard).
            // Password is stored in plain text intentionally so it can be exported/read via Excel.
            $table->string('login_username', 100)->nullable()->after('client_notes');
            $table->string('login_password', 100)->nullable()->after('login_username');
            $table->unique(['com_code', 'login_username']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropUnique(['com_code', 'login_username']);
            $table->dropColumn(['login_username', 'login_password']);
        });
    }
};
