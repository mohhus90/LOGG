<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eta_credentials', function (Blueprint $table) {
            $table->id();
            $table->integer('com_code')->index();
            $table->string('client_id');
            $table->text('client_secret');
            $table->string('taxpayer_id')->nullable()->comment('الرقم الضريبي للمنشأة');
            $table->string('taxpayer_name')->nullable();
            $table->text('access_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('com_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eta_credentials');
    }
};
