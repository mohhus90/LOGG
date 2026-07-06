<?php
namespace App\Http\Controllers\Admin\Assets;

use App\Http\Controllers\Controller;
use App\Models\FixedAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssetReportsController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function index()
    {
        $comCode = $this->comCode();
        $totals = [
            'count'        => FixedAsset::where('com_code', $comCode)->count(),
            'total_cost'   => FixedAsset::where('com_code', $comCode)->sum('purchase_cost'),
            'total_accum'  => FixedAsset::where('com_code', $comCode)->sum('accumulated_depreciation'),
            'total_book'   => FixedAsset::where('com_code', $comCode)->where('status', '!=', 'disposed')->sum('book_value'),
        ];
        return view('admin.assets.reports.index', compact('totals'));
    }

    public function register(Request $request)
    {
        $query = FixedAsset::with('category')->where('com_code', $this->comCode());
        if ($request->filled('status')) $query->where('status', $request->status);
        $data = $query->orderBy('asset_number')->paginate(30);
        return view('admin.assets.reports.register', compact('data'));
    }

    public function depreciationSchedule($id)
    {
        $asset = FixedAsset::with('category')->where('com_code', $this->comCode())->findOrFail($id);
        $monthlyAmount = \App\Services\Assets\DepreciationService::monthlyAmount($asset);
        $remainingMonths = $monthlyAmount > 0
            ? (int) ceil((($asset->purchase_cost - $asset->salvage_value) - $asset->accumulated_depreciation) / $monthlyAmount)
            : 0;
        return view('admin.assets.reports.depreciation_schedule', compact('asset', 'monthlyAmount', 'remainingMonths'));
    }
}
