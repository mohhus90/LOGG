{{-- FILE: resources/views/admin/branch_commissions/targets.blade.php --}}
@extends('admin.layouts.admin')
@section('title') أهداف الفروع الشهرية @endsection
@section('start') الرواتب والمؤثرات @endsection
@section('home') <a href="{{ route('branch_commissions.index') }}">عمولات الفروع</a> @endsection
@section('startpage') الأهداف الشهرية @endsection

@section('content')
<div class="col-md-9 mx-auto">

  {{-- فلتر الشهر --}}
  <div class="card card-outline card-primary mb-3">
    <div class="card-body py-2">
      <form method="GET" class="form-inline">
        <label class="ml-2 font-weight-bold">الشهر:</label>
        <select name="month" class="form-control ml-2">
          @foreach(['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'] as $i => $m)
            <option value="{{ $i+1 }}" {{ $month == $i+1 ? 'selected' : '' }}>{{ $m }}</option>
          @endforeach
        </select>
        <input type="number" name="year" class="form-control mr-2 ml-2"
          style="width:90px" value="{{ $year }}">
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-search"></i>
        </button>
      </form>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

  <div class="card card-success">
    <div class="card-header">
      <h3 class="card-title">
        <i class="fas fa-bullseye ml-2"></i>
        تحديد أهداف المبيعات —
        {{ ['','يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'][$month] }}
        {{ $year }}
      </h3>
    </div>

    @if($branches->isEmpty())
      <div class="card-body text-center py-5">
        <i class="fas fa-exclamation-circle fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">لا توجد فروع لديها خطط عمولة نشطة</h5>
        <a href="{{ route('branch_commissions.create') }}" class="btn btn-primary mt-2">
          إنشاء خطة عمولة
        </a>
      </div>
    @else
    <form action="{{ route('branch_commissions.save_targets') }}" method="POST">
      @csrf
      <input type="hidden" name="month" value="{{ $month }}">
      <input type="hidden" name="year" value="{{ $year }}">

      <div class="card-body p-0">
        <table class="table table-bordered mb-0">
          <thead class="thead-dark">
            <tr>
              <th>الفرع</th>
              <th>التارجت الشهري (ج.م)</th>
              <th class="text-muted text-center" style="font-size:.85em">
                المبيعات الفعلية المُدخلة
              </th>
            </tr>
          </thead>
          <tbody>
            @foreach($branches as $branch)
            <tr>
              <td>
                <i class="fas fa-map-marker-alt text-danger ml-1"></i>
                <strong>{{ $branch->branch_name }}</strong>
              </td>
              <td>
                <div class="input-group" style="max-width:280px">
                  <input type="number" name="targets[{{ $branch->id }}]"
                    class="form-control"
                    value="{{ $targets->get($branch->id)?->target_amount ?? '' }}"
                    min="0" step="0.01"
                    placeholder="أدخل التارجت...">
                  <div class="input-group-append">
                    <span class="input-group-text">ج.م</span>
                  </div>
                </div>
              </td>
              <td class="text-center text-muted" style="font-size:.85em">
                @php
                  $actualSales = \App\Models\SalesRecord::where('branch_id', $branch->id)
                    ->where('month', $month)->where('year', $year)
                    ->sum('sales_amount');
                  $target = $targets->get($branch->id)?->target_amount ?? 0;
                  $pct = $target > 0 ? round($actualSales / $target * 100, 1) : null;
                @endphp
                @if($actualSales > 0)
                  {{ number_format($actualSales, 0) }} ج.م
                  @if($pct !== null)
                    <br>
                    <span class="badge {{ $pct >= 100 ? 'badge-success' : ($pct >= 70 ? 'badge-warning' : 'badge-danger') }}">
                      {{ $pct }}%
                    </span>
                  @endif
                @else
                  <span class="text-muted">لم تُدخل بعد</span>
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="card-footer d-flex justify-content-between align-items-center">
        <button type="submit" class="btn btn-success">
          <i class="fas fa-save ml-1"></i>حفظ الأهداف
        </button>
        <div>
          <a href="{{ route('commissions_v2.sales', ['month'=>$month,'year'=>$year]) }}"
             class="btn btn-outline-info btn-sm">
            <i class="fas fa-cash-register ml-1"></i>إدخال مبيعات الموظفين
          </a>
          <a href="{{ route('branch_commissions.calculate', ['month'=>$month,'year'=>$year]) }}"
             class="btn btn-primary btn-sm mr-1">
            <i class="fas fa-calculator ml-1"></i>احتساب العمولات
          </a>
        </div>
      </div>
    </form>
    @endif
  </div>
</div>
@endsection
