<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_centers', function (Blueprint $table) {
            $table->id();
            $table->integer('com_code');
            $table->string('code', 20);
            $table->string('name', 150);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['com_code', 'code']);
            $table->foreign('parent_id')->references('id')->on('cost_centers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_centers');
    }
};
