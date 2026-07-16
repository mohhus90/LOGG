<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncomeTaxBracket extends Model
{
    protected $table = 'income_tax_brackets';
    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean'];

    /**
     * يحسب ضريبة كسب العمل على إجمالي دخل سنوي معين (القانون 91/2005 المعدّل
     * بالقانون 7/2024، المادة 8). القانون لا يطبّق شرائح تصاعدية ثابتة فقط،
     * بل "مسار شرائح" يُختار حسب إجمالي الدخل السنوي نفسه: كلما ارتفع
     * إجمالي الدخل تختفي الشرائح الأدنى (0% ثم 10%) وتُدمج ضمن الشريحة
     * الأعلى، بدلاً من إعفاء كل دافع ضرائب من أول 40 ألف جنيه دائمًا.
     * لذلك أول خطوة هنا هي تحديد "المسار" (income_band_min/max) الذي يقع
     * فيه إجمالي الدخل، ثم توزيع الوعاء على شرائح هذا المسار فقط تصاعديًا.
     */
    public static function calcTax(int $comCode, float $annualTaxableBase): float
    {
        if ($annualTaxableBase <= 0) return 0.0;

        $brackets = self::where('com_code', $comCode)->where('is_active', true)
            ->where(function ($q) use ($annualTaxableBase) {
                $q->whereNull('income_band_min')->orWhere('income_band_min', '<', $annualTaxableBase);
            })
            ->where(function ($q) use ($annualTaxableBase) {
                $q->whereNull('income_band_max')->orWhere('income_band_max', '>=', $annualTaxableBase);
            })
            ->orderBy('from_amount')->get();

        $tax = 0.0;
        foreach ($brackets as $bracket) {
            if ($annualTaxableBase <= $bracket->from_amount) continue;

            $upperBound = $bracket->to_amount !== null ? (float) $bracket->to_amount : $annualTaxableBase;
            $sliceTop   = min($annualTaxableBase, $upperBound);
            $sliceAmount = $sliceTop - (float) $bracket->from_amount;

            if ($sliceAmount > 0) {
                $tax += $sliceAmount * ((float) $bracket->rate / 100);
            }
        }

        return round($tax, 2);
    }
}
