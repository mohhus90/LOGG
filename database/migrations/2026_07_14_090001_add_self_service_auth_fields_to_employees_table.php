<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * login_password_hash powers real authentication (Employee Self-Service app/API).
     * The existing plaintext login_password column is left untouched — it stays as the
     * HR-export field it was originally added for.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('login_password_hash', 255)->nullable()->after('login_password');
            $table->boolean('location_tracking_enabled')->default(true)->after('login_password_hash');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['login_password_hash', 'location_tracking_enabled']);
        });
    }
};
