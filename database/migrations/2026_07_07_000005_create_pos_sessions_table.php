<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('com_code');
            $table->unsignedBigInteger('register_id');
            $table->unsignedBigInteger('opened_by');
            $table->decimal('opening_amount', 15, 2)->default(0);
            $table->decimal('expected_closing_amount', 15, 2)->nullable();
            $table->decimal('counted_closing_amount', 15, 2)->nullable();
            $table->decimal('difference', 15, 2)->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->foreign('register_id')->references('id')->on('pos_registers');
            $table->foreign('opened_by')->references('id')->on('admins');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_sessions');
    }
};
