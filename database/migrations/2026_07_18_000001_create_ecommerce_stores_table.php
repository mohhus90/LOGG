<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ecommerce_stores', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('com_code')->index();
            $table->string('provider')->default('wuilt');
            $table->string('name')->nullable();
            $table->string('store_id');
            $table->text('api_key');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sync_interval_minutes')->default(15);
            $table->timestamp('last_synced_at')->nullable();
            $table->enum('last_sync_status', ['success', 'failed', 'never'])->default('never');
            $table->unsignedInteger('last_sync_count')->default(0);
            $table->text('last_sync_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecommerce_stores');
    }
};
