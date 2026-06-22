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
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->string('doc_type', 50);
            $table->string('doc_original_name', 255);
            $table->string('doc_path', 500);
            $table->unsignedBigInteger('com_code');
            $table->unsignedBigInteger('added_by')->nullable();
            $table->timestamps();
            $table->index(['employee_id', 'doc_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
    }
};
