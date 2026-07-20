<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ecommerce_stores', function (Blueprint $table) {
            $table->decimal('wallet_balance', 15, 2)->nullable()->after('last_sync_error');
            $table->timestamp('wallet_synced_at')->nullable()->after('wallet_balance');
        });
    }

    public function down(): void
    {
        Schema::table('ecommerce_stores', function (Blueprint $table) {
            $table->dropColumn(['wallet_balance', 'wallet_synced_at']);
        });
    }
};
