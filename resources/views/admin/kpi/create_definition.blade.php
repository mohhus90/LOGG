{{-- FILE: resources/views/admin/kpi/create_definition.blade.php --}}
@extends('admin.layouts.admin')
@section('title') {{ isset($kpi) ? 'تعديل مؤشر' : 'إضافة مؤشر أداء' }} @endsection
@section('start') الأداء @endsection
@section('home') <a href="{{ route('kpi.definitions') }}">مؤشرات الأداء</a> @endsection
@section('startpage') {{ isset($kpi) ? 'تعديل' : 'إضافة' }} @endsection

@section('content')
<div class="col-md-9 mx-auto">
<div class="card card-{{ isset($kpi) ? 'warning' : 'primary' }}">
  <div class="card-header">
    <h3 class="card-title">
      <i class="fas fa-chart-line ml-2"></i>
      {{ isset($kpi) ? 'تعديل مؤشر: '.$kpi->name : 'إضافة مؤشر أداء جديد' }}
    </h3>
  </div>

  <form action="{{ isset($kpi) ? route('kpi.update_definition',$kpi->id) : route('kpi.store_definition') }}"
        method="POST">
    @csrf
    @if(isset($kpi)) @method('PUT') @endif

    <div class="card-body">
      @if($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
      @endif

      <div class="row">
        <div class="col-md-7 form-group">
          <label>اسم المؤشر <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control" required
            value="{{ old('name', $kpi->name ?? '') }}"
            placeholder="مثال: معدل إتمام المهام، نسبة رضا العملاء...">
        </div>
        <div class="col-md-2 form-group">
          <label>كود المؤشر <span class="text-danger">*</span></label>
          <input type="text" name="code" class="form-control" required
            value="{{ old('code', $kpi->code ?? '') }}"
            placeholder="KPI001">
        </div>
        <div class="col-md-3 form-group">
          <label>وحدة القياس</label>
          <select name="measurement_unit" class="form-control">
            <option value="%"         {{ old('measurement_unit',$kpi->measurement_unit??'%')=='%'?'selected':'' }}>% نسبة مئوية</option>
            <option value="رقم"       {{ old('measurement_unit',$kpi->measurement_unit??'')=='رقم'?'selected':'' }}>رقم / عدد</option>
            <option value="ج.م"       {{ old('measurement_unit',$kpi->measurement_unit??'')=='ج.م'?'selected':'' }}>ج.م مبلغ مالي</option>
            <option value="نقطة"      {{ old('measurement_unit',$kpi->measurement_unit??'')=='نقطة'?'selected':'' }}>نقطة</option>
            <option value="ساعة"      {{ old('measurement_unit',$kpi->measurement_unit??'')=='ساعة'?'selected':'' }}>ساعة</option>
          </select>
        </div>
      </div>

      <div class="row">
        <div class="col-md-3 form-group">
          <label>الفئة <span class="text-danger">*</span></label>
          <select name="category" class="form-control" required>
            <option value="performance" {{ old('category',$kpi->category??'performance')=='performance'?'selected':'' }}>📊 الأداء العام</option>
            <option value="quality"     {{ old('category',$kpi->category??'')=='quality'?'selected':'' }}>⭐ الجودة</option>
            <option value="attendance"  {{ old('category',$kpi->category??'')=='attendance'?'selected':'' }}>⏰ الانضباط</option>
            <option value="sales"       {{ old('category',$kpi->category??'')=='sales'?'selected':'' }}>💰 المبيعات</option>
            <option value="custom"      {{ old('category',$kpi->category??'')=='custom'?'selected':'' }}>🔧 مخصص</option>
          </select>
        </div>
        <div class="col-md-3 form-group">
          <label>القيمة المستهدفة <span class="text-danger">*</span></label>
          <input type="number" name="target_value" class="form-control" required step="0.01" min="0"
            value="{{ old('target_value', $kpi->target_value ?? 100) }}">
          <small class="text-muted">مثال: 100 للنسبة المئوية، 50 للعدد</small>
        </div>
        <div class="col-md-3 form-group">
          <label>الوزن النسبي % <span class="text-danger">*</span></label>
          <div class="input-group">
            <input type="number" name="weight" class="form-control" required step="0.01" min="0" max="100"
              id="weightInput" value="{{ old('weight', $kpi->weight ?? 100) }}"
              onchange="updateWeightBar()">
            <div class="input-group-append"><span class="input-group-text">%</span></div>
          </div>
          <div class="progress mt-1" style="height:8px">
            <div class="progress-bar bg-info" id="weightBar"
              style="width:{{ old('weight',$kpi->weight??100) }}%"></div>
          </div>
        </div>
        <div class="col-md-3 form-group">
          <label>ترتيب العرض</label>
          <input type="number" name="sort_order" class="form-control" min="0"
            value="{{ old('sort_order', $kpi->sort_order ?? 0) }}">
        </div>
      </div>

      <div class="form-group">
        <label>الوصف</label>
        <textarea name="description" class="form-control" rows="2"
          placeholder="شرح مختصر للمؤشر وكيفية قياسه">{{ old('description', $kpi->description ?? '') }}</textarea>
      </div>

      {{-- تأثير الراتب --}}
      <div class="card card-outline card-warning mt-2">
        <div class="card-header py-2">
          <h6 class="mb-0"><i class="fas fa-money-bill-wave ml-1 text-warning"></i>تأثير هذا المؤشر على الراتب</h6>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-4 form-group">
              <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="affectsSalary"
                  name="affects_salary" value="1"
                  {{ old('affects_salary',$kpi->affects_salary??0)?'checked':'' }}
                  onchange="toggleSalaryFields()">
                <label class="custom-control-label font-weight-bold" for="affectsSalary">
                  يؤثر على الراتب
                </label>
              </div>
              <small class="text-muted">عند التفعيل تُحتسب مكافأة أو خصم تلقائياً في مسير الراتب</small>
            </div>
            <div class="col-md-3 form-group">
              <label>نوع التأثير</label>
              <select name="salary_effect_type" class="form-control" id="effectType">
                <option value="bonus"     {{ old('salary_effect_type',$kpi->salary_effect_type??'bonus')=='bonus'?'selected':'' }}>مكافأة فقط</option>
                <option value="deduction" {{ old('salary_effect_type',$kpi->salary_effect_type??'')=='deduction'?'selected':'' }}>خصم فقط</option>
                <option value="both"      {{ old('salary_effect_type',$kpi->salary_effect_type??'')=='both'?'selected':'' }}>مكافأة أو خصم</option>
              </select>
            </div>
            <div class="col-md-2 form-group" id="bonusPctField">
              <label>أقصى مكافأة %</label>
              <div class="input-group">
                <input type="number" name="max_bonus_pct" class="form-control" step="0.01" min="0" max="100"
                  value="{{ old('max_bonus_pct', $kpi->max_bonus_pct ?? 0) }}">
                <div class="input-group-append"><span class="input-group-text">%</span></div>
              </div>
              <small class="text-muted">نسبة من الراتب الأساسي</small>
            </div>
            <div class="col-md-2 form-group" id="deductPctField">
              <label>أقصى خصم %</label>
              <div class="input-group">
                <input type="number" name="max_deduction_pct" class="form-control" step="0.01" min="0" max="100"
                  value="{{ old('max_deduction_pct', $kpi->max_deduction_pct ?? 0) }}">
                <div class="input-group-append"><span class="input-group-text">%</span></div>
              </div>
            </div>
          </div>

          <div class="alert alert-info py-2 mb-0" id="salaryCalcExample" style="font-size:.88em">
            <i class="fas fa-calculator ml-1"></i>
            <strong>مثال:</strong> موظف براتب 5000 ج.م، حقق 120% من الهدف → مكافأة = 5000 × (20%/100 × max_bonus%) ج.م
          </div>
        </div>
      </div>

      <div class="row mt-3">
        <div class="col-md-4 form-group">
          <label>الحالة</label>
          <select name="is_active" class="form-control">
            <option value="1" {{ old('is_active',$kpi->is_active??1)==1?'selected':'' }}>✅ نشط</option>
            <option value="0" {{ old('is_active',$kpi->is_active??1)==0?'selected':'' }}>❌ معطل</option>
          </select>
        </div>
      </div>
    </div>

    <div class="card-footer">
      <button type="submit" class="btn btn-{{ isset($kpi)?'warning':'primary' }}">
        <i class="fas fa-save ml-1"></i>{{ isset($kpi)?'حفظ التعديلات':'إضافة المؤشر' }}
      </button>
      <a href="{{ route('kpi.definitions') }}" class="btn btn-secondary mr-2">رجوع</a>
    </div>
  </form>
</div>
</div>
@endsection

@section('js')
<script>
function updateWeightBar() {
  const v = parseFloat(document.getElementById('weightInput').value) || 0;
  document.getElementById('weightBar').style.width = Math.min(v, 100) + '%';
}
function toggleSalaryFields() {
  const on = document.getElementById('affectsSalary').checked;
  document.getElementById('effectType').disabled     = !on;
  document.getElementById('bonusPctField').style.opacity = on ? '1' : '0.4';
  document.getElementById('deductPctField').style.opacity = on ? '1' : '0.4';
}
toggleSalaryFields();
</script>
@endsection
