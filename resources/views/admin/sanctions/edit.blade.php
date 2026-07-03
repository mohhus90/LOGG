@extends('admin.layouts.admin')
@section('title') تعديل جزاء @endsection
@section('start') شئون الموظفين @endsection
@section('home') <a href="{{ route('sanctions.index') }}">الجزاءات</a> @endsection
@section('startpage') تعديل @endsection

@section('content')
<div class="col-md-7 mx-auto">
<div class="card card-warning">
  <div class="card-header">
    <h3 class="card-title">
      <i class="fas fa-edit ml-2"></i>
      تعديل جزاء — {{ $sanction->employee->employee_name_A ?? '' }}
    </h3>
  </div>
  <form method="POST" action="{{ route('sanctions.update', $sanction->id) }}">
    @csrf
    <div class="card-body">
      @if($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
      @endif

      <div class="alert alert-secondary py-2">
        <i class="fas fa-user ml-1"></i>
        <strong>الموظف:</strong> {{ $sanction->employee->employee_name_A ?? '-' }}
      </div>

      <div class="row">
        <div class="col-md-4 form-group">
          <label>تاريخ الجزاء <span class="text-danger">*</span></label>
          <input type="date" name="date" class="form-control" required
            value="{{ old('date', $sanction->date?->format('Y-m-d')) }}">
        </div>
        <div class="col-md-4 form-group">
          <label>نوع الجزاء <span class="text-danger">*</span></label>
          <select name="type" class="form-control" id="sanctionType" required
            onchange="toggleSanctionFields()">
            <option value="1" {{ $sanction->type==1?'selected':'' }}>تحذير</option>
            <option value="2" {{ $sanction->type==2?'selected':'' }}>إنذار رسمي</option>
            <option value="3" {{ $sanction->type==3?'selected':'' }}>خصم مالي</option>
            <option value="4" {{ $sanction->type==4?'selected':'' }}>إيقاف عن العمل</option>
            <option value="5" {{ $sanction->type==5?'selected':'' }}>خصم باليوم</option>
          </select>
        </div>
      </div>

      <div id="amountWrap" style="display:none">
        <div class="row">
          <div class="col-md-4 form-group">
            <label>المبلغ المخصوم</label>
            <div class="input-group">
              <input type="number" name="amount" class="form-control"
                step="0.01" min="0" value="{{ old('amount', $sanction->amount) }}">
              <div class="input-group-append"><span class="input-group-text">ج.م</span></div>
            </div>
          </div>
          <div class="col-md-4 form-group">
            <label>شهر الاستقطاع</label>
            <input type="month" name="deduct_month" class="form-control"
              value="{{ old('deduct_month', $sanction->deduct_month) }}">
          </div>
        </div>
      </div>

      <div id="suspensionWrap" style="display:none">
        <div class="row">
          <div class="col-md-4 form-group">
            <label>عدد أيام الإيقاف</label>
            <div class="input-group">
              <input type="number" name="suspension_days" class="form-control"
                min="1" step="1" value="{{ old('suspension_days', $sanction->suspension_days) }}">
              <div class="input-group-append"><span class="input-group-text">يوم</span></div>
            </div>
          </div>
        </div>
      </div>

      {{-- حقول خصم باليوم --}}
      <div id="dayDeductWrap" style="display:none">
        <div class="row">
          <div class="col-md-4 form-group">
            <label>عدد الأيام المخصومة</label>
            <div class="input-group">
              <input type="number" name="deduct_days" class="form-control"
                step="0.25" min="0.25" max="30"
                value="{{ old('deduct_days', $sanction->deduct_days) }}"
                placeholder="مثال: 0.5 = نصف يوم">
              <div class="input-group-append"><span class="input-group-text">يوم</span></div>
            </div>
            <small class="text-muted">يُحسب التأثير المالي من راتب الموظف في كشف الرواتب</small>
          </div>
          <div class="col-md-4 form-group">
            <label>شهر الاستقطاع من الراتب</label>
            <input type="month" name="deduct_month_day" class="form-control"
              value="{{ old('deduct_month_day', $sanction->type == 5 ? $sanction->deduct_month : today()->format('Y-m')) }}">
          </div>
        </div>
      </div>

      <div class="form-group">
        <label>وصف / سبب الجزاء</label>
        <textarea name="description" class="form-control" rows="3">{{ old('description', $sanction->description) }}</textarea>
      </div>
    </div>
    <div class="card-footer">
      <button type="submit" class="btn btn-warning">
        <i class="fas fa-save ml-1"></i> حفظ التعديلات
      </button>
      <a href="{{ route('sanctions.index') }}" class="btn btn-secondary mr-2">
        <i class="fas fa-arrow-right ml-1"></i> رجوع
      </a>
    </div>
  </form>
</div>
</div>
@endsection

@section('script')
<script>
function toggleSanctionFields() {
  var type = parseInt(document.getElementById('sanctionType').value);
  document.getElementById('amountWrap').style.display     = (type === 3) ? '' : 'none';
  document.getElementById('suspensionWrap').style.display = (type === 4) ? '' : 'none';
  document.getElementById('dayDeductWrap').style.display  = (type === 5) ? '' : 'none';
}
document.addEventListener('DOMContentLoaded', toggleSanctionFields);
</script>
@endsection
