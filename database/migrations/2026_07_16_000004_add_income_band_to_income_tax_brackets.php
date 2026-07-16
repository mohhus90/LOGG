<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // القانون المصري (91/2005 المعدّل بالقانون 7/2024، المادة 8) لا يطبّق شرائح
    // تصاعدية ثابتة فقط، بل "مسار شرائح" مختلف يتم اختياره حسب إجمالي الدخل
    // السنوي نفسه (حتى 600 ألف / 600-700 / 700-800 / 800-900 / 900-1.2 مليون /
    // أكثر من 1.2 مليون) — فكلما ارتفع إجمالي الدخل، تختفي الشرائح الأدنى
    // (الإعفاء صفر% ثم 10%) وتُدمج ضمن الشريحة الأعلى. هذان العمودان يحددان
    // مدى إجمالي الدخل السنوي الذي تُطبَّق ضمنه هذه الشريحة.
    public function up(): void
    {
        Schema::table('income_tax_brackets', function (Blueprint $table) {
            if (!Schema::hasColumn('income_tax_brackets', 'income_band_min')) {
                $table->decimal('income_band_min', 12, 2)->nullable()->after('is_active')
                      ->comment('أقل إجمالي دخل سنوي (حصري) لتطبيق هذا المسار — فارغ = من صفر');
            }
            if (!Schema::hasColumn('income_tax_brackets', 'income_band_max')) {
                $table->decimal('income_band_max', 12, 2)->nullable()->after('income_band_min')
                      ->comment('أعلى إجمالي دخل سنوي (شامل) لتطبيق هذا المسار — فارغ = بلا حد أعلى');
            }
        });
    }

    public function down(): void
    {
        Schema::table('income_tax_brackets', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('income_tax_brackets', 'income_band_min') ? 'income_band_min' : null,
                Schema::hasColumn('income_tax_brackets', 'income_band_max') ? 'income_band_max' : null,
            ]));
        });
    }
};
