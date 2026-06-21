<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('client_name', 255);
            $table->string('client_name_A', 255)->nullable();
            $table->string('contact_person', 255)->nullable();
            $table->string('phone', 100)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('address', 500)->nullable();
            $table->string('industry', 150)->nullable();
            $table->tinyInteger('active')->default(1)->comment('1=مفعل, 0=معطل');
            $table->text('notes')->nullable();
            $table->integer('com_code');
            $table->integer('added_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
