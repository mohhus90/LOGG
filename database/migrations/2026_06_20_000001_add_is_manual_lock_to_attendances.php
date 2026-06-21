<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->tinyInteger('is_manual_lock')->default(0)->after('is_before_hire')
                  ->comment('1 = السجل محمي من أي معالجة بصمة تلقائية');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('is_manual_lock');
        });
    }
};
