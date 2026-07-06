<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_categories', function (Blueprint $table) {
            $table->id();
            $table->integer('com_code');
            $table->string('name', 150);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->integer('com_code');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('title', 200);
            $table->string('file_path', 500);
            $table->string('file_original_name', 255);
            $table->string('linked_type', 50)->nullable();
            $table->unsignedBigInteger('linked_id')->nullable();
            $table->unsignedSmallInteger('version')->default(1);
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected'])->default('draft');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();

            $table->index(['linked_type', 'linked_id']);
            $table->foreign('category_id')->references('id')->on('document_categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
        Schema::dropIfExists('document_categories');
    }
};
