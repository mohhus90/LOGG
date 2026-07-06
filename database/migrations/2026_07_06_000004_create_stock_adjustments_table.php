<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('com_code');
            $table->string('adjustment_number');
            $table->date('date');
            $table->unsignedBigInteger('warehouse_id');
            $table->enum('type', ['increase', 'decrease'])->default('increase');
            $table->text('reason')->nullable();
            // draft=مسودة, approved=معتمد (يؤثر على المخزون), rejected=مرفوض
            $table->enum('status', ['draft', 'approved', 'rejected'])->default('draft');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('stock_adjustments'); }
};
