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
        Schema::table('employee_requests', function (Blueprint $table) {
            $table->foreignId('document_id')->nullable()->after('employee_id')
                ->constrained('employee_documents')->nullOnDelete()
                ->comment('للطلبات من نوع document_download فقط: المستند المطلوب تنزيله');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_requests', function (Blueprint $table) {
            $table->dropForeign(['document_id']);
            $table->dropColumn('document_id');
        });
    }
};
