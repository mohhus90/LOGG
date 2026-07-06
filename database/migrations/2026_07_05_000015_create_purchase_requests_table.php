<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('com_code');
            $table->string('request_number');
            $table->date('date');
            $table->date('needed_by_date')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            // draft=مسودة, submitted=مقدم, approved=معتمد, rejected=مرفوض, converted=تم التحويل لأمر شراء
            $table->enum('status', ['draft','submitted','approved','rejected','converted'])->default('draft');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('purchase_requests'); }
};
