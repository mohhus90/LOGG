<?php
namespace App\Http\Controllers\Admin\Quality;

use App\Http\Controllers\Controller;
use App\Models\QualityInspection;
use Illuminate\Support\Facades\Auth;

class QualityReportsController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function index()
    {
        $comCode = $this->comCode();
        $total = QualityInspection::where('com_code', $comCode)->count();
        $stats = [
            'total'       => $total,
            'pass'        => QualityInspection::where('com_code', $comCode)->where('overall_result', 'pass')->count(),
            'fail'        => QualityInspection::where('com_code', $comCode)->where('overall_result', 'fail')->count(),
            'conditional' => QualityInspection::where('com_code', $comCode)->where('overall_result', 'conditional')->count(),
        ];
        $stats['pass_rate'] = $total > 0 ? round($stats['pass'] / $total * 100, 1) : 0;

        $recent = QualityInspection::with('checklist')->where('com_code', $comCode)->orderByDesc('id')->limit(10)->get();

        return view('admin.quality.reports.index', compact('stats', 'recent'));
    }
}
