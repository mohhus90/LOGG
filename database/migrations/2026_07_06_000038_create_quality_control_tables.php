<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quality_checklists', function (Blueprint $table) {
            $table->id();
            $table->integer('com_code');
            $table->string('name', 150);
            $table->enum('applies_to', ['production', 'purchase', 'both'])->default('both');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('quality_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('checklist_id');
            $table->string('criterion', 255);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('checklist_id')->references('id')->on('quality_checklists')->cascadeOnDelete();
        });

        Schema::create('quality_inspections', function (Blueprint $table) {
            $table->id();
            $table->integer('com_code');
            $table->string('inspection_number', 30);
            $table->unsignedBigInteger('checklist_id');
            $table->enum('source_type', ['production_order', 'purchase_invoice']);
            $table->unsignedBigInteger('source_id');
            $table->unsignedBigInteger('inspector_id')->nullable();
            $table->date('date');
            $table->enum('overall_result', ['pass', 'fail', 'conditional'])->default('pass');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['com_code', 'inspection_number']);
            $table->index(['source_type', 'source_id']);
            $table->foreign('checklist_id')->references('id')->on('quality_checklists');
        });

        Schema::create('quality_inspection_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inspection_id');
            $table->unsignedBigInteger('checklist_item_id');
            $table->enum('result', ['pass', 'fail', 'na'])->default('pass');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('inspection_id')->references('id')->on('quality_inspections')->cascadeOnDelete();
            $table->foreign('checklist_item_id')->references('id')->on('quality_checklist_items');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_inspection_items');
        Schema::dropIfExists('quality_inspections');
        Schema::dropIfExists('quality_checklist_items');
        Schema::dropIfExists('quality_checklists');
    }
};
