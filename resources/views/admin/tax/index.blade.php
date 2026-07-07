@extends('admin.layouts.accounting')
@section('title') الضرائب والفواتير الإلكترونية @endsection
@section('start') الضرائب @endsection
@section('home') <a href="{{ route('tax.index') }}">لوحة الضرائب</a> @endsection
@section('startpage') الرئيسية @endsection

@section('content')
<div class="col-12">

  {{-- تنبيه بيانات الاعتماد --}}
  @if(!$credential)
  <div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle ml-1"></i>
    لم يتم ربط منظومة الفواتير الإلكترونية بعد.
    <a href="{{ route('tax.credentials') }}" class="alert-link">إعداد بيانات الاعتماد</a>
  </div>
  @endif

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

  {{-- بطاقات الإحصاء --}}
  <div class="row">
    <div class="col-md-3 col-sm-6">
      <div class="info-box bg-success">
        <span class="info-box-icon"><i class="fas fa-file-invoice-dollar"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">إجمالي المبيعات</span>
          <span class="info-box-number">{{ number_format($salesTotal, 2) }} ج.م</span>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6">
      <div class="info-box bg-primary">
        <span class="info-box-icon"><i class="fas fa-shopping-cart"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">إجمالي المشتريات</span>
          <span class="info-box-number">{{ number_format($purchaseTotal, 2) }} ج.م</span>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6">
      <div class="info-box bg-warning">
        <span class="info-box-icon"><i class="fas fa-percentage"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">ضريبة مبيعات محصّلة</span>
          <span class="info-box-number">{{ number_format($salesVat, 2) }} ج.م</span>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6">
      <div class="info-box {{ ($salesVat - $purchaseVat) > 0 ? 'bg-danger' : 'bg-teal' }}">
        <span class="info-box-icon"><i class="fas fa-balance-scale"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">صافي الضريبة المستحقة</span>
          <span class="info-box-number">{{ number_format($salesVat - $purchaseVat, 2) }} ج.م</span>
        </div>
      </div>
    </div>
  </div>

  {{-- أزرار سريعة --}}
  <div class="row mb-3">
    <div class="col-12">
      @if($credential)
      <a href="{{ route('tax.sync.form') }}" class="btn btn-dark ml-1">
        <i class="fas fa-cloud-download-alt ml-1"></i> سحب فواتير جديدة من ETA
      </a>
      @endif
      <a href="{{ route('tax.invoices', ['direction' => 'Sent']) }}" class="btn btn-success ml-1">
        <i class="fas fa-arrow-up ml-1"></i> فواتير المبيعات
      </a>
      <a href="{{ route('tax.invoices', ['direction' => 'Received']) }}" class="btn btn-primary ml-1">
        <i class="fas fa-arrow-down ml-1"></i> فواتير المشتريات
      </a>
      <a href="{{ route('tax.vat_report') }}" class="btn btn-warning ml-1">
        <i class="fas fa-file-alt ml-1"></i> الإقرار الضريبي
      </a>
      @if($unpostedCount > 0)
        <span class="badge badge-danger badge-pill ml-1">{{ $unpostedCount }} غير مرحّلة</span>
      @endif
    </div>
  </div>

  {{-- جدولي آخر المبيعات والمشتريات --}}
  <div class="row">
    <div class="col-md-6">
      <div class="card card-success card-outline">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-arrow-up ml-1"></i> آخر فواتير المبيعات</h3>
          <div class="card-tools">
            <a href="{{ route('tax.invoices', ['direction'=>'Sent']) }}" class="btn btn-sm btn-success">
              عرض الكل
            </a>
          </div>
        </div>
        <div class="card-body p-0">
          <table class="table table-sm table-hover mb-0">
            <thead class="thead-light">
              <tr>
                <th>الرقم</th>
                <th>المستلم</th>
                <th>التاريخ</th>
                <th>الإجمالي</th>
                <th>الحالة</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recentSales as $inv)
              <tr>
                <td><a href="{{ route('tax.show', $inv->id) }}">{{ $inv->internal_id ?? substr($inv->uuid,0,8).'...' }}</a></td>
                <td>{{ Str::limit($inv->receiver_name, 20) }}</td>
                <td>{{ $inv->date_issued?->format('Y-m-d') }}</td>
                <td class="text-success font-weight-bold">{{ number_format($inv->total_amount, 2) }}</td>
                <td><span class="badge badge-{{ $inv->status_class }}">{{ $inv->status_label }}</span></td>
              </tr>
              @empty
              <tr><td colspan="5" class="text-center text-muted">لا توجد بيانات</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card card-primary card-outline">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-arrow-down ml-1"></i> آخر فواتير المشتريات</h3>
          <div class="card-tools">
            <a href="{{ route('tax.invoices', ['direction'=>'Received']) }}" class="btn btn-sm btn-primary">
              عرض الكل
            </a>
          </div>
        </div>
        <div class="card-body p-0">
          <table class="table table-sm table-hover mb-0">
            <thead class="thead-light">
              <tr>
                <th>الرقم</th>
                <th>المورد</th>
                <th>التاريخ</th>
                <th>الإجمالي</th>
                <th>الحالة</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recentPurchases as $inv)
              <tr>
                <td><a href="{{ route('tax.show', $inv->id) }}">{{ $inv->internal_id ?? substr($inv->uuid,0,8).'...' }}</a></td>
                <td>{{ Str::limit($inv->issuer_name, 20) }}</td>
                <td>{{ $inv->date_issued?->format('Y-m-d') }}</td>
                <td class="text-primary font-weight-bold">{{ number_format($inv->total_amount, 2) }}</td>
                <td><span class="badge badge-{{ $inv->status_class }}">{{ $inv->status_label }}</span></td>
              </tr>
              @empty
              <tr><td colspan="5" class="text-center text-muted">لا توجد بيانات</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection
