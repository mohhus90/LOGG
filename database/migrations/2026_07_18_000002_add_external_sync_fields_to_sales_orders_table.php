<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->string('source')->nullable()->after('quotation_id');
            $table->string('external_order_id')->nullable()->after('source');
            $table->string('external_status')->nullable()->after('external_order_id');
            $table->timestamp('synced_at')->nullable()->after('external_status');
            $table->boolean('needs_item_mapping')->default(false)->after('synced_at');

            $table->string('shipping_company')->nullable()->after('delivery_address');
            $table->string('waybill_number')->nullable()->after('shipping_company');
            $table->enum('cod_status', ['none', 'pending', 'collected', 'returned'])->default('none')->after('waybill_number');
            $table->decimal('cod_amount', 15, 2)->nullable()->after('cod_status');
            $table->timestamp('cod_collected_at')->nullable()->after('cod_amount');

            $table->index(['com_code', 'source', 'external_order_id'], 'sales_orders_external_ref_index');
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropIndex('sales_orders_external_ref_index');
            $table->dropColumn([
                'source', 'external_order_id', 'external_status', 'synced_at', 'needs_item_mapping',
                'shipping_company', 'waybill_number', 'cod_status', 'cod_amount', 'cod_collected_at',
            ]);
        });
    }
};
