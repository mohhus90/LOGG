<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_of_materials', function (Blueprint $table) {
            $table->id();
            $table->integer('com_code');
            $table->unsignedBigInteger('item_id');
            $table->unsignedSmallInteger('version')->default(1);
            $table->decimal('output_quantity', 15, 4)->default(1);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['com_code', 'item_id', 'version'], 'bom_item_version_unique');
        });

        Schema::create('bill_of_material_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bom_id');
            $table->unsignedBigInteger('component_item_id');
            $table->decimal('quantity', 15, 4);
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->decimal('scrap_percent', 5, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('bom_id')->references('id')->on('bill_of_materials')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_of_material_lines');
        Schema::dropIfExists('bill_of_materials');
    }
};
