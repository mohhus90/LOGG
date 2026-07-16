<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_payrolls', function (Blueprint $table) {
            if (!Schema::hasColumn('monthly_payrolls', 'is_held')) {
                $table->boolean('is_held')->default(false)->after('status')
                      ->comment('إيقاف عن الصرف هذا الشهر');
            }
            if (!Schema::hasColumn('monthly_payrolls', 'hold_reason')) {
                $table->string('hold_reason')->nullable()->after('is_held');
            }
        });
    }

    public function down(): void
    {
        Schema::table('monthly_payrolls', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('monthly_payrolls', 'is_held')     ? 'is_held'     : null,
                Schema::hasColumn('monthly_payrolls', 'hold_reason') ? 'hold_reason' : null,
            ]));
        });
    }
};
