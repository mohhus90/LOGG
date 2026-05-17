{{-- FILE: resources/views/admin/commissions_v2/sales.blade.php --}}
@extends('admin.layouts.admin')
@section('title') إدخال مبيعات الشهر @endsection
@section('start') الرواتب والمؤثرات @endsection
@section('home') <a href="{{ route('commissions_v2.rules') }}">قواعد العمولات</a> @endsection
@section('startpage') إدخال المبيعات @endsection

@section('content')
<div class="col-12">
<div class="card">
  <div class="card-header">
    <h3 class="card-title">
      <i class="fas fa-cash-register ml-2 text-success"></i>إدخال مبيعات الشهر
    </h3>
  </div>

  <div class="card-body pb-0">
    <form method="GET" class="form-inline mb-3">
      <select name="month" class="form-control ml-2">
        @foreach(['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'] as $i=>$m)
        <option value="{{ $i+1 }}" {{ $month==$i+1?'selected':'' }}>{{ $m }}</option>
        @endforeach
      </select>
      <input type="number" name="year" class="form-control mr-2 ml-2" style="width:90px" value="{{ $year }}">
      <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
    </form>
    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif
  </div>

  <form action="{{ route('commissions_v2.save_sales') }}" method="POST">
    @csrf
    <input type="hidden" name="month" value="{{ $month }}">
    <input type="hidden" name="year" value="{{ $year }}">

    <div class="card-body pt-0">
    <div class="table-responsive">
    <table class="table table-bordered table-sm">
      <thead class="thead-dark">
        <tr>
          <th>#</th>
          <th>الموظف</th>
          <th>الفرع</th>
          <th>قيمة المبيعات</th>
          <th>المبيعات المسجلة مسبقاً</th>
        </tr>
      </thead>
      <tbody>
        @foreach($employees as $emp)
        @php $prev = $existing->get($emp->id)?->first(); @endphp
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td>
            <strong>{{ $emp->employee_name_A }}</strong>
            <br><small class="text-muted">{{ $emp->employee_id }}</small>
          </td>
          <td>
            <select name="branch_id[{{ $emp->id }}]" class="form-control form-control-sm" style="width:140px">
              <option value="">-- --</option>
              @foreach($branches as $br)
                <option value="{{ $br->id }}"
                  {{ ($prev?->branch_id == $br->id || $emp->branch_id == $br->id) ? 'selected' : '' }}>
                  {{ $br->branch_name }}
                </option>
              @endforeach
            </select>
          </td>
          <td>
            <div class="input-group" style="max-width:160px">
              <input type="number" name="sales[{{ $emp->id }}]" class="form-control form-control-sm"
                step="0.01" min="0"
                value="{{ $prev?->sales_amount ?? '' }}"
                placeholder="0.00">
              <div class="input-group-append"><span class="input-group-text" style="font-size:.8em">ج.م</span></div>
            </div>
          </td>
          <td>
            @if($prev)
              <span class="text-success font-weight-bold">
                {{ number_format($prev->sales_amount, 2) }} ج.م
              </span>
              <small class="text-muted">— {{ $prev->updated_at?->format('Y-m-d') }}</small>
            @else
              <span class="text-muted">—</span>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    </div>
    </div>

    <div class="card-footer d-flex justify-content-between">
      <div>
        <button type="submit" class="btn btn-success">
          <i class="fas fa-save ml-1"></i>حفظ المبيعات
        </button>
        <a href="{{ route('commissions_v2.rules') }}" class="btn btn-secondary mr-2">رجوع</a>
      </div>
      <a href="{{ route('commissions_v2.calculate', ['month'=>$month,'year'=>$year]) }}"
         class="btn btn-primary">
        <i class="fas fa-calculator ml-1"></i>احتساب العمولات ←
      </a>
    </div>
  </form>
</div>
</div>
@endsection
