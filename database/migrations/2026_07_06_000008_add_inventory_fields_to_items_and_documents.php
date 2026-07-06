<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->decimal('reorder_level', 15, 4)->default(0)->after('min_selling_price');
        });

        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('warehouse_id')->nullable()->after('branch_id');
        });
        Schema::table('sales_returns', function (Blueprint $table) {
            $table->unsignedBigInteger('warehouse_id')->nullable()->after('branch_id');
        });
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('warehouse_id')->nullable()->after('branch_id');
        });
        Schema::table('purchase_returns', function (Blueprint $table) {
            $table->unsignedBigInteger('warehouse_id')->nullable()->after('branch_id');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('reorder_level');
        });
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->dropColumn('warehouse_id');
        });
        Schema::table('sales_returns', function (Blueprint $table) {
            $table->dropColumn('warehouse_id');
        });
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->dropColumn('warehouse_id');
        });
        Schema::table('purchase_returns', function (Blueprint $table) {
            $table->dropColumn('warehouse_id');
        });
    }
};
