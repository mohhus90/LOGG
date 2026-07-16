<?php
namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\{Item, Warehouse, StockMovement};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryReportsController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function index()
    {
        $cc = $this->comCode();

        $stockValue = DB::table('stock_balances as sb')
            ->where('sb.com_code', $cc)
            ->sum('sb.total_value');

        $stats = [
            'total_stock_value' => $stockValue ?? 0,
            'warehouses_count'  => Warehouse::where('com_code', $cc)->where('is_active', true)->count(),
            'items_count'       => Item::where('com_code', $cc)->where('is_active', true)->count(),
            'low_stock_count'   => DB::table('items as i')
                ->leftJoin('stock_balances as sb', 'sb.item_id', '=', 'i.id')
                ->where('i.com_code', $cc)
                ->groupBy('i.id', 'i.reorder_level')
                ->havingRaw('COALESCE(SUM(sb.quantity), 0) <= i.reorder_level')
                ->select('i.id')
                ->get()->count(),
            'movements_this_month' => StockMovement::where('com_code', $cc)
                ->whereMonth('date', now()->month)->whereYear('date', now()->year)->count(),
        ];

        $lowStockItems = DB::table('items as i')
            ->leftJoin('stock_balances as sb', 'sb.item_id', '=', 'i.id')
            ->where('i.com_code', $cc)
            ->groupBy('i.id', 'i.name', 'i.code', 'i.reorder_level')
            ->havingRaw('COALESCE(SUM(sb.quantity), 0) <= i.reorder_level')
            ->selectRaw('i.id, i.name, i.code, i.reorder_level, COALESCE(SUM(sb.quantity), 0) as total_stock')
            ->limit(10)->get();

        return view('admin.inventory.reports.index', compact('stats', 'lowStockItems'));
    }

    public function valuation(Request $request)
    {
        $query = DB::table('stock_balances as sb')
            ->join('items as i', 'sb.item_id', '=', 'i.id')
            ->join('warehouses as w', 'sb.warehouse_id', '=', 'w.id')
            ->where('sb.com_code', $this->comCode())
            ->where('sb.quantity', '>', 0)
            ->select('i.id', 'i.name', 'i.code', 'w.name as warehouse_name', 'sb.quantity')
            ->selectRaw('sb.avg_cost as cost_price, sb.total_value as value');

        if ($request->filled('warehouse_id')) $query->where('sb.warehouse_id', $request->warehouse_id);

        $totalValue = (clone $query)->get()->sum('value');
        $data       = $query->orderByDesc('value')->paginate(30);
        $warehouses = Warehouse::where('com_code', $this->comCode())->orderBy('name')->get();

        return view('admin.inventory.reports.valuation', compact('data', 'warehouses', 'totalValue'));
    }

    public function lowStock(Request $request)
    {
        $query = DB::table('items as i')
            ->leftJoin('stock_balances as sb', 'sb.item_id', '=', 'i.id')
            ->where('i.com_code', $this->comCode())
            ->groupBy('i.id', 'i.name', 'i.code', 'i.reorder_level')
            ->havingRaw('COALESCE(SUM(sb.quantity), 0) <= i.reorder_level')
            ->selectRaw('i.id, i.name, i.code, i.reorder_level, COALESCE(SUM(sb.quantity), 0) as total_stock');

        $data = $query->paginate(30);
        return view('admin.inventory.reports.low_stock', compact('data'));
    }

    public function movementsSummary(Request $request)
    {
        $query = StockMovement::where('com_code', $this->comCode());
        if ($request->filled('from')) $query->whereDate('date', '>=', $request->from);
        if ($request->filled('to'))   $query->whereDate('date', '<=', $request->to);

        $summary = (clone $query)
            ->selectRaw('movement_type, COUNT(*) as cnt, SUM(quantity) as total_qty')
            ->groupBy('movement_type')->get();

        return view('admin.inventory.reports.movements_summary', compact('summary'));
    }
}
