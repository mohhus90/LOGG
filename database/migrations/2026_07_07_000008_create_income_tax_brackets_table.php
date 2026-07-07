<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('income_tax_brackets', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('com_code');
            $table->decimal('from_amount', 12, 2);
            $table->decimal('to_amount', 12, 2)->nullable()->comment('فارغ = بلا حد أعلى (آخر شريحة)');
            $table->decimal('rate', 5, 2)->comment('نسبة الضريبة % على الجزء الواقع داخل هذه الشريحة');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('income_tax_brackets');
    }
};
