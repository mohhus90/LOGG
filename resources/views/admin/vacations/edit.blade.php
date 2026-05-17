{{-- FILE: resources/views/admin/vacations/edit.blade.php --}}
@extends('admin.layouts.admin')
@section('title') تعديل رصيد الإجازات @endsection
@section('start') شئون الموظفين @endsection
@section('home') <a href="{{ route('vacations.index') }}">الإجازات</a> @endsection
@section('startpage') تعديل رصيد @endsection

@section('content')
<div class="col-md-7 mx-auto">
<div class="card card-warning">
  <div class="card-header">
    <h3 class="card-title">
      <i class="fas fa-edit ml-2"></i>
      تعديل رصيد إجازات: <strong>{{ $employee->employee_name_A }}</strong>
      — سنة {{ $year }}
    </h3>
  </div>

  <form action="{{ route('vacations.update', [$employee->id, $year]) }}" method="POST">
    @csrf
    <div class="card-body">
      @if($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
      @endif

      {{-- إجازة اعتيادية --}}
      <h6 class="text-success border-bottom pb-1 mb-3">
        🏖 الإجازة الاعتيادية
        <small class="text-muted">(القانون المصري: 21 يوم سنوياً)</small>
      </h6>
      <div class="row">
        <div class="col-md-4 form-group">
          <label>الرصيد الكلي (يوم)</label>
          <input type="number" name="annual_balance" class="form-control" step="0.5" min="0"
            value="{{ old('annual_balance', $balance->annual_balance ?? 21) }}"
            onchange="calcRemaining('annual')">
        </div>
        <div class="col-md-4 form-group">
          <label>المستخدم</label>
          <input type="number" name="annual_used_display" class="form-control bg-light" readonly
            value="{{ $balance->annual_used ?? 0 }}">
          <small class="text-muted">يُحتسب تلقائياً</small>
        </div>
        <div class="col-md-4 form-group">
          <label>المتبقي <span class="text-danger">*</span></label>
          <input type="number" name="annual_remaining" class="form-control" step="0.5" min="0"
            id="annualRemaining"
            value="{{ old('annual_remaining', $balance->annual_remaining ?? 21) }}">
        </div>
      </div>

      {{-- إجازة عارضة --}}
      <h6 class="text-warning border-bottom pb-1 mb-3 mt-2">
        📅 الإجازة العارضة
        <small class="text-muted">(القانون المصري: 6 أيام سنوياً)</small>
      </h6>
      <div class="row">
        <div class="col-md-4 form-group">
          <label>الرصيد الكلي (يوم)</label>
          <input type="number" name="casual_balance" class="form-control" step="0.5" min="0"
            value="{{ old('casual_balance', $balance->casual_balance ?? 6) }}"
            onchange="calcRemaining('casual')">
        </div>
        <div class="col-md-4 form-group">
          <label>المستخدم</label>
          <input type="number" name="casual_used_display" class="form-control bg-light" readonly
            value="{{ $balance->casual_used ?? 0 }}">
        </div>
        <div class="col-md-4 form-group">
          <label>المتبقي <span class="text-danger">*</span></label>
          <input type="number" name="casual_remaining" class="form-control" step="0.5" min="0"
            id="casualRemaining"
            value="{{ old('casual_remaining', $balance->casual_remaining ?? 6) }}">
        </div>
      </div>

      <div class="row">
        <div class="col-md-4 form-group">
          <label>الاستحقاق الشهري (يوم)</label>
          <input type="number" name="monthly_accrual" class="form-control" step="0.01" min="0"
            value="{{ old('monthly_accrual', $balance->monthly_accrual ?? 1.75) }}">
          <small class="text-muted">21 ÷ 12 = 1.75 يوم/شهر</small>
        </div>
      </div>

      <div class="alert alert-info py-2 mb-0">
        <i class="fas fa-info-circle ml-1"></i>
        الرصيد المتبقي يتأثر تلقائياً بطلبات الإجازة المقبولة.
        عدّل هنا فقط للتصحيح اليدوي.
      </div>
    </div>

    <div class="card-footer">
      <button type="submit" class="btn btn-warning">
        <i class="fas fa-save ml-1"></i>حفظ التعديلات
      </button>
      <a href="{{ route('vacations.index', ['year'=>$year]) }}" class="btn btn-secondary mr-2">
        رجوع
      </a>
    </div>
  </form>
</div>
</div>
@endsection

@section('js')
<script>
function calcRemaining(type) {
  // تلميح فقط — المستخدم يعدّل المتبقي يدوياً
}
</script>
@endsection
