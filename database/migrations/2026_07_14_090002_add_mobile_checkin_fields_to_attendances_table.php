<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Supports self-service check-in/out from the Employee app: GPS captured at the
     * moment of the tap, the source of the punch, and whether the device's own
     * biometric gate (Face ID / fingerprint) passed client-side before the call —
     * audit-only, not cryptographically verified server-side.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->decimal('check_in_lat', 10, 7)->nullable()->after('check_in_time');
            $table->decimal('check_in_lng', 10, 7)->nullable()->after('check_in_lat');
            $table->decimal('check_out_lat', 10, 7)->nullable()->after('check_out_time');
            $table->decimal('check_out_lng', 10, 7)->nullable()->after('check_out_lat');
            $table->string('source', 20)->default('fingerprint_device')->after('check_out_lng')
                ->comment('fingerprint_device | mobile_app');
            $table->boolean('device_verified')->default(false)->after('source')
                ->comment('client-reported local biometric pass — audit only');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'check_in_lat', 'check_in_lng',
                'check_out_lat', 'check_out_lng',
                'source', 'device_verified',
            ]);
        });
    }
};
