<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('name_dictionary', function (Blueprint $table) {
            $table->id();
            $table->string('ar_name');
            $table->string('en_name');
            $table->integer('com_code')->nullable()->index();
            $table->timestamps();
            $table->unique(['ar_name', 'com_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('name_dictionary');
    }
};
