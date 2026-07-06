<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('treasury_voucher_id')->nullable()->after('id');
        });
        Schema::table('purchase_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('treasury_voucher_id')->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('sales_payments', function (Blueprint $table) {
            $table->dropColumn('treasury_voucher_id');
        });
        Schema::table('purchase_payments', function (Blueprint $table) {
            $table->dropColumn('treasury_voucher_id');
        });
    }
};
