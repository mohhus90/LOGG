@extends('admin.layouts.admin')
@section('title') تفاصيل الفاتورة @endsection
@section('start') الضرائب @endsection
@section('home') <a href="{{ route('tax.invoices', ['direction' => $invoice->direction]) }}">{{ $invoice->direction_label }}</a> @endsection
@section('startpage') تفاصيل الفاتورة @endsection

@section('content')
<div class="col-12">

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

  {{-- رأس الفاتورة --}}
  <div class="card card-outline card-{{ $invoice->direction === 'Sent' ? 'success' : 'primary' }}">
    <div class="card-header">
      <h3 class="card-title">
        <i class="fas fa-file-invoice ml-2"></i>
        {{ $invoice->doc_type_label }} —
        {{ $invoice->direction_label }}
        <span class="badge badge-{{ $invoice->status_class }} mr-1">{{ $invoice->status_label }}</span>
        @if($invoice->is_posted)
          <span class="badge badge-success"><i class="fas fa-check ml-1"></i>مرحّل محاسبياً</span>
        @endif
      </h3>
      <div class="card-tools">
        @if($invoice->status === 'Valid' && !$invoice->is_posted)
          <form action="{{ route('tax.post', $invoice->id) }}" method="POST" class="d-inline">
            @csrf
            <button class="btn btn-warning btn-sm">
              <i class="fas fa-check ml-1"></i> ترحيل محاسبي
            </button>
          </form>
        @elseif($invoice->is_posted)
          <form action="{{ route('tax.unpost', $invoice->id) }}" method="POST" class="d-inline">
            @csrf
            <button class="btn btn-secondary btn-sm" onclick="return confirm('إلغاء الترحيل؟')">
              <i class="fas fa-undo ml-1"></i> إلغاء الترحيل
            </button>
          </form>
        @endif
        <a href="{{ route('tax.invoices', ['direction' => $invoice->direction]) }}" class="btn btn-default btn-sm">
          <i class="fas fa-arrow-right ml-1"></i> رجوع
        </a>
      </div>
    </div>

    <div class="card-body">
      <div class="row">
        {{-- بيانات المُصدر --}}
        <div class="col-md-6">
          <h6 class="text-muted mb-2"><i class="fas fa-building ml-1"></i>المُصدر</h6>
          <table class="table table-sm table-borderless">
            <tr><th width="130">الاسم</th><td>{{ $invoice->issuer_name ?? '—' }}</td></tr>
            <tr><th>الرقم الضريبي</th><td><code>{{ $invoice->issuer_id ?? '—' }}</code></td></tr>
          </table>
        </div>
        {{-- بيانات المستلم --}}
        <div class="col-md-6">
          <h6 class="text-muted mb-2"><i class="fas fa-user ml-1"></i>المستلم</h6>
          <table class="table table-sm table-borderless">
            <tr><th width="130">الاسم</th><td>{{ $invoice->receiver_name ?? '—' }}</td></tr>
            <tr><th>الرقم الضريبي</th><td><code>{{ $invoice->receiver_id ?? '—' }}</code></td></tr>
          </table>
        </div>
      </div>

      <hr>

      <div class="row">
        <div class="col-md-4">
          <table class="table table-sm table-borderless">
            <tr><th>الرقم الداخلي</th><td>{{ $invoice->internal_id ?? '—' }}</td></tr>
            <tr><th>UUID</th><td><small><code>{{ $invoice->uuid }}</code></small></td></tr>
            <tr><th>تاريخ الإصدار</th><td>{{ $invoice->date_issued?->format('Y-m-d H:i') }}</td></tr>
            <tr><th>تاريخ الاستلام</th><td>{{ $invoice->date_received?->format('Y-m-d H:i') ?? '—' }}</td></tr>
            <tr><th>كود النشاط</th><td>{{ $invoice->activity_code ?? '—' }}</td></tr>
          </table>
        </div>
        <div class="col-md-4 offset-md-4">
          <table class="table table-sm">
            <tr>
              <th>إجمالي المبيعات</th>
              <td class="text-right">{{ number_format($invoice->total_sales, 2) }}</td>
            </tr>
            <tr>
              <th>إجمالي الخصم</th>
              <td class="text-right text-danger">- {{ number_format($invoice->total_discount, 2) }}</td>
            </tr>
            <tr>
              <th>صافي المبلغ</th>
              <td class="text-right">{{ number_format($invoice->net_amount, 2) }}</td>
            </tr>
            <tr>
              <th>الضريبة (14%)</th>
              <td class="text-right text-danger">{{ number_format($invoice->total_vat, 2) }}</td>
            </tr>
            <tr class="table-dark">
              <th>الإجمالي الكلي</th>
              <td class="text-right font-weight-bold">{{ number_format($invoice->total_amount, 2) }} ج.م</td>
            </tr>
          </table>
        </div>
      </div>

      {{-- بنود الفاتورة --}}
      @if($invoice->items->isNotEmpty())
      <hr>
      <h6><i class="fas fa-list ml-1"></i>بنود الفاتورة</h6>
      <div class="table-responsive">
        <table class="table table-bordered table-sm">
          <thead class="thead-dark">
            <tr>
              <th>#</th>
              <th>الكود</th>
              <th>الوصف</th>
              <th>الوحدة</th>
              <th>الكمية</th>
              <th>سعر الوحدة</th>
              <th>الإجمالي</th>
              <th>الخصم</th>
              <th>الصافي</th>
              <th>الضريبة %</th>
              <th>قيمة الضريبة</th>
              <th>الإجمالي + ض</th>
            </tr>
          </thead>
          <tbody>
            @foreach($invoice->items as $item)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td><small>{{ $item->item_code ?? '—' }}</small></td>
              <td>{{ $item->description ?? '—' }}</td>
              <td>{{ $item->unit_type ?? '—' }}</td>
              <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
              <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
              <td class="text-right">{{ number_format($item->total, 2) }}</td>
              <td class="text-right text-danger">{{ number_format($item->discount, 2) }}</td>
              <td class="text-right">{{ number_format($item->net_total, 2) }}</td>
              <td class="text-center">{{ $item->vat_rate }}%</td>
              <td class="text-right text-danger">{{ number_format($item->vat_amount, 2) }}</td>
              <td class="text-right font-weight-bold">{{ number_format($item->total_with_vat, 2) }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endif

      {{-- القيد المحاسبي المقترح --}}
      @if($invoice->status === 'Valid')
      <hr>
      <h6><i class="fas fa-book ml-1"></i>القيد المحاسبي المقترح</h6>
      @php
        $isPosted = $invoice->is_posted;
        $net      = $invoice->net_amount;
        $vat      = $invoice->total_vat;
        $total    = $invoice->total_amount;
        $isSales  = $invoice->direction === 'Sent';
      @endphp
      <div class="table-responsive">
        <table class="table table-sm table-bordered {{ $isPosted ? '' : 'table-warning' }}">
          <thead class="thead-dark">
            <tr><th>الحساب</th><th>مدين</th><th>دائن</th></tr>
          </thead>
          <tbody>
            @if($isSales)
            <tr>
              <td>العملاء / ذمم مدينة</td>
              <td class="text-right">{{ number_format($total, 2) }}</td>
              <td></td>
            </tr>
            <tr>
              <td>المبيعات</td>
              <td></td>
              <td class="text-right">{{ number_format($net, 2) }}</td>
            </tr>
            <tr>
              <td>ضريبة القيمة المضافة (دائن)</td>
              <td></td>
              <td class="text-right">{{ number_format($vat, 2) }}</td>
            </tr>
            @else
            <tr>
              <td>المشتريات</td>
              <td class="text-right">{{ number_format($net, 2) }}</td>
              <td></td>
            </tr>
            <tr>
              <td>ضريبة القيمة المضافة (مدين)</td>
              <td class="text-right">{{ number_format($vat, 2) }}</td>
              <td></td>
            </tr>
            <tr>
              <td>الموردون / ذمم دائنة</td>
              <td></td>
              <td class="text-right">{{ number_format($total, 2) }}</td>
            </tr>
            @endif
          </tbody>
        </table>
      </div>
      @if(!$isPosted)
        <p class="text-warning"><i class="fas fa-exclamation-triangle ml-1"></i>القيد مقترح ولم يُرحَّل بعد</p>
      @else
        <p class="text-success"><i class="fas fa-check-circle ml-1"></i>
          مرحّل بتاريخ {{ $invoice->posted_at?->format('Y-m-d H:i') }}
        </p>
      @endif
      @endif

    </div>
  </div>
</div>
@endsection
