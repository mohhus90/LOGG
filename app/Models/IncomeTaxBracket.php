<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncomeTaxBracket extends Model
{
    protected $table = 'income_tax_brackets';
    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean'];

    /**
     * يحسب ضريبة كسب العمل التصاعدية على وعاء شهري معين، بتوزيع الوعاء على
     * الشرائح المرتبة تصاعديًا (كل شريحة تُحمَّل بنسبتها فقط على الجزء الواقع
     * داخلها، وليس على كامل الوعاء).
     */
    public static function calcTax(int $comCode, float $taxableBase): float
    {
        if ($taxableBase <= 0) return 0.0;

        $brackets = self::where('com_code', $comCode)->where('is_active', true)
            ->orderBy('from_amount')->get();

        $tax = 0.0;
        foreach ($brackets as $bracket) {
            if ($taxableBase <= $bracket->from_amount) continue;

            $upperBound = $bracket->to_amount !== null ? (float) $bracket->to_amount : $taxableBase;
            $sliceTop   = min($taxableBase, $upperBound);
            $sliceAmount = $sliceTop - (float) $bracket->from_amount;

            if ($sliceAmount > 0) {
                $tax += $sliceAmount * ((float) $bracket->rate / 100);
            }
        }

        return round($tax, 2);
    }
}
