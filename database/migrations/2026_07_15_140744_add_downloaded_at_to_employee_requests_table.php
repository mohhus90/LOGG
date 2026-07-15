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
            $table->timestamp('downloaded_at')->nullable()->after('reviewed_at')
                ->comment('للطلبات من نوع document_download/salary_certificate: وقت أول تنزيل فعلي - كل موافقة تُستهلك بمجرد أول تنزيل');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_requests', function (Blueprint $table) {
            $table->dropColumn('downloaded_at');
        });
    }
};
