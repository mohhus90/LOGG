<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_balances', function (Blueprint $table) {
            $table->decimal('avg_cost', 15, 4)->default(0)->after('quantity');
            $table->decimal('total_value', 15, 4)->default(0)->after('avg_cost');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->decimal('total_cost', 15, 4)->nullable()->after('unit_cost');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->string('costing_method', 20)->default('weighted_average')->after('cost_price');
        });

        // تهيئة أولية: استخدام cost_price الحالي للصنف كمتوسط مبدئي للأرصدة الموجودة بالفعل
        \Illuminate\Support\Facades\DB::statement(
            'UPDATE stock_balances sb JOIN items i ON sb.item_id = i.id
             SET sb.avg_cost = i.cost_price, sb.total_value = sb.quantity * i.cost_price
             WHERE i.cost_price > 0'
        );
    }

    public function down(): void
    {
        Schema::table('stock_balances', function (Blueprint $table) {
            $table->dropColumn(['avg_cost', 'total_value']);
        });
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn('total_cost');
        });
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('costing_method');
        });
    }
};
