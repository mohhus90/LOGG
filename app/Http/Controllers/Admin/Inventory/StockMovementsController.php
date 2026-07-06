<?php
namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockMovementsController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function index(Request $request)
    {
        $query = StockMovement::with(['warehouse', 'item'])->where('com_code', $this->comCode());
        if ($request->filled('warehouse_id')) $query->where('warehouse_id', $request->warehouse_id);
        if ($request->filled('item_id'))      $query->where('item_id', $request->item_id);
        if ($request->filled('movement_type')) $query->where('movement_type', $request->movement_type);
        if ($request->filled('from'))         $query->whereDate('date', '>=', $request->from);
        if ($request->filled('to'))           $query->whereDate('date', '<=', $request->to);

        $data       = $query->orderByDesc('date')->orderByDesc('id')->paginate(30);
        $warehouses = Warehouse::where('com_code', $this->comCode())->orderBy('name')->get();
        $items      = Item::where('com_code', $this->comCode())->orderBy('name')->get();

        return view('admin.inventory.movements.index', compact('data', 'warehouses', 'items'));
    }
}
