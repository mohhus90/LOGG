<?php
namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\EcommerceStore;
use App\Services\WuiltOrderSyncService;
use App\Services\WuiltProductSyncService;
use App\Services\WuiltWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EcommerceStoresController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function index()
    {
        $stores = EcommerceStore::where('com_code', $this->comCode())->orderByDesc('id')->get();
        $walletAccountId = ChartOfAccount::where('com_code', $this->comCode())->where('account_code', '1103')->value('id');
        return view('admin.sales.ecommerce_stores.index', compact('stores', 'walletAccountId'));
    }

    public function create()
    {
        return view('admin.sales.ecommerce_stores.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'nullable|string|max:255',
            'store_id' => 'required|string|max:255',
            'api_key'  => 'required|string',
        ]);

        EcommerceStore::create([
            'com_code' => $this->comCode(),
            'provider' => 'wuilt',
            'name'     => $request->name,
            'store_id' => trim($request->store_id),
            'api_key'  => trim($request->api_key),
            'is_active'=> true,
        ]);

        return redirect()->route('sales_ecommerce_stores.index')->with('success', 'تم إضافة المتجر بنجاح');
    }

    public function edit($id)
    {
        $store = EcommerceStore::where('com_code', $this->comCode())->findOrFail($id);
        return view('admin.sales.ecommerce_stores.edit', compact('store'));
    }

    public function update(Request $request, $id)
    {
        $store = EcommerceStore::where('com_code', $this->comCode())->findOrFail($id);

        $request->validate([
            'name'     => 'nullable|string|max:255',
            'store_id' => 'required|string|max:255',
            'api_key'  => 'nullable|string',
        ]);

        $data = [
            'name'                  => $request->name,
            'store_id'              => trim($request->store_id),
            'is_active'             => $request->boolean('is_active'),
            'sync_interval_minutes' => (int) ($request->sync_interval_minutes ?? $store->sync_interval_minutes),
        ];

        if ($request->filled('api_key')) {
            $data['api_key'] = trim($request->api_key);
        }

        $store->update($data);

        return redirect()->route('sales_ecommerce_stores.index')->with('success', 'تم تعديل بيانات المتجر');
    }

    public function delete($id)
    {
        EcommerceStore::where('com_code', $this->comCode())->findOrFail($id)->delete();
        return redirect()->route('sales_ecommerce_stores.index')->with('success', 'تم حذف المتجر');
    }

    public function syncNow($id, WuiltOrderSyncService $service, WuiltProductSyncService $productService, WuiltWalletService $walletService)
    {
        $store = EcommerceStore::where('com_code', $this->comCode())->findOrFail($id);

        $productMsg = '';
        try {
            $productStats = $productService->sync($store);
            $productMsg = "المنتجات: {$productStats['created']} جديد، {$productStats['linked']} مربوط، {$productStats['updated']} محدّث | ";
        } catch (\Throwable $e) {
            $productMsg = 'فشلت مزامنة المنتجات: ' . $e->getMessage() . ' | ';
        }

        $stats = $service->sync($store);

        $walletMsg = '';
        try {
            $wallet = $walletService->syncBalance($store);
            $walletMsg = " | رصيد المحفظة: {$wallet['balance']} ج.م";
        } catch (\Throwable $e) {
            $walletMsg = ' | فشلت مزامنة المحفظة: ' . $e->getMessage();
        }

        if ($stats['errors'] > 0 && ($stats['created'] + $stats['updated']) === 0) {
            return back()->with('error', $productMsg . 'فشلت المزامنة: ' . implode(' | ', $stats['error_details']) . $walletMsg);
        }

        $msg = $productMsg . "الطلبات: {$stats['created']} جديد، {$stats['updated']} محدّث" . $walletMsg;
        if ($stats['errors'] > 0) {
            $msg .= " ({$stats['errors']} خطأ — راجع السجل)";
            return back()->with('warning', $msg);
        }

        return back()->with('success', $msg);
    }
}
