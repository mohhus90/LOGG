<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sales_records', function (Blueprint $table) {
            $table->tinyInteger('from_day')->unsigned()->nullable()->after('notes')
                ->comment('اليوم الأول لفترة المبيعات');
            $table->tinyInteger('to_day')->unsigned()->nullable()->after('from_day')
                ->comment('اليوم الأخير لفترة المبيعات');
        });
    }

    public function down(): void
    {
        Schema::table('sales_records', function (Blueprint $table) {
            $table->dropColumn(['from_day', 'to_day']);
        });
    }
};
