<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EtaCredential;
use App\Models\EtaInvoice;
use App\Services\EtaService;
use App\Exports\EtaInvoicesExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class TaxController extends Controller
{
    private function comCode(): int
    {
        return (int) Auth::guard('admin')->user()->com_code;
    }

    private function credential(): ?EtaCredential
    {
        return EtaCredential::where('com_code', $this->comCode())->first();
    }

    // ─────────────────────────────────────────────
    //  Dashboard
    // ─────────────────────────────────────────────

    public function index()
    {
        $credential = $this->credential();

        $salesTotal    = EtaInvoice::where('com_code', $this->comCode())
                            ->where('direction', 'Sent')->where('status', 'Valid')
                            ->sum('total_amount');
        $purchaseTotal = EtaInvoice::where('com_code', $this->comCode())
                            ->where('direction', 'Received')->where('status', 'Valid')
                            ->sum('total_amount');
        $salesVat      = EtaInvoice::where('com_code', $this->comCode())
                            ->where('direction', 'Sent')->where('status', 'Valid')
                            ->sum('total_vat');
        $purchaseVat   = EtaInvoice::where('com_code', $this->comCode())
                            ->where('direction', 'Received')->where('status', 'Valid')
                            ->sum('total_vat');

        $recentSales    = EtaInvoice::where('com_code', $this->comCode())
                            ->where('direction', 'Sent')
                            ->latest('date_issued')->limit(5)->get();
        $recentPurchases = EtaInvoice::where('com_code', $this->comCode())
                            ->where('direction', 'Received')
                            ->latest('date_issued')->limit(5)->get();

        $unpostedCount = EtaInvoice::where('com_code', $this->comCode())
                            ->where('status', 'Valid')
                            ->where('is_posted', false)->count();

        return view('admin.tax.index', compact(
            'credential', 'salesTotal', 'purchaseTotal',
            'salesVat', 'purchaseVat', 'recentSales', 'recentPurchases', 'unpostedCount'
        ));
    }

    // ─────────────────────────────────────────────
    //  Credentials
    // ─────────────────────────────────────────────

    public function credentials()
    {
        $credential = $this->credential();
        return view('admin.tax.credentials', compact('credential'));
    }

    public function saveCredentials(Request $request)
    {
        $existing = $this->credential();

        $request->validate([
            'auth_type'     => 'required|in:portal,api',
            'client_id'     => 'required|string',
            'client_secret' => $existing ? 'nullable|string' : 'required|string',
            'taxpayer_id'   => 'nullable|string',
            'taxpayer_name' => 'nullable|string',
        ]);

        $data = [
            'auth_type'    => $request->auth_type,
            'client_id'    => trim($request->client_id),
            'taxpayer_id'  => $request->taxpayer_id,
            'taxpayer_name' => $request->taxpayer_name,
            'is_active'    => true,
        ];

        // حدّث الـ secret/password فقط إذا أدخل المستخدم قيمة جديدة
        if ($request->filled('client_secret')) {
            $data['client_secret']    = trim($request->client_secret);
            $data['access_token']     = null;
            $data['token_expires_at'] = null;
        }

        EtaCredential::updateOrCreate(['com_code' => $this->comCode()], $data);

        return redirect()->route('tax.credentials')
            ->with('success', 'تم حفظ بيانات الاعتماد بنجاح');
    }

    // ─────────────────────────────────────────────
    //  Invoices List
    // ─────────────────────────────────────────────

    public function invoices(Request $request)
    {
        $direction = $request->get('direction', 'Sent');

        $query = EtaInvoice::where('com_code', $this->comCode())
                    ->where('direction', $direction);

        if ($request->filled('status'))    $query->where('status', $request->status);
        if ($request->filled('doc_type'))  $query->where('document_type', $request->doc_type);
        if ($request->filled('is_posted')) $query->where('is_posted', (bool) $request->is_posted);
        if ($request->filled('from'))      $query->whereDate('date_issued', '>=', $request->from);
        if ($request->filled('to'))        $query->whereDate('date_issued', '<=', $request->to);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('issuer_name', 'like', "%$s%")
                  ->orWhere('receiver_name', 'like', "%$s%")
                  ->orWhere('internal_id', 'like', "%$s%")
                  ->orWhere('uuid', 'like', "%$s%");
            });
        }

        $totals = (clone $query)->selectRaw('
            SUM(total_sales) as total_sales,
            SUM(total_discount) as total_discount,
            SUM(net_amount) as net_amount,
            SUM(total_vat) as total_vat,
            SUM(total_amount) as total_amount
        ')->first();

        $data = $query->orderByDesc('date_issued')->paginate(paginate_counter);

        return view('admin.tax.invoices', compact('data', 'direction', 'totals'));
    }

    // ─────────────────────────────────────────────
    //  Invoice Detail
    // ─────────────────────────────────────────────

    public function show(int $id)
    {
        $invoice = EtaInvoice::where('com_code', $this->comCode())
                    ->with('items')
                    ->findOrFail($id);

        // إذا لا توجد بنود نجلب التفاصيل من ETA
        if ($invoice->items->isEmpty() && $this->credential()) {
            $service = new EtaService($this->credential());
            $service->fetchInvoiceDetails($invoice);
            $invoice->load('items');
        }

        return view('admin.tax.show', compact('invoice'));
    }

    // ─────────────────────────────────────────────
    //  Test Connection
    // ─────────────────────────────────────────────

    public function testConnection()
    {
        // تأكد أن الاستجابة دائماً JSON حتى عند الأخطاء غير المتوقعة
        try {
            return $this->runTestConnection();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('testConnection unexpected: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'خطأ داخلي: ' . $e->getMessage(),
            ]);
        }
    }

    private function runTestConnection(): \Illuminate\Http\JsonResponse
    {
        $credential = $this->credential();
        if (!$credential) {
            return response()->json(['success' => false, 'message' => 'لا توجد بيانات اعتماد محفوظة']);
        }

        // الخطوة 1: تحقق من قدرات سيرفر ETA
        $service = new EtaService($credential);
        $caps    = $service->fetchServerCapabilities();

        // الخطوة 2: احصل على التوكن
        try {
            $credential->update(['access_token' => null, 'token_expires_at' => null]);
            $credential->refresh();
            $service = new EtaService($credential);
            $service->getAccessToken();
        } catch (\Throwable $e) {
            $grantTypes = implode(', ', (array)($caps['grant_types_supported'] ?? []));
            return response()->json([
                'success'     => false,
                'step'        => 'token',
                'message'     => nl2br(e($e->getMessage())),
                'grant_types' => $grantTypes,
            ]);
        }

        // الخطوة 3: اختبر الوصول للـ API
        $apiTest = $service->testApiAccess();

        if ($apiTest['success']) {
            return response()->json([
                'success' => true,
                'message' => "✔ المصادقة نجحت<br>✔ الـ API يستجيب (كود 200)<br>✔ إجمالي الفواتير المتاحة: <strong>{$apiTest['total']}</strong>",
                'api_url' => $apiTest['api_url'],
            ]);
        }

        // المصادقة نجحت لكن الـ API فشل
        $httpCode = (int) ($apiTest['http_code'] ?? 0);
        $rawBody  = (string) ($apiTest['raw_body'] ?? '');
        $decoded  = is_array(json_decode($rawBody, true)) ? json_decode($rawBody, true) : [];

        $apiHint = match(true) {
            $httpCode === 401 => '❌ التوكن غير مقبول (401) — الـ client لا يملك صلاحية قراءة الفواتير',
            $httpCode === 403 => '❌ ممنوع الوصول (403) — فعّل صلاحية قراءة الفواتير من إعدادات ERP في البوابة',
            $httpCode === 404 => '❌ الـ endpoint غير موجود (404)',
            $httpCode === 0   => '❌ انتهت مهلة الاتصال أو السيرفر غير متاح',
            default           => "❌ كود HTTP: {$httpCode}",
        };

        $toCtrlStr = static fn ($v): string => is_array($v)
            ? json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : (string) ($v ?? '');

        $rawVal = $decoded['message'] ?? $decoded['error'] ?? null;
        $rawMsg = $rawVal !== null ? $toCtrlStr($rawVal) : $rawBody;

        return response()->json([
            'success'   => false,
            'step'      => 'api',
            'message'   => $apiHint . ($rawMsg ? "<br><small>{$rawMsg}</small>" : ''),
            'api_url'   => (string) ($apiTest['api_url'] ?? ''),
            'http_code' => $httpCode,
        ]);
    }

    // ─────────────────────────────────────────────
    //  Sync (سحب الفواتير من ETA)
    // ─────────────────────────────────────────────

    public function syncForm()
    {
        $credential = $this->credential();
        if (!$credential) {
            return redirect()->route('tax.credentials')
                ->with('error', 'يجب إعداد بيانات الاعتماد أولاً');
        }
        return view('admin.tax.sync', compact('credential'));
    }

    public function sync(Request $request)
    {
        $request->validate([
            'direction' => 'required|in:Sent,Received,Both',
            'from'      => 'required|date',
            'to'        => 'required|date|after_or_equal:from',
            'date_type' => 'nullable|in:issue,submission',
        ]);

        $credential = $this->credential();
        if (!$credential) {
            return back()->with('error', 'بيانات الاعتماد غير موجودة');
        }

        $service      = new EtaService($credential);
        $allStats     = ['new' => 0, 'updated' => 0, 'errors' => 0, 'error_details' => []];

        try {
            $directions = $request->direction === 'Both'
                ? ['Sent', 'Received']
                : [$request->direction];

            foreach ($directions as $dir) {
                $stats = $service->syncInvoices($dir, $request->from, $request->to, $request->date_type ?? 'issue');
                $allStats['new']     += $stats['new'];
                $allStats['updated'] += $stats['updated'];
                $allStats['errors']  += $stats['errors'];
                $allStats['error_details'] = array_merge(
                    $allStats['error_details'],
                    $stats['error_details'] ?? []
                );
            }

            if ($allStats['errors'] === 0) {
                $msg = "تم السحب بنجاح: {$allStats['new']} جديدة، {$allStats['updated']} محدّثة";
                return redirect()->route('tax.index')->with('success', $msg);
            }

            // نجاح جزئي أو فشل كامل
            $errorText = implode("\n", $allStats['error_details']);
            $msg = "تم السحب: {$allStats['new']} جديدة، {$allStats['updated']} محدّثة\n"
                 . "الأخطاء ({$allStats['errors']}):\n{$errorText}";

            return back()->with(
                $allStats['new'] + $allStats['updated'] > 0 ? 'warning' : 'error',
                $msg
            );

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────
    //  Accounting — ترحيل محاسبي
    // ─────────────────────────────────────────────

    public function postInvoice(int $id)
    {
        $invoice = EtaInvoice::where('com_code', $this->comCode())->findOrFail($id);

        if ($invoice->is_posted) {
            return back()->with('error', 'هذه الفاتورة مرحّلة بالفعل');
        }

        $invoice->update([
            'is_posted'  => true,
            'posted_at'  => now(),
            'posted_by'  => Auth::guard('admin')->id(),
        ]);

        return back()->with('success', 'تم الترحيل المحاسبي للفاتورة بنجاح');
    }

    public function postBulk(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);

        $count = EtaInvoice::where('com_code', $this->comCode())
            ->whereIn('id', $request->ids)
            ->where('is_posted', false)
            ->update([
                'is_posted' => true,
                'posted_at' => now(),
                'posted_by' => Auth::guard('admin')->id(),
            ]);

        return response()->json(['success' => true, 'count' => $count]);
    }

    public function unpostInvoice(int $id)
    {
        $invoice = EtaInvoice::where('com_code', $this->comCode())->findOrFail($id);

        $invoice->update([
            'is_posted'      => false,
            'posted_at'      => null,
            'posted_by'      => null,
            'posting_notes'  => null,
        ]);

        return back()->with('success', 'تم إلغاء الترحيل');
    }

    // ─────────────────────────────────────────────
    //  VAT Report (الإقرار الضريبي)
    // ─────────────────────────────────────────────

    public function vatReport(Request $request)
    {
        $from  = $request->get('from', now()->startOfMonth()->format('Y-m-d'));
        $to    = $request->get('to',   now()->endOfMonth()->format('Y-m-d'));

        $sales = EtaInvoice::where('com_code', $this->comCode())
            ->where('direction', 'Sent')
            ->where('status', 'Valid')
            ->whereDate('date_issued', '>=', $from)
            ->whereDate('date_issued', '<=', $to)
            ->selectRaw('
                COUNT(*) as count,
                SUM(net_amount) as net_amount,
                SUM(total_vat) as total_vat,
                SUM(total_amount) as total_amount
            ')->first();

        $purchases = EtaInvoice::where('com_code', $this->comCode())
            ->where('direction', 'Received')
            ->where('status', 'Valid')
            ->whereDate('date_issued', '>=', $from)
            ->whereDate('date_issued', '<=', $to)
            ->selectRaw('
                COUNT(*) as count,
                SUM(net_amount) as net_amount,
                SUM(total_vat) as total_vat,
                SUM(total_amount) as total_amount
            ')->first();

        $netVat = ($sales->total_vat ?? 0) - ($purchases->total_vat ?? 0);

        return view('admin.tax.vat_report', compact('sales', 'purchases', 'netVat', 'from', 'to'));
    }

    // ─────────────────────────────────────────────
    //  Excel Export
    // ─────────────────────────────────────────────

    public function export(Request $request)
    {
        $direction = $request->get('direction', 'Sent');
        $filters   = $request->only(['direction', 'status', 'doc_type', 'is_posted', 'from', 'to', 'search']);
        $label     = $direction === 'Sent' ? 'مبيعات' : 'مشتريات';

        return Excel::download(
            new EtaInvoicesExport($filters, $this->comCode()),
            "فواتير_{$label}_" . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
