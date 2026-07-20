<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * external_sku يخزّن معرّف الـ Variant (ProductVariant_xxx) — فريد لكل صنف.
 * external_product_id يخزّن معرّف المنتج الأب (Product_xxx) — نفس القيمة ممكن تتكرر
 * على أكتر من صنف لو المنتج عنده أكتر من variant، ومطلوب عشان مطابقة بنود الطلبات
 * (Wuilt Orders API بيرجّع معرّف المنتج بس، مش الـ variant المحدد اللي اتشرى).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->string('external_product_id')->nullable()->after('external_sku');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('external_product_id');
        });
    }
};
