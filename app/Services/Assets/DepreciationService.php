<?php
namespace App\Services\Assets;

use App\Models\AssetDepreciationEntry;
use App\Models\FixedAsset;
use App\Services\Accounting\JournalPostingService;
use Illuminate\Support\Facades\DB;

/**
 * إهلاك الأصول الثابتة بطريقة القسط الثابت (Straight-Line). كل فئة أصول تحمل
 * حساباتها المحاسبية الخاصة (مصروف إهلاك/مجمع إهلاك) مباشرة، فلا حاجة لأدوار
 * gl_posting_rules هنا - القيد يُبنى مباشرة من إعدادات الفئة.
 */
class DepreciationService
{
    public static function monthlyAmount(FixedAsset $asset): float
    {
        $depreciableBase = (float) $asset->purchase_cost - (float) $asset->salvage_value;
        $totalMonths = (int) $asset->useful_life_years * 12;
        if ($totalMonths <= 0 || $depreciableBase <= 0) {
            return 0.0;
        }
        return round($depreciableBase / $totalMonths, 4);
    }

    /** يشغّل الإهلاك الشهري لكل الأصول النشطة، ويرحّل قيدًا مجمّعًا لكل فئة أصول */
    public static function runMonthly(int $comCode, int $year, int $month, int $adminId): array
    {
        return DB::transaction(function () use ($comCode, $year, $month, $adminId) {
            $assets = FixedAsset::where('com_code', $comCode)->where('status', 'active')->with('category')->get();
            $byCategory = [];
            $processed  = 0;
            $skipped    = 0;

            foreach ($assets as $asset) {
                $alreadyRun = AssetDepreciationEntry::where('fixed_asset_id', $asset->id)
                    ->where('period_year', $year)->where('period_month', $month)->exists();
                if ($alreadyRun) { $skipped++; continue; }

                $depreciableBase = (float) $asset->purchase_cost - (float) $asset->salvage_value;
                $remaining = $depreciableBase - (float) $asset->accumulated_depreciation;
                $amount = min(self::monthlyAmount($asset), max(0, $remaining));
                if ($amount <= 0) { $skipped++; continue; }

                $newAccum     = (float) $asset->accumulated_depreciation + $amount;
                $newBookValue = (float) $asset->purchase_cost - $newAccum;
                $asset->update([
                    'accumulated_depreciation' => $newAccum,
                    'book_value'               => $newBookValue,
                    'status'                   => $newAccum >= $depreciableBase - 0.0001 ? 'fully_depreciated' : 'active',
                ]);

                AssetDepreciationEntry::create([
                    'com_code'            => $comCode,
                    'fixed_asset_id'      => $asset->id,
                    'period_year'         => $year,
                    'period_month'        => $month,
                    'depreciation_amount' => $amount,
                    'run_at'              => now(),
                    'run_by'              => $adminId,
                ]);

                $catId = $asset->category_id;
                if (!isset($byCategory[$catId])) {
                    $byCategory[$catId] = [
                        'expense_account' => $asset->category->depreciation_expense_gl_account_id,
                        'accum_account'   => $asset->category->accum_depreciation_gl_account_id,
                        'total'           => 0.0,
                    ];
                }
                $byCategory[$catId]['total'] += $amount;
                $processed++;
            }

            foreach ($byCategory as $catId => $data) {
                if ($data['total'] <= 0 || !$data['expense_account'] || !$data['accum_account']) {
                    continue;
                }

                $entry = JournalPostingService::post('asset_depreciation', $comCode, [
                    ['account_id' => $data['expense_account'], 'debit' => $data['total'], 'credit' => 0],
                    ['account_id' => $data['accum_account'], 'debit' => 0, 'credit' => $data['total']],
                ], [
                    'source_module' => 'asset_depreciation_run',
                    'source_id'     => $catId,
                    'entry_date'    => now()->toDateString(),
                    'description'   => 'إهلاك أصول الفئة رقم '.$catId.' - '.$month.'/'.$year,
                    'created_by'    => $adminId,
                ]);

                AssetDepreciationEntry::where('period_year', $year)->where('period_month', $month)
                    ->whereIn('fixed_asset_id', FixedAsset::where('category_id', $catId)->where('com_code', $comCode)->pluck('id'))
                    ->update(['journal_entry_id' => $entry->id]);
            }

            return ['processed' => $processed, 'skipped' => $skipped];
        });
    }
}
