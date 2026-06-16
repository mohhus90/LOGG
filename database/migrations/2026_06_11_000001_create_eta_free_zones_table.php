<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eta_free_zones', function (Blueprint $table) {
            $table->id();
            $table->integer('com_code')->index();
            $table->string('tax_id', 20);
            $table->string('name')->nullable();
            $table->timestamps();

            $table->unique(['com_code', 'tax_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eta_free_zones');
    }
};
