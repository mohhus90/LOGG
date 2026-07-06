<?php
namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Warehouse;
use App\Models\StockBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockLevelsController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function index(Request $request)
    {
        $cc = $this->comCode();
        $query = Item::where('items.com_code', $cc)
            ->leftJoin('stock_balances', function ($join) use ($request) {
                $join->on('stock_balances.item_id', '=', 'items.id');
                if ($request->filled('warehouse_id')) {
                    $join->where('stock_balances.warehouse_id', $request->warehouse_id);
                }
            })
            ->select('items.*')
            ->selectRaw('COALESCE(SUM(stock_balances.quantity), 0) as total_stock')
            ->groupBy('items.id');

        if ($request->filled('search')) {
            $query->where('items.name', 'like', '%'.$request->search.'%');
        }

        if ($request->boolean('low_stock')) {
            $query->havingRaw('COALESCE(SUM(stock_balances.quantity), 0) <= items.reorder_level');
        }

        $data = $query->orderBy('items.name')->paginate(25);

        $warehouses = Warehouse::where('com_code', $cc)->where('is_active', true)->orderBy('name')->get();
        return view('admin.inventory.stock.index', compact('data', 'warehouses'));
    }

    public function show($itemId)
    {
        $item = Item::where('com_code', $this->comCode())->with(['category', 'unit'])->findOrFail($itemId);
        $balances = StockBalance::where('item_id', $itemId)->with('warehouse')->get();
        $movements = \App\Models\StockMovement::where('item_id', $itemId)
            ->with('warehouse')
            ->orderByDesc('date')->orderByDesc('id')
            ->limit(30)->get();
        return view('admin.inventory.stock.show', compact('item', 'balances', 'movements'));
    }
}
