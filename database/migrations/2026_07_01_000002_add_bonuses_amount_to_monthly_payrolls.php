<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_payrolls', function (Blueprint $table) {
            $table->decimal('bonuses_amount', 10, 2)->default(0)
                  ->comment('إجمالي المكافآت')
                  ->after('commissions_amount');
        });
    }

    public function down(): void
    {
        Schema::table('monthly_payrolls', function (Blueprint $table) {
            $table->dropColumn('bonuses_amount');
        });
    }
};
