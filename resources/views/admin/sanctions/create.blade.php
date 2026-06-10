@extends('admin.layouts.admin')
@section('title') إضافة جزاء @endsection
@section('start') شئون الموظفين @endsection
@section('home') <a href="{{ route('sanctions.index') }}">الجزاءات</a> @endsection
@section('startpage') إضافة @endsection

@section('content')
<div class="col-md-7 mx-auto">
<div class="card card-danger">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-gavel ml-2"></i>إضافة جزاء جديد</h3>
  </div>
  <form method="POST" action="{{ route('sanctions.store') }}">
    @csrf
    <div class="card-body">
      @if($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
      @endif

      <div class="row">
        <div class="col-md-6 form-group">
          <label>الموظف <span class="text-danger">*</span></label>
          <select name="employee_id" class="form-control select2" required>
            <option value="">-- اختر الموظف --</option>
            @foreach($employees as $emp)
              <option value="{{ $emp->id }}" {{ old('employee_id')==$emp->id?'selected':'' }}>
                {{ $emp->employee_name_A }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3 form-group">
          <label>تاريخ الجزاء <span class="text-danger">*</span></label>
          <input type="date" name="date" class="form-control" required
            value="{{ old('date', today()->format('Y-m-d')) }}">
        </div>
        <div class="col-md-3 form-group">
          <label>نوع الجزاء <span class="text-danger">*</span></label>
          <select name="type" class="form-control" id="sanctionType" required
            onchange="toggleSanctionFields()">
            <option value="">-- اختر --</option>
            <option value="1" {{ old('type')==1?'selected':'' }}>تحذير</option>
            <option value="2" {{ old('type')==2?'selected':'' }}>إنذار رسمي</option>
            <option value="3" {{ old('type')==3?'selected':'' }}>خصم مالي</option>
            <option value="4" {{ old('type')==4?'selected':'' }}>إيقاف عن العمل</option>
            <option value="5" {{ old('type')==5?'selected':'' }}>خصم باليوم</option>
          </select>
        </div>
      </div>

      {{-- حقول الخصم المالي --}}
      <div id="amountWrap" style="display:none">
        <div class="row">
          <div class="col-md-4 form-group">
            <label>المبلغ المخصوم <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="number" name="amount" class="form-control"
                step="0.01" min="0" value="{{ old('amount') }}" placeholder="0.00">
              <div class="input-group-append"><span class="input-group-text">ج.م</span></div>
            </div>
          </div>
          <div class="col-md-4 form-group">
            <label>شهر الاستقطاع من الراتب</label>
            <input type="month" name="deduct_month" class="form-control"
              value="{{ old('deduct_month', today()->format('Y-m')) }}">
            <small class="text-muted">الشهر الذي يُستقطع منه المبلغ</small>
          </div>
        </div>
      </div>

      {{-- حقول الإيقاف --}}
      <div id="suspensionWrap" style="display:none">
        <div class="row">
          <div class="col-md-4 form-group">
            <label>عدد أيام الإيقاف <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="number" name="suspension_days" class="form-control"
                min="1" step="1" value="{{ old('suspension_days', 1) }}">
              <div class="input-group-append"><span class="input-group-text">يوم</span></div>
            </div>
          </div>
        </div>
      </div>

      {{-- حقول خصم باليوم --}}
      <div id="dayDeductWrap" style="display:none">
        <div class="row">
          <div class="col-md-4 form-group">
            <label>عدد الأيام المخصومة <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="number" name="deduct_days" class="form-control"
                step="0.25" min="0.25" max="30" value="{{ old('deduct_days', 1) }}"
                placeholder="مثال: 0.5 = نصف يوم، 1 = يوم كامل">
              <div class="input-group-append"><span class="input-group-text">يوم</span></div>
            </div>
            <small class="text-muted">يُحسب التأثير المالي تلقائياً من راتب الموظف في الرواتب</small>
          </div>
          <div class="col-md-4 form-group">
            <label>شهر الاستقطاع من الراتب</label>
            <input type="month" name="deduct_month_day" class="form-control"
              value="{{ old('deduct_month_day', today()->format('Y-m')) }}">
            <small class="text-muted">الشهر الذي يُستقطع منه</small>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label>وصف / سبب الجزاء</label>
        <textarea name="description" class="form-control" rows="3"
          placeholder="تفاصيل سبب الجزاء...">{{ old('description') }}</textarea>
      </div>
    </div>
    <div class="card-footer">
      <button type="submit" class="btn btn-danger">
        <i class="fas fa-save ml-1"></i> حفظ الجزاء
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
  document.getElementById('amountWrap').style.display    = (type === 3) ? '' : 'none';
  document.getElementById('suspensionWrap').style.display = (type === 4) ? '' : 'none';
  document.getElementById('dayDeductWrap').style.display  = (type === 5) ? '' : 'none';
}
document.addEventListener('DOMContentLoaded', toggleSanctionFields);
</script>
@endsection
