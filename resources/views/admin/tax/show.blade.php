@extends('admin.layouts.accounting')
@section('title') تفاصيل الفاتورة @endsection
@section('start') الضرائب @endsection
@section('home') <a href="{{ route('tax.invoices', ['direction' => $invoice->direction]) }}">{{ $invoice->direction_label }}</a> @endsection
@section('startpage') تفاصيل الفاتورة @endsection

@section('content')
@php
  $raw      = $invoice->raw_data ?? [];

  // بيانات الطرفين — تكون nested في التفاصيل الكاملة أو flat في نتائج البحث
  $issuer   = $raw['issuer']   ?? [];
  $receiver = $raw['receiver'] ?? [];

  // ملخص المبالغ
  $taxTotals          = $raw['taxTotals']               ?? [];
  $extraDiscount      = (float)($raw['extraDiscountAmount']      ?? 0);
  $invoiceDiscount    = (float)($raw['totalItemsDiscountAmount'] ?? $invoice->total_discount ?? 0);

  // تسميات أنواع الضرائب ETA
  $taxTypeLabels = [
    'T1'  => 'ضريبة القيمة المضافة',
    'T2'  => 'ضريبة جدولية (نسبية)',
    'T3'  => 'رسوم جمركية',
    'T4'  => 'خصم تحت حساب الضريبة',
    'W1'  => 'خصم تحت حساب الضريبة',
    'W11' => 'خصم تحت حساب الضريبة',
  ];

  // نوع الطرف
  $partyTypes = ['B' => 'شركة', 'P' => 'فرد', 'F' => 'أجنبي'];
@endphp

