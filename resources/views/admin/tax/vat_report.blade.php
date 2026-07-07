@extends('admin.layouts.accounting')
@section('title') الإقرار الضريبي @endsection
@section('start') الضرائب @endsection
@section('home') <a href="{{ route('tax.index') }}">الضرائب</a> @endsection
@section('startpage') الإقرار الضريبي @endsection

@section('content')
<div class="col-12 col-md-10 offset-md-1">

  {{-- فلتر الفترة --}}
  <div class="card card-outline card-dark mb-4">
    <div class="card-body py-2">
      <form method="GET" action="{{ route('tax.vat_report') }}" class="form-inline">
        <label class="ml-2">من:</label>
        <input type="date" name="from" class="form-control ml-2" value="{{ $from }}">
        <label class="ml-2">إلى:</label>
        <input type="date" name="to" class="form-control ml-2" value="{{ $to }}">
        <button type="submit" class="btn btn-dark ml-2"><i class="fas fa-search ml-1"></i>عرض</button>
        <span class="text-muted mr-3">الفاتورة المعتمدة (Valid) فقط</span>
      </form>
    </div>
  </div>

  {{-- ملخص الإقرار --}}
  <div class="card">
    <div class="card-header bg-dark text-white">
      <h3 class="card-title">
        <i class="fas fa-file-alt ml-2"></i>
        الإقرار الضريبي — الفترة من {{ $from }} إلى {{ $to }}
      </h3>
      <div class="card-tools">
        <button onclick="window.print()" class="btn btn-sm btn-light">
          <i class="fas fa-print ml-1"></i>طباعة
        </button>
      </div>
    </div>
    <div class="card-body">

      <div class="row">
        {{-- المبيعات --}}
        <div class="col-md-6">
          <div class="card card-success">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-arrow-up ml-1"></i>المبيعات (الضريبة المحصّلة)</h3>
            </div>
            <div class="card-body p-0">
              <table class="table table-sm mb-0">
                <tr><th>عدد الفواتير</th><td class="text-right">{{ number_format($sales->count ?? 0) }}</td></tr>
                <tr><th>صافي المبيعات</th><td class="text-right">{{ number_format($sales->net_amount ?? 0, 2) }} ج.م</td></tr>
                <tr class="table-danger">
                  <th>ضريبة القيمة المضافة (ناتج ضريبي)</th>
                  <td class="text-right font-weight-bold">{{ number_format($sales->total_vat ?? 0, 2) }} ج.م</td>
                </tr>
                <tr class="table-success">
                  <th>إجمالي مع الضريبة</th>
                  <td class="text-right font-weight-bold">{{ number_format($sales->total_amount ?? 0, 2) }} ج.م</td>
                </tr>
              </table>
            </div>
          </div>
        </div>

        {{-- المشتريات --}}
        <div class="col-md-6">
          <div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-arrow-down ml-1"></i>المشتريات (الضريبة المدفوعة)</h3>
            </div>
            <div class="card-body p-0">
              <table class="table table-sm mb-0">
                <tr><th>عدد الفواتير</th><td class="text-right">{{ number_format($purchases->count ?? 0) }}</td></tr>
                <tr><th>صافي المشتريات</th><td class="text-right">{{ number_format($purchases->net_amount ?? 0, 2) }} ج.م</td></tr>
                <tr class="table-warning">
                  <th>ضريبة القيمة المضافة (ضريبة مدخلات)</th>
                  <td class="text-right font-weight-bold">{{ number_format($purchases->total_vat ?? 0, 2) }} ج.م</td>
                </tr>
                <tr class="table-primary">
                  <th>إجمالي مع الضريبة</th>
                  <td class="text-right font-weight-bold">{{ number_format($purchases->total_amount ?? 0, 2) }} ج.م</td>
                </tr>
              </table>
            </div>
          </div>
        </div>
      </div>

      {{-- صافي الضريبة المستحقة --}}
      <div class="card {{ $netVat > 0 ? 'card-danger' : 'card-success' }} mt-3">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-balance-scale ml-2"></i>
            صافي الضريبة المستحقة للسداد
          </h3>
        </div>
        <div class="card-body">
          <div class="row text-center">
            <div class="col-md-4">
              <h5 class="text-muted">ضريبة المبيعات</h5>
              <h3 class="text-danger">{{ number_format($sales->total_vat ?? 0, 2) }}</h3>
            </div>
            <div class="col-md-4">
              <h5 class="text-muted">ضريبة المشتريات المخصومة</h5>
              <h3 class="text-success">{{ number_format($purchases->total_vat ?? 0, 2) }}</h3>
            </div>
            <div class="col-md-4">
              <h5 class="text-muted">الصافي المستحق</h5>
              <h2 class="font-weight-bold {{ $netVat > 0 ? 'text-danger' : 'text-success' }}">
                {{ number_format(abs($netVat), 2) }} ج.م
              </h2>
              @if($netVat > 0)
                <span class="badge badge-danger">مستحق السداد</span>
              @elseif($netVat < 0)
                <span class="badge badge-success">رصيد دائن</span>
              @else
                <span class="badge badge-secondary">متعادل</span>
              @endif
            </div>
          </div>
        </div>
      </div>

      {{-- جدول الإقرار المفصّل --}}
      <div class="mt-4">
        <h5><i class="fas fa-table ml-1"></i>ملخص الإقرار</h5>
        <table class="table table-bordered">
          <thead class="thead-dark">
            <tr>
              <th>البيان</th>
              <th class="text-right">الوعاء الضريبي (صافي)</th>
              <th class="text-right">الضريبة</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>المبيعات الخاضعة للضريبة</td>
              <td class="text-right">{{ number_format($sales->net_amount ?? 0, 2) }}</td>
              <td class="text-right text-danger">{{ number_format($sales->total_vat ?? 0, 2) }}</td>
            </tr>
            <tr>
              <td>المشتريات الخاضعة للضريبة</td>
              <td class="text-right">{{ number_format($purchases->net_amount ?? 0, 2) }}</td>
              <td class="text-right text-success">({{ number_format($purchases->total_vat ?? 0, 2) }})</td>
            </tr>
            <tr class="table-dark font-weight-bold">
              <td>صافي الضريبة المستحقة</td>
              <td></td>
              <td class="text-right {{ $netVat >= 0 ? 'text-danger' : 'text-success' }}">
                {{ number_format($netVat, 2) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</div>
@endsection

@section('css')
<style>
@media print {
    .main-sidebar, .main-header, .content-header, .card-tools { display: none !important; }
    .content-wrapper { margin: 0 !important; }
}
</style>
@endsection
