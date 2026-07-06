<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EtaCredential;
use App\Models\EtaFreeZone;
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

        // إذا لا توجد بنود نحاول جلب التفاصيل تلقائياً من ETA
        if ($invoice->items->isEmpty() && $this->credential()) {
            try {
                $service = new EtaService($this->credential());
                $service->fetchInvoiceDetails($invoice);
                $invoice->load('items');
            } catch (\Exception) {
                // نكمل عرض الصفحة بدون بنود إذا فشل الجلب التلقائي
            }
        }

        $linkedInvoice = $invoice->linkedInvoice();
        $suggestedMatches = $linkedInvoice ? collect() : $invoice->suggestedMatches();

        return view('admin.tax.show', compact('invoice', 'linkedInvoice', 'suggestedMatches'));
    }

    // ─────────────────────────────────────────────
    //  Fetch Invoice Details from ETA
    // ─────────────────────────────────────────────

    public function fetchDetails(int $id)
    {
        $invoice    = EtaInvoice::where('com_code', $this->comCode())->findOrFail($id);
        $credential = $this->credential();

        if (!$credential) {
            return back()->with('error', 'بيانات الاعتماد غير موجودة — يرجى إعداد بيانات ETA أولاً');
        }

        try {
            $service = new EtaService($credential);
            $service->fetchInvoiceDetails($invoice);
            return back()->with('success', 'تم سحب تفاصيل الفاتورة من ETA بنجاح');
        } catch (\Exception $e) {
            return back()->with('error', 'فشل سحب التفاصيل: ' . $e->getMessage());
        }
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
            'direction'     => 'required|in:Sent,Received,Both',
            'from'          => 'required|date',
            'to'            => 'required|date|after_or_equal:from',
            'date_type'     => 'nullable|in:issue,submission',
            'fetch_details' => 'nullable|boolean',
        ]);

        $credential = $this->credential();
        if (!$credential) {
            return back()->with('error', 'بيانات الاعتماد غير موجودة');
        }

        $service    = new EtaService($credential);
        $allStats   = ['new' => 0, 'updated' => 0, 'errors' => 0, 'error_details' => []];
        $directions = $request->direction === 'Both'
            ? ['Sent', 'Received']
            : [$request->direction];

        try {
            foreach ($directions as $dir) {
                $stats = $service->syncInvoices($dir, $request->from, $request->to, $request->date_type ?? 'issue');
                $allStats['new']          += $stats['new'];
                $allStats['updated']      += $stats['updated'];
                $allStats['errors']       += $stats['errors'];
                $allStats['error_details'] = array_merge(
                    $allStats['error_details'],
                    $stats['error_details'] ?? []
                );
            }
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        // ── سحب التفاصيل (اختياري) ──────────────────────────────
        $detailOk = $detailFail = 0;
        if ($request->boolean('fetch_details') && ($allStats['new'] + $allStats['updated']) > 0) {
            $invoices = EtaInvoice::where('com_code', $this->comCode())
                ->whereIn('direction', $directions)
                ->whereBetween('date_issued', [$request->from, $request->to . ' 23:59:59'])
                ->get();

            foreach ($invoices as $invoice) {
                try {
                    $service->fetchInvoiceDetails($invoice);
                    $detailOk++;
                } catch (\Exception $e) {
                    $detailFail++;
                }
            }
        }

        // ── بناء رسالة النتيجة ───────────────────────────────────
        $msg = "تم السحب: {$allStats['new']} جديدة، {$allStats['updated']} محدّثة";

        if ($detailOk + $detailFail > 0) {
            $msg .= " | التفاصيل: {$detailOk} ناجحة";
            if ($detailFail > 0) {
                $msg .= "، {$detailFail} فشلت (محفوظة بدون أصناف)";
            }
        }

        if ($allStats['errors'] > 0) {
            $errorText = implode("\n", $allStats['error_details']);
            $msg .= "\nالأخطاء ({$allStats['errors']}):\n{$errorText}";
            $type = $allStats['new'] + $allStats['updated'] > 0 ? 'warning' : 'error';
            return back()->with($type, $msg);
        }

        return redirect()->route('tax.index')->with('success', $msg);
    }

    // ─────────────────────────────────────────────
    //  ربط الفاتورة الإلكترونية بفاتورة داخلية (Phase 7)
    // ─────────────────────────────────────────────

    /**
     * لا يوجد مفتاح مطابقة مضمون بين ETA ونظامنا الداخلي (internal_id حر النص)،
     * فالربط هنا يدوي يختاره المستخدم من مرشحات مقترحة بالمبلغ والتاريخ.
     */
    public function linkInvoice(Request $request, int $id)
    {
        $invoice = EtaInvoice::where('com_code', $this->comCode())->findOrFail($id);
        $request->validate(['linked_invoice_id' => 'required|integer']);

        if ($invoice->direction === 'Sent') {
            $target = \App\Models\SalesInvoice::where('com_code', $this->comCode())->findOrFail($request->linked_invoice_id);
            $invoice->update(['sales_invoice_id' => $target->id, 'purchase_invoice_id' => null]);
        } else {
            $target = \App\Models\PurchaseInvoice::where('com_code', $this->comCode())->findOrFail($request->linked_invoice_id);
            $invoice->update(['purchase_invoice_id' => $target->id, 'sales_invoice_id' => null]);
        }

        return back()->with('success', 'تم ربط الفاتورة بالسجل الداخلي');
    }

    public function unlinkInvoice(int $id)
    {
        $invoice = EtaInvoice::where('com_code', $this->comCode())->findOrFail($id);
        $invoice->update(['sales_invoice_id' => null, 'purchase_invoice_id' => null]);
        return back()->with('success', 'تم إلغاء ربط الفاتورة');
    }

    // ─────────────────────────────────────────────
    //  تأكيد المطابقة المحاسبية — ETA طبقة امتثال فوق القيد الفعلي
    //  (القيد المحاسبي الحقيقي يحدث وقت حفظ فاتورة البيع/الشراء في نظامنا،
    //  وليس هنا؛ هذا الإجراء يتحقق فقط أن القيد موجود فعلاً قبل الاعتماد)
    // ─────────────────────────────────────────────

    /** يتحقق أن للفاتورة المرتبطة (إن وُجدت) قيدًا محاسبيًا فعليًا مرحّلًا */
    private function verifyGlPosting(EtaInvoice $invoice): ?string
    {
        $linked = $invoice->linkedInvoice();
        if (!$linked) {
            return 'لم يتم ربط الفاتورة بسجل داخلي - تعذّر التحقق من الترحيل المحاسبي، تُعتمد كامتثال فقط';
        }

        $sourceModule = $invoice->direction === 'Sent' ? 'sales_invoice' : 'purchase_invoice';
        if (!\App\Services\Accounting\JournalPostingService::alreadyPosted($invoice->com_code, $sourceModule, $linked->id)) {
            return 'الفاتورة الداخلية المرتبطة ليس لها قيد محاسبي مرحّل بعد';
        }

        return null;
    }

    public function postInvoice(int $id)
    {
        $invoice = EtaInvoice::where('com_code', $this->comCode())->findOrFail($id);

        if ($invoice->is_posted) {
            return back()->with('error', 'هذه الفاتورة مرحّلة بالفعل');
        }

        $warning = $this->verifyGlPosting($invoice);
        if ($warning && $invoice->linkedInvoice()) {
            // مربوطة لكن بدون قيد فعلي - نمنع الاعتماد حتى تتم مطابقتها محاسبيًا
            return back()->with('error', $warning);
        }

        $invoice->update([
            'is_posted'      => true,
            'posted_at'      => now(),
            'posted_by'      => Auth::guard('admin')->id(),
            'posting_notes'  => $warning,
        ]);

        return back()->with('success', 'تم تأكيد المطابقة المحاسبية للفاتورة بنجاح');
    }

    public function postBulk(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);

        $invoices = EtaInvoice::where('com_code', $this->comCode())
            ->whereIn('id', $request->ids)->where('is_posted', false)->get();

        $count = 0;
        $blocked = 0;
        foreach ($invoices as $invoice) {
            $warning = $this->verifyGlPosting($invoice);
            if ($warning && $invoice->linkedInvoice()) {
                $blocked++;
                continue;
            }
            $invoice->update([
                'is_posted'     => true,
                'posted_at'     => now(),
                'posted_by'     => Auth::guard('admin')->id(),
                'posting_notes' => $warning,
            ]);
            $count++;
        }

        return response()->json(['success' => true, 'count' => $count, 'blocked' => $blocked]);
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

    // ─────────────────────────────────────────────
    //  CSV Exports (منظومة الضرائب)
    // ─────────────────────────────────────────────

    public function exportCsvForm()
    {
        return view('admin.tax.export_csv');
    }

    /** تصدير فواتير المبيعات بتنسيق منظومة الضرائب (sales-doc) */
    public function exportSalesDoc(Request $request)
    {
        $request->validate(['from' => 'required|date', 'to' => 'required|date|after_or_equal:from']);

        $invoices = EtaInvoice::where('com_code', $this->comCode())
            ->where('direction', 'Sent')
            ->where('status', 'Valid')
            ->whereDate('date_issued', '>=', $request->from)
            ->whereDate('date_issued', '<=', $request->to)
            ->with('items')
            ->orderBy('date_issued')
            ->get();

        // أرقام تسجيل عملاء المناطق الحرة من قاعدة البيانات
        $freeZoneIds = EtaFreeZone::where('com_code', $this->comCode())
            ->pluck('tax_id');

        $rows = [];

        // صفا الرأس: عربي + كود
        $rows[] = [
            'نوع المستند *','نوع الضريبة *','نوع سلع الجدول *','رقم الفاتورة *',
            'اسم العميل *','رقم التسجيل الضريبي للعميل *','رقم الملف الضريبي للعميل',
            'العنوان *','الرقم القومي / رقم جواز السفر','رقم الموبايل',
            'تاريخ الفاتورة *','إسم المنتج *','كود المنتج',
            'نوع البيان *','نوع السلعة *','وحدة قياس المنتج',
            'سعر الوحدة *','فئة الضريبة *','كمية المنتج *',
            'المبلغ الإجمالي *','قيمة الخصم','المبلغ الصافي *',
            'قيمة الضريبة *','الإجمالي *',
        ];
        $rows[] = [
            'DOC_TYP_S','TAX_TYP','TYP_SCH','INVNUM',
            'CUST_NAME','CUST_REG_NO','CUST_FILE_NO',
            'ADDRESS','ID','PHONE',
            'DOC_TIN_NUM','PROD_NAME','PROD_CODE',
            'STAT_TYPE_S','COMM_TYPE_S','PMU',
            'UNITP','TAX_CAT','QOP',
            'TAMMOUNT','DISC','NET_AMM',
            'TAX_VAL','TOTAL',
        ];

        foreach ($invoices as $invoice) {
            $raw      = $invoice->raw_data ?? [];
            $receiver = $raw['receiver'] ?? [];

            // نوع المستند والسلعة والبيان
            $addr      = $receiver['address'] ?? [];
            $country   = strtoupper($addr['country'] ?? 'EG');
            $recType   = strtoupper($receiver['type'] ?? 'B');

            // أجنبي: دولة ≠ EG أو نوع F
            $isForeign  = ($country !== 'EG' || in_array($recType, ['F', 'FOREIGN']));
            // منطقة حرة: نوع FZ أو العنوان يحتوي "حر" أو رقم تسجيل مسجّل في قاعدة البيانات
            $addrText   = implode(' ', array_values($addr));
            $isFreeZone = !$isForeign && (
                in_array($recType, ['FZ', 'FREEZONE', 'FREE_ZONE']) ||
                $freeZoneIds->contains($invoice->receiver_id ?? '') ||
                (bool) preg_match('/حر[هة]/u', $addrText)
            );

            $docType  = $isForeign ? 2 : 1;
            $statType = $isForeign ? 5 : 4;  // STAT_TYPE_S: 5=تصدير، 4=خدمة محلية
            $commType = $isFreeZone ? 2 : 1;  // COMM_TYPE_S: 2=منطقة حرة، 1=محلي/تصدير

            // بناء العنوان
            $address = trim(implode(' ', array_filter([
                $addr['buildingNumber'] ?? null,
                $addr['street'] ?? null,
                $addr['regionCity'] ?? null,
                $addr['governate'] ?? null,
                $country !== 'EG' ? $country : null,
            ])));
            if (!$address) $address = $invoice->receiver_name ?? '';

            // تصحيح العناوين الثابتة لعملاء بعينهم
            $addressOverrides = [
                '200029665' => '49 0 / 49 شياخه الفواله قسم عابدين قصر النيل قسم عابدين القاهرة',
            ];
            if (isset($addressOverrides[$invoice->receiver_id ?? ''])) {
                $address = $addressOverrides[$invoice->receiver_id];
            }

            $date       = $invoice->date_issued?->format('d.m.Y') ?? '';
            $fileNum    = $receiver['taxpayerFileNum'] ?? '';
            $invNum     = $invoice->internal_id ?? $invoice->id;

            if ($invoice->items->isNotEmpty()) {
                // هل أي صنف يحمل ضريبة مدمجة في total_with_vat؟
                $anyItemHasVat = $invoice->items->contains(
                    fn($i) => ((float)$i->total_with_vat - (float)$i->net_total) > 0.005
                );

                // احتياطي لو لا يوجد أي صنف بضريبة سطرية — نشتق النسبة من taxTotals
                $invT1Rate = 0.0;
                if (!$anyItemHasVat) {
                    $t1Amt = 0.0;
                    foreach (($raw['taxTotals'] ?? []) as $tax) {
                        if (($tax['taxType'] ?? '') === 'T1') {
                            $t1Amt     = abs((float)($tax['amount'] ?? 0));
                            $invT1Rate = (float)($tax['rate'] ?? 0);
                            break;
                        }
                    }
                    if ($invT1Rate == 0) {
                        $fallbackAmt = $t1Amt > 0 ? $t1Amt : (float)$invoice->total_vat;
                        $invNet      = (float)$invoice->net_amount;
                        if ($invNet > 0 && $fallbackAmt > 0) {
                            $invT1Rate = round($fallbackAmt / $invNet * 100);
                        }
                    }
                }

                foreach ($invoice->items as $item) {
                    $netAmt       = (float)$item->net_total;
                    $totalWithVat = (float)$item->total_with_vat;

                    // الضريبة الفعلية لهذا السطر = الفرق بين الإجمالي والصافي
                    $t1Line = max(0.0, $totalWithVat - $netAmt);

                    if ($t1Line > 0.005) {
                        // ضريبة سطرية متاحة من ETA line['total']
                        $vatAmt  = round($t1Line, 4);
                        $vatRate = $netAmt > 0 ? (int)round($vatAmt / $netAmt * 100) : 0;
                    } elseif (!$anyItemHasVat && $invT1Rate > 0) {
                        // جميع الأصناف بدون ضريبة سطرية — توزيع نسبي من إجمالي الفاتورة
                        $vatRate = (int)$invT1Rate;
                        $vatAmt  = round($netAmt * $vatRate / 100, 4);
                    } else {
                        // هذا الصنف معفى (أصناف أخرى لها ضريبة لكن هذا لا)
                        $vatRate = 0;
                        $vatAmt  = 0.0;
                    }

                    $taxCat = $vatRate > 0 ? $vatRate : 0; // 0=معفى
                    $disc   = (float)$item->discount;

                    $rows[] = [
                        $docType,
                        1,                              // TAX_TYP = VAT دائماً
                        0,                              // TYP_SCH
                        $invNum,
                        $invoice->receiver_name ?? '',
                        $invoice->receiver_id   ?? '',
                        $fileNum,
                        $address,
                        '',                             // ID (رقم قومي)
                        '',                             // PHONE
                        $date,
                        'خدمة',
                        '',
                        $statType,                      // STAT_TYPE_S: 4=محلي، 5=تصدير
                        $commType,
                        '',
                        $this->fmt($item->unit_price),
                        $taxCat,
                        $this->fmt($item->quantity, 4),
                        $this->fmt($item->total),
                        $disc > 0 ? $this->fmt($disc) : '',
                        $this->fmt($netAmt),
                        $this->fmt($vatAmt),
                        $this->fmt($totalWithVat),
                    ];
                }
            } else {
                // لا توجد أصناف — نستخدم إجماليات الفاتورة
                $netAmt  = (float)$invoice->net_amount;
                $vatAmt  = (float)$invoice->total_vat;
                $total   = (float)$invoice->total_amount;
                $sales   = (float)$invoice->total_sales;
                $disc    = (float)$invoice->total_discount;
                $vatRate = $netAmt > 0 ? round(($vatAmt / $netAmt) * 100) : 0;
                $taxCat  = $vatRate > 0 ? (int)$vatRate : 0; // 0=معفى

                $rows[] = [
                    $docType, 1, 0, $invNum,
                    $invoice->receiver_name ?? '',
                    $invoice->receiver_id   ?? '',
                    $fileNum, $address, '', '', $date,
                    'خدمة', '',
                    $statType, $commType, '',
                    $this->fmt($netAmt),    // UNITP = سعر الوحدة الصافي
                    $taxCat,
                    1,                      // QOP = 1 وحدة
                    $this->fmt($sales),     // TAMMOUNT
                    $disc > 0 ? $this->fmt($disc) : '',
                    $this->fmt($netAmt),    // NET_AMM
                    $this->fmt($vatAmt),    // TAX_VAL
                    $this->fmt($total),     // TOTAL
                ];
            }
        }

        $filename = 'sales_doc_' . $request->from . '_' . $request->to . '.csv';
        return $this->streamCsv($filename, $rows);
    }

    /** تصدير نموذج 41 (خصم تحت حساب الضريبة) */
    public function exportForm41(Request $request)
    {
        $request->validate(['from' => 'required|date', 'to' => 'required|date|after_or_equal:from']);

        $invoices = EtaInvoice::where('com_code', $this->comCode())
            ->where('direction', 'Received')
            ->where('status', 'Valid')
            ->whereDate('date_issued', '>=', $request->from)
            ->whereDate('date_issued', '<=', $request->to)
            ->orderBy('date_issued')
            ->get();

        $rows = [];

        // صف رأس عربي واحد فقط
        $rows[] = [
            'مسلسل','رقم التسجيل الضريبي','الرقم القومي','اسم الممول',
            'العنوان','اسم المأمورية','كود المأمورية',
            'تاريخ التعامل','طبيعة التعامل','القيمة الإجمالية للتعامل',
            'نوع الخصم','تاريخ التعامل للمبلغ المخصوم',
            'القيمة الصافية للتعامل','نسبة الخصم','المحصل لحساب الضريبة',
        ];

        foreach ($invoices as $invoice) {
            $raw    = $invoice->raw_data ?? [];
            $issuer = $raw['issuer'] ?? [];

            // استخراج T4 (خصم تحت حساب الضريبة) من taxTotals
            $taxTotals = $raw['taxTotals'] ?? [];
            $whtAmount = 0.0;
            $whtRate   = 0.0;
            foreach ($taxTotals as $tax) {
                if (in_array($tax['taxType'] ?? '', ['T4', 'W1', 'W11'])) {
                    $whtAmount += abs((float)($tax['amount'] ?? 0));
                    $whtRate   += (float)($tax['rate']   ?? 0);
                }
            }

            // DED_TYPE: 3=3%, 4=1%, 5=0.5% (أو افتراضي 5 للخدمات)
            $dedType = 5;
            if ($whtRate >= 3)     $dedType = 3;
            elseif ($whtRate >= 1) $dedType = 4;

            // TRNS_TYP: 4=خدمات (افتراضي للخدمات المهنية)
            $trnsTyp = 4;

            // بناء العنوان
            $addr    = $issuer['address'] ?? [];
            $address = trim(implode(' ', array_filter([
                $addr['buildingNumber'] ?? null,
                $addr['street'] ?? null,
                $addr['regionCity'] ?? null,
                $addr['governate'] ?? null,
            ])));

            $date    = $invoice->date_issued?->format('d.m.Y') ?? '';
            $trnsVal = $this->fmt($invoice->net_amount ?? $invoice->total_sales ?? 0);

            $rows[] = [
                '',                             // SERIAL (فارغ)
                $invoice->issuer_id   ?? '',    // TAX_REGI_NUM
                '',                             // NATI_ID (رقم قومي — للأفراد فقط)
                $invoice->issuer_name ?? '',    // TAXPAYEY_NAM
                $address,                       // TAXPAY_ADDR
                '',                             // TAX_OFF
                '',                             // COD_OFF
                $date,                          // TRNS_DAT
                $trnsTyp,                       // TRNS_TYP
                $trnsVal,                       // TRNS_VAL
                $dedType,                       // DED_TYPE
                $date,                          // DED_AMNT (نفس التاريخ)
                '',                             // TRNS_NET_VAL
                '',                             // DED_PRCT
                '',                             // WTHLD_AMT
            ];
        }

        $filename = 'form41_' . $request->from . '_' . $request->to . '.csv';
        return $this->streamCsv($filename, $rows);
    }

    /** تنسيق رقم عشري بدون أصفار زائدة غير ضرورية */
    private function fmt(float|string $val, int $dec = 2): string
    {
        return number_format((float)$val, $dec, '.', '');
    }

    /** إرجاع CSV كـ StreamedResponse بـ UTF-8 BOM */
    private function streamCsv(string $filename, array $rows)
    {
        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM
            foreach ($rows as $row) {
                fputcsv($out, $row, ',', '"', '\\');
            }
            fclose($out);
        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
