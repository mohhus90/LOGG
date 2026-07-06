<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('com_code');
            $table->string('code')->nullable();
            $table->string('barcode')->nullable();
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('unit_id')->nullable();
            // product=منتج تام, service=خدمة, raw_material=مادة خام, semi_finished=نصف مصنع
            $table->enum('type', ['product', 'service', 'raw_material', 'semi_finished'])->default('product');
            $table->decimal('cost_price', 15, 4)->default(0);
            $table->decimal('selling_price', 15, 4)->default(0);
            $table->decimal('min_selling_price', 15, 4)->default(0);
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('items'); }
};
