<?php
namespace App\Http\Controllers\Admin\Assets;

use App\Http\Controllers\Controller;
use App\Models\AssetDepreciationEntry;
use App\Services\Assets\DepreciationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepreciationRunController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function form()
    {
        return view('admin.assets.depreciation.form');
    }

    public function run(Request $request)
    {
        $request->validate(['year' => 'required|integer|min:2000', 'month' => 'required|integer|between:1,12']);

        $result = DepreciationService::runMonthly($this->comCode(), (int) $request->year, (int) $request->month, Auth::guard('admin')->id());

        return redirect()->route('asset_depreciation.history')
            ->with('success', "تم تشغيل الإهلاك: {$result['processed']} أصل تم إهلاكه، {$result['skipped']} تم تخطيه");
    }

    public function history(Request $request)
    {
        $query = AssetDepreciationEntry::with('fixedAsset')->where('com_code', $this->comCode());
        if ($request->filled('year'))  $query->where('period_year', $request->year);
        if ($request->filled('month')) $query->where('period_month', $request->month);
        $data = $query->orderByDesc('period_year')->orderByDesc('period_month')->paginate(30);
        return view('admin.assets.depreciation.history', compact('data'));
    }
}
