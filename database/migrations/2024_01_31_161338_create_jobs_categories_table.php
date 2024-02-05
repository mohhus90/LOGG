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
        Schema::create('jobs_categories', function (Blueprint $table) {
            $table->id();
            $table->string('job_name',255);
            $table->integer('com_code');
            $table->integer('added_by')->references('id')->on('admins')->onupdate('cascade');
            $table->integer('updated_by')->references('id')->on('admins')->onupdate('cascade')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs_categories');
    }
};