<div class="col-12">

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

  <div class="card card-outline card-{{ $invoice->direction === 'Sent' ? 'success' : 'primary' }}">

    {{-- ─── رأس الفاتورة ─── --}}
    <div class="card-header">
      <h3 class="card-title">
        <i class="fas fa-file-invoice ml-2"></i>
        {{ $invoice->doc_type_label }}
        @if($invoice->document_type_version)
          <small class="text-muted">v{{ $invoice->document_type_version }}</small>
        @endif
        — {{ $invoice->direction_label }}
        <span class="badge badge-{{ $invoice->status_class }} mr-2">{{ $invoice->status_label }}</span>
        @if($invoice->is_posted)
          <span class="badge badge-success"><i class="fas fa-check ml-1"></i>مرحّل محاسبياً</span>
        @endif
      </h3>
      <div class="card-tools">
        {{-- زرار سحب التفاصيل من ETA --}}
        <form action="{{ route('tax.fetch_details', $invoice->id) }}" method="POST" class="d-inline">
          @csrf
          <button class="btn btn-info btn-sm" onclick="return confirm('سحب أحدث تفاصيل هذه الفاتورة من ETA؟')">
            <i class="fas fa-cloud-download-alt ml-1"></i> سحب التفاصيل من ETA
          </button>
        </form>

        @if($invoice->status === 'Valid' && !$invoice->is_posted)
          <form action="{{ route('tax.post', $invoice->id) }}" method="POST" class="d-inline">
            @csrf
            <button class="btn btn-warning btn-sm"><i class="fas fa-check ml-1"></i> تأكيد المطابقة المحاسبية</button>
          </form>
        @elseif($invoice->is_posted)
          <form action="{{ route('tax.unpost', $invoice->id) }}" method="POST" class="d-inline">
            @csrf
            <button class="btn btn-secondary btn-sm" onclick="return confirm('إلغاء الترحيل؟')">
              <i class="fas fa-undo ml-1"></i> إلغاء التأكيد
            </button>
          </form>
        @endif
        <a href="{{ route('tax.invoices', ['direction' => $invoice->direction]) }}" class="btn btn-default btn-sm">
          <i class="fas fa-arrow-right ml-1"></i> رجوع
        </a>
      </div>
    </div>

    <div class="card-body">

      {{-- ─── معلومات الفاتورة الأساسية ─── --}}
      <div class="row mb-3">
        <div class="col-md-6">
          <table class="table table-sm table-borderless mb-0">
            <tr>
              <th style="width:160px" class="text-muted">الرقم الإلكتروني</th>
              <td dir="ltr" class="ltr-text"><code class="text-dark">{{ $invoice->long_id ?? $invoice->uuid }}</code></td>
            </tr>
            <tr>
              <th class="text-muted">الرقم الداخلي</th>
              <td dir="ltr" class="ltr-text">{{ $invoice->internal_id ?? '—' }}</td>
            </tr>
            <tr>
              <th class="text-muted">تاريخ الإصدار</th>
              <td>{{ $invoice->date_issued?->format('Y/m/d H:i') ?? '—' }}</td>
            </tr>
            @if($invoice->date_received)
            <tr>
              <th class="text-muted">تاريخ الاستلام</th>
              <td>{{ $invoice->date_received->format('Y/m/d H:i') }}</td>
            </tr>
            @endif
          </table>
        </div>
        <div class="col-md-6">
          <table class="table table-sm table-borderless mb-0">
            @if($invoice->activity_code)
            <tr>
              <th style="width:160px" class="text-muted">كود النشاط الضريبي</th>
              <td>{{ $invoice->activity_code }}</td>
            </tr>
            @endif
            @if($invoice->uuid !== $invoice->long_id)
            <tr>
              <th class="text-muted">UUID</th>
              <td dir="ltr" class="ltr-text"><small><code>{{ $invoice->uuid }}</code></small></td>
            </tr>
            @endif
          </table>
        </div>
      </div>

      <hr class="mt-0">

      {{-- ─── البائع والمشتري ─── --}}
      <div class="row">
        {{-- البائع --}}
        <div class="col-md-6">
          <div class="card card-outline card-secondary mb-3">
            <div class="card-header py-2" style="background:#2c3e50;color:#fff">
              <h6 class="mb-0"><i class="fas fa-store ml-1"></i>البائع</h6>
            </div>
            <div class="card-body py-2">
              <table class="table table-sm table-borderless mb-0">
                <tr>
                  <th style="width:130px" class="text-muted">الاسم</th>
                  <td class="font-weight-bold">{{ $issuer['name'] ?? $invoice->issuer_name ?? '—' }}</td>
                </tr>
                <tr>
                  <th class="text-muted">رقم التسجيل</th>
                  <td dir="ltr" class="ltr-text"><code>{{ $issuer['id'] ?? $invoice->issuer_id ?? '—' }}</code></td>
                </tr>
                @if(!empty($issuer['type']))
                <tr>
                  <th class="text-muted">النوع</th>
                  <td>{{ $partyTypes[$issuer['type']] ?? $issuer['type'] }}</td>
                </tr>
                @endif
                @if(!empty($issuer['branchID']) || !empty($issuer['branchCode']))
                <tr>
                  <th class="text-muted">عنوان الفرع</th>
                  <td>{{ $issuer['branchID'] ?? $issuer['branchCode'] }}</td>
                </tr>
                @endif
                @php
                  $iAddr = $issuer['address'] ?? [];
                  $iAddrStr = implode('، ', array_filter([
                    $iAddr['street']    ?? null,
                    $iAddr['buildingNumber'] ?? null,
                    $iAddr['regionCity'] ?? null,
                    $iAddr['governate'] ?? null,
                    $iAddr['country']   ?? null,
                  ]));
                @endphp
                @if($iAddrStr)
                <tr>
                  <th class="text-muted">العنوان</th>
                  <td><small>{{ $iAddrStr }}</small></td>
                </tr>
                @endif
              </table>
            </div>
          </div>
        </div>

        {{-- المشتري --}}
        <div class="col-md-6">
          <div class="card card-outline card-secondary mb-3">
            <div class="card-header py-2" style="background:#2c3e50;color:#fff">
              <h6 class="mb-0"><i class="fas fa-user ml-1"></i>المشتري</h6>
            </div>
            <div class="card-body py-2">
              <table class="table table-sm table-borderless mb-0">
                <tr>
                  <th style="width:130px" class="text-muted">الاسم</th>
                  <td class="font-weight-bold">{{ $receiver['name'] ?? $invoice->receiver_name ?? '—' }}</td>
                </tr>
                <tr>
                  <th class="text-muted">رقم التسجيل</th>
                  <td dir="ltr" class="ltr-text"><code>{{ $receiver['id'] ?? $invoice->receiver_id ?? '—' }}</code></td>
                </tr>
                @if(!empty($receiver['type']))
                <tr>
                  <th class="text-muted">النوع</th>
                  <td>{{ $partyTypes[$receiver['type']] ?? $receiver['type'] }}</td>
                </tr>
                @endif
                @php
                  $rAddr = $receiver['address'] ?? [];
                  $rAddrStr = implode('، ', array_filter([
                    $rAddr['street']    ?? null,
                    $rAddr['buildingNumber'] ?? null,
                    $rAddr['regionCity'] ?? null,
                    $rAddr['governate'] ?? null,
                    $rAddr['country']   ?? null,
                  ]));
                @endphp
                @if($rAddrStr)
                <tr>
                  <th class="text-muted">العنوان</th>
                  <td><small>{{ $rAddrStr }}</small></td>
                </tr>
                @endif
                @if(!empty($receiver['taxpayerActivityCode']))
                <tr>
                  <th class="text-muted">كود النشاط</th>
                  <td>{{ $receiver['taxpayerActivityCode'] }}</td>
                </tr>
                @endif
              </table>
            </div>
          </div>
        </div>
      </div>

      {{-- ─── بنود الفاتورة ─── --}}
      @if($invoice->items->isNotEmpty())
      <h6 class="mb-2"><i class="fas fa-list ml-1"></i>الأصناف</h6>
      <div class="table-responsive mb-3">
        <table class="table table-bordered table-sm">
          <thead class="thead-dark">
            <tr>
              <th>#</th>
              <th>اسم الكود</th>
              <th>كود الصنف</th>
              <th>الوصف</th>
              <th>الكمية / نوع الوحدة</th>
              <th>سعر الوحدة (ج.م)</th>
              <th>إجمالي المبيعات</th>
              <th>الخصم</th>
              <th>الصافي</th>
              @if($invoice->items->sum('vat_amount') > 0)
              <th>نوع الضريبة</th>
              <th>قيمة الضريبة</th>
              <th>الإجمالي + ض</th>
              @endif
            </tr>
          </thead>
          <tbody>
            @foreach($invoice->items as $item)
            @php
              $lineRaw  = $raw['invoiceLines'][$loop->index] ?? [];
              $itemName = $lineRaw['internalCode'] ?? $lineRaw['itemCode'] ?? $item->item_code ?? '—';
              $taxItems = $lineRaw['taxableItems'] ?? [];
              $taxDesc  = collect($taxItems)->map(fn($t) =>
                ($taxTypeLabels[$t['taxType']] ?? $t['taxType']) . ' ' . ($t['rate'] ?? '') . '%'
              )->implode(' | ');
              if (!$taxDesc && $item->vat_rate > 0) {
                $taxDesc = $item->vat_rate . '%';
              }
            @endphp
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td dir="ltr" class="ltr-text"><small>{{ $itemName }}</small></td>
              <td dir="ltr" class="ltr-text"><small>{{ $item->item_code ?? '—' }}</small></td>
              <td>{{ $item->description ?? '—' }}</td>
              <td class="text-center">
                <span class="ltr-text">{{ number_format($item->quantity, 2) }}</span>
                @if($item->unit_type) <small class="text-muted">{{ $item->unit_type }}</small> @endif
              </td>
              <td class="text-left ltr-text">{{ number_format($item->unit_price, 2) }}</td>
              <td class="text-left ltr-text">{{ number_format($item->total, 2) }}</td>
              <td class="text-left ltr-text text-danger">{{ number_format($item->discount, 2) }}</td>
              <td class="text-left ltr-text">{{ number_format($item->net_total, 2) }}</td>
              @if($invoice->items->sum('vat_amount') > 0)
              <td class="text-center"><small>{{ $taxDesc ?: '—' }}</small></td>
              <td class="text-left ltr-text">{{ number_format($item->vat_amount, 2) }}</td>
              <td class="text-left ltr-text font-weight-bold">{{ number_format($item->total_with_vat, 2) }}</td>
              @endif
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endif

      {{-- ─── ملخص المبالغ ─── --}}
      <div class="row justify-content-end">
        <div class="col-md-5">
          <table class="table table-sm table-bordered">
            <tr>
              <th class="text-right">إجمالي المبيعات (ج.م)</th>
              <td class="text-right">{{ number_format($invoice->total_sales, 2) }}</td>
            </tr>
            <tr>
              <th class="text-right">إجمالي الخصم (ج.م)</th>
              <td class="text-right text-danger">{{ number_format($invoiceDiscount, 2) }}</td>
            </tr>
            @if($extraDiscount > 0)
            <tr>
              <th class="text-right">إجمالي خصم الإضافي (ج.م)</th>
              <td class="text-right text-danger">{{ number_format($extraDiscount, 2) }}</td>
            </tr>
            @endif

            {{-- تفاصيل الضرائب من raw_data --}}
            @if(!empty($taxTotals))
              @foreach($taxTotals as $tax)
              @php
                $taxCode  = $tax['taxType'] ?? '';
                $taxLabel = $taxTypeLabels[$taxCode] ?? $taxCode;
                $taxAmt   = (float)($tax['amount'] ?? 0);
                $isWht    = in_array($taxCode, ['T4','W1','W11']);
              @endphp
              <tr>
                <th class="text-right">{{ $taxLabel }} (ج.م)</th>
                <td class="text-right {{ $isWht ? 'text-danger' : '' }}">
                  @if($isWht) - @endif{{ number_format(abs($taxAmt), 2) }}
                </td>
              </tr>
              @endforeach
            @else
              {{-- fallback إذا لم تكن taxTotals متاحة --}}
              @if($invoice->total_vat != 0)
              <tr>
                <th class="text-right">الضريبة (ج.م)</th>
                <td class="text-right">{{ number_format($invoice->total_vat, 2) }}</td>
              </tr>
              @endif
            @endif

            @if(!empty($raw['invoiceDiscount']['amount']) && $raw['invoiceDiscount']['amount'] > 0)
            <tr>
              <th class="text-right">خصم الفاتورة الإضافي (ج.م)</th>
              <td class="text-right text-danger">{{ number_format($raw['invoiceDiscount']['amount'], 2) }}</td>
            </tr>
            @endif

            <tr class="table-dark">
              <th class="text-right">اجمالي المبلغ (ج.م)</th>
              <td class="text-right font-weight-bold">{{ number_format($invoice->total_amount, 2) }}</td>
            </tr>
          </table>
        </div>
      </div>

      {{-- ─── الربط بالسجل الداخلي والمطابقة المحاسبية ─── --}}
      @if($invoice->status === 'Valid')
      <hr>
      <h6><i class="fas fa-link ml-1"></i>الربط بالفاتورة الداخلية</h6>

      @if($linkedInvoice)
        <div class="alert alert-info d-flex justify-content-between align-items-center">
          <div>
            مربوطة بـ
            @if($invoice->direction === 'Sent')
              <a href="{{ route('sales_invoices.show', $linkedInvoice->id) }}">{{ $linkedInvoice->invoice_number }}</a>
            @else
              <a href="{{ route('purchase_invoices.show', $linkedInvoice->id) }}">{{ $linkedInvoice->invoice_number }}</a>
            @endif
            — إجمالي {{ number_format($linkedInvoice->total, 2) }}
          </div>
          <form action="{{ route('tax.unlink', $invoice->id) }}" method="POST" class="d-inline">
            @csrf
            <button class="btn btn-xs btn-outline-secondary" onclick="return confirm('إلغاء الربط؟')">إلغاء الربط</button>
          </form>
        </div>
      @else
        <p class="text-muted">لم يتم ربط هذه الفاتورة بسجل داخلي بعد. اختر أقرب فاتورة مطابقة من المقترحات التالية (بالمبلغ والتاريخ):</p>
        @if($suggestedMatches->count())
        <form action="{{ route('tax.link', $invoice->id) }}" method="POST" class="form-inline">
          @csrf
          <select name="linked_invoice_id" class="form-control form-control-sm ml-2" required style="min-width:280px">
            <option value="">-- اختر الفاتورة --</option>
            @foreach($suggestedMatches as $match)
              <option value="{{ $match->id }}">{{ $match->invoice_number }} — {{ number_format($match->total, 2) }} — {{ \Carbon\Carbon::parse($match->date)->format('Y-m-d') }}</option>
            @endforeach
          </select>
          <button class="btn btn-primary btn-sm"><i class="fas fa-link ml-1"></i> ربط</button>
        </form>
        @else
        <p class="text-muted small">لا توجد فواتير داخلية مطابقة بنفس المبلغ تقريبًا خلال ٣ أيام من تاريخ الإصدار.</p>
        @endif
      @endif

      <hr>
      <h6><i class="fas fa-book ml-1"></i>حالة الترحيل المحاسبي</h6>
      <p class="text-muted small">
        القيد المحاسبي الفعلي يُرحَّل تلقائيًا وقت حفظ الفاتورة في نظامنا (موديول المحاسبة)، وليس هنا -
        هذا القسم يتحقق فقط من وجود ذلك القيد قبل اعتماد المطابقة مع ETA.
      </p>
      @if($invoice->is_posted)
        <p class="text-success"><i class="fas fa-check-circle ml-1"></i>
          تم تأكيد المطابقة بتاريخ {{ $invoice->posted_at?->format('Y-m-d H:i') }}
        </p>
        @if($invoice->posting_notes)
          <p class="text-warning small"><i class="fas fa-exclamation-triangle ml-1"></i>{{ $invoice->posting_notes }}</p>
        @endif
      @else
        <p class="text-warning"><i class="fas fa-exclamation-triangle ml-1"></i>لم يتم تأكيد المطابقة المحاسبية بعد</p>
      @endif
      @endif

    </div>
  </div>
</div>
@endsection
