<?php
namespace App\Http\Controllers\Admin\Manufacturing;

use App\Http\Controllers\Controller;
use App\Models\ProductionOrder;
use Illuminate\Support\Facades\Auth;

class ManufacturingReportsController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function index()
    {
        $comCode = $this->comCode();
        $stats = [
            'draft'       => ProductionOrder::where('com_code', $comCode)->where('status', 'draft')->count(),
            'in_progress' => ProductionOrder::where('com_code', $comCode)->where('status', 'in_progress')->count(),
            'completed'   => ProductionOrder::where('com_code', $comCode)->where('status', 'completed')->count(),
            'total_cost'  => ProductionOrder::where('com_code', $comCode)->where('status', 'completed')->sum('total_cost'),
        ];
        $recent = ProductionOrder::with('item')->where('com_code', $comCode)->orderByDesc('id')->limit(10)->get();
        return view('admin.manufacturing.reports.index', compact('stats', 'recent'));
    }

    public function costSummary()
    {
        $data = ProductionOrder::with('item')->where('com_code', $this->comCode())->orderByDesc('id')->paginate(30);
        return view('admin.manufacturing.reports.cost_summary', compact('data'));
    }
}
