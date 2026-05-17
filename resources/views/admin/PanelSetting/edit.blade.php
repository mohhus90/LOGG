{{-- FILE: resources/views/admin/PanelSetting/edit.blade.php — UPDATED --}}
@extends('admin.layouts.admin')
@section('title') الضبط العام @endsection
@section('start') الضبط العام @endsection
@section('home') <a href="{{ route('generalsetting.index') }}">الضبط</a> @endsection
@section('startpage') تعديل @endsection

@section('content')
<div class="col-12">
<div class="card">
  <div class="card-header">
    <h3 class="card-title">تعديل بيانات الضبط العام</h3>
  </div>

  @if(@isset($data) and !@empty($data))

<form method="POST" action="{{ route('generalsetting.update') }}" enctype="multipart/form-data">
  @csrf
  @method('PUT')
  <input type="hidden" name="id" value="{{ $data->id }}">


  {{-- <form method="GET" action="{{ route('generalsetting.update') }}" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="id" value="{{ $data->id }}"> --}}

    <div class="card-body">
      @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif
      @if(session('errorUpdate'))
        <div class="alert alert-danger">{{ session('errorUpdate') }}</div>
      @endif

      {{-- ══ بيانات الشركة ══ --}}
      <h5 class="text-primary border-bottom pb-2 mb-3">
        <i class="fas fa-building ml-2"></i>بيانات الشركة
      </h5>
      <div class="row">
        <div class="col-md-4 form-group">
          <label>اسم الشركة</label>
          <input type="text" class="form-control" name="com_name"
            value="{{ old('com_name',$data->com_name) }}">
        </div>
        <div class="col-md-4 form-group">
          <label>هاتف الشركة</label>
          <input type="text" class="form-control" name="phone"
            value="{{ old('phone',$data->phone) }}">
        </div>
        <div class="col-md-4 form-group">
          <label>ايميل الشركة</label>
          <input type="email" class="form-control" name="email"
            value="{{ old('email',$data->email) }}">
        </div>
      </div>
      <div class="row">
        <div class="col-md-6 form-group">
          <label>العنوان</label>
          <input type="text" class="form-control" name="address"
            value="{{ old('address',$data->address ?? '') }}">
        </div>
        <div class="col-md-3 form-group">
          <label>حالة النظام</label>
          <select class="form-control" name="saysem_status">
            <option value="1" {{ $data->saysem_status?'selected':'' }}>✅ مفعّل</option>
            <option value="0" {{ !$data->saysem_status?'selected':'' }}>❌ معطّل</option>
          </select>
        </div>
        <div class="col-md-3 form-group">
          <label>شعار الشركة (Logo)</label>
          <div class="d-flex align-items-center">
            @if($data->image ?? null)
              <img src="{{ asset('storage/'.$data->image) }}" alt="Logo"
                style="height:45px;margin-left:8px;border-radius:6px;border:1px solid #dee2e6">
            @endif
            <input type="file" class="form-control-file" name="logo_file" accept="image/*">
          </div>
          <small class="text-muted">PNG, JPG — حجم أقصى 2MB</small>
        </div>
      </div>

      <hr>

      {{-- ══ إعدادات التأخير والانصراف المبكر ══ --}}
      <h5 class="text-warning border-bottom pb-2 mb-3">
        <i class="fas fa-clock ml-2"></i>إعدادات التأخير والانصراف المبكر
      </h5>

      {{-- اختيار طريقة الحساب --}}
      <div class="form-group">
        <label class="font-weight-bold">طريقة احتساب التأخير والانصراف المبكر
          <span class="text-danger">*</span>
        </label>
        <div class="row mt-2">
          @php $mode = old('delay_calc_mode', $data->delay_calc_mode ?? 1); @endphp

          <div class="col-md-4">
            <div class="card card-outline card-warning {{ $mode==1?'border-warning':'' }} p-2"
              style="cursor:pointer" onclick="setMode(1)">
              <div class="custom-control custom-radio">
                <input type="radio" class="custom-control-input" name="delay_calc_mode"
                  id="mode1" value="1" {{ $mode==1?'checked':'' }} onchange="setMode(1)">
                <label class="custom-control-label font-weight-bold" for="mode1">
                  الخصم بالدقيقة
                </label>
              </div>
              <small class="text-muted mt-1">
                كل دقيقة تأخير تُخصم بقيمة ثابتة = سعر الدقيقة × عدد الدقائق
              </small>
            </div>
          </div>

          <div class="col-md-4">
            <div class="card card-outline card-danger {{ $mode==2?'border-danger':'' }} p-2"
              style="cursor:pointer" onclick="setMode(2)">
              <div class="custom-control custom-radio">
                <input type="radio" class="custom-control-input" name="delay_calc_mode"
                  id="mode2" value="2" {{ $mode==2?'checked':'' }} onchange="setMode(2)">
                <label class="custom-control-label font-weight-bold" for="mode2">
                  خصم نصف يوم/يوم بعد X مرة
                </label>
              </div>
              <small class="text-muted mt-1">
                بعد عدد محدد من مرات التأخير يُخصم نصف يوم أو يوم كامل
              </small>
            </div>
          </div>

          <div class="col-md-4">
            <div class="card card-outline card-info {{ $mode==3?'border-info':'' }} p-2"
              style="cursor:pointer" onclick="setMode(3)">
              <div class="custom-control custom-radio">
                <input type="radio" class="custom-control-input" name="delay_calc_mode"
                  id="mode3" value="3" {{ $mode==3?'checked':'' }} onchange="setMode(3)">
                <label class="custom-control-label font-weight-bold" for="mode3">
                  مدمج (هامش + دقيقة)
                </label>
              </div>
              <small class="text-muted mt-1">
                يُتجاهل هامش الدقائق الأولى ثم يُخصم بالدقيقة بعدها
              </small>
            </div>
          </div>
        </div>
      </div>

      <div class="row mt-2">
        <div class="col-md-3 form-group">
          <label>بعد كم دقيقة يُحسب تأخير حضور</label>
          <div class="input-group">
            <input type="number" class="form-control" name="after_minute_calc_delay" min="0"
              value="{{ old('after_minute_calc_delay',$data->after_minute_calc_delay) }}">
            <div class="input-group-append"><span class="input-group-text">د</span></div>
          </div>
          <small class="text-muted">0 = أي تأخير مهما كان يُحتسب</small>
        </div>
        <div class="col-md-3 form-group">
          <label>بعد كم دقيقة يُحسب انصراف مبكر</label>
          <div class="input-group">
            <input type="number" class="form-control" name="after_minute_calc_early" min="0"
              value="{{ old('after_minute_calc_early',$data->after_minute_calc_early) }}">
            <div class="input-group-append"><span class="input-group-text">د</span></div>
          </div>
        </div>

        {{-- حقول خاصة بالوضع 1 (بالدقيقة) --}}
        <div class="col-md-3 form-group mode-field mode-1" id="minuteRateField">
          <label class="text-warning font-weight-bold">
            سعر الدقيقة للخصم
            <i class="fas fa-info-circle text-muted" title="يُضرب في عدد دقائق التأخير"></i>
          </label>
          <div class="input-group">
            <input type="number" class="form-control" name="sanctions_value_minute_delay"
              step="0.01" min="0"
              value="{{ old('sanctions_value_minute_delay',$data->sanctions_value_minute_delay ?? 0) }}">
            <div class="input-group-append"><span class="input-group-text">ج.م/د</span></div>
          </div>
          <small class="text-muted">
            مثال: 0 = يُحتسب تلقائياً (سعر اليوم÷8÷60) | 2 = 2 ج.م لكل دقيقة
          </small>
        </div>

        {{-- حقول خاصة بالوضع 2 (نصف يوم/يوم) --}}
        <div class="col-md-3 form-group mode-field mode-2">
          <label>بعد كم مرة تأخير يُخصم نصف يوم</label>
          <input type="number" class="form-control" name="after_time_half_daycut" min="0"
            value="{{ old('after_time_half_daycut',$data->after_time_half_daycut) }}">
          <small class="text-muted">0 = معطّل</small>
        </div>
      </div>

      <div class="row" id="modeExtraFields">
        <div class="col-md-3 form-group mode-field mode-2">
          <label>بعد كم مرة تأخير يُخصم يوم كامل</label>
          <input type="number" class="form-control" name="after_time_allday_daycut" min="0"
            value="{{ old('after_time_allday_daycut',$data->after_time_allday_daycut) }}">
          <small class="text-muted">0 = معطّل</small>
        </div>
        <div class="col-md-3 form-group mode-field mode-2 mode-3">
          <label>بعد كم دقيقة مجتمعة يُخصم ربع يوم</label>
          <input type="number" class="form-control" name="after_minute_quarterday" min="0"
            value="{{ old('after_minute_quarterday',$data->after_minute_quarterday) }}">
        </div>
      </div>

      <hr>

      {{-- ══ إعدادات الإجازات ══ --}}
      <h5 class="text-success border-bottom pb-2 mb-3">
        <i class="fas fa-umbrella-beach ml-2"></i>إعدادات الإجازات (القانون المصري)
      </h5>
      <div class="row">
        <div class="col-md-3 form-group">
          <label>رصيد الإجازة الاعتيادية السنوي (يوم)</label>
          <input type="number" class="form-control" name="annual_vacation_days" step="0.5" min="0"
            value="{{ old('annual_vacation_days', $data->annual_vacation_days ?? 21) }}">
          <small class="text-muted">القانون المصري: 21 يوم</small>
        </div>
        <div class="col-md-3 form-group">
          <label>رصيد الإجازة العارضة السنوي (يوم)</label>
          <input type="number" class="form-control" name="casual_vacation_days" step="0.5" min="0"
            value="{{ old('casual_vacation_days', $data->casual_vacation_days ?? 6) }}">
          <small class="text-muted">القانون المصري: 6 أيام</small>
        </div>
        <div class="col-md-3 form-group">
          <label>رصيد الإجازة الشهري (ينزل تلقائياً)</label>
          <input type="number" class="form-control" name="monthly_vacation_balance"
            step="0.01" min="0"
            value="{{ old('monthly_vacation_balance',$data->monthly_vacation_balance) }}">
          <small class="text-muted">21÷12 = 1.75 يوم شهرياً</small>
        </div>
        {{-- ══ تعديل ══ --}}
        <div class="col-md-3 form-group">
          <label>رصيد أول المدة لبداية الإجازة</label>
          <input type="number" class="form-control" name="first_balance_begain_vacation" step="0.5" min="0"
            value="{{ old('first_balance_begain_vacation', $data->first_balance_begain_vacation ?? 0) }}">
        </div>

        <div class="col-md-3 form-group">
          <label>بعد كم يوم ينزل رصيد الإجازة</label>
          <input type="number" class="form-control" name="after_days_begain_vacation" min="0"
            value="{{ old('after_days_begain_vacation',$data->after_days_begain_vacation) }}">
          <small class="text-muted">مثال: 180 يوم (6 أشهر)</small>
        </div>
      </div>

      <hr>

      {{-- ══ إعدادات الغياب ══ --}}
      <h5 class="text-danger border-bottom pb-2 mb-3">
        <i class="fas fa-user-slash ml-2"></i>عقوبات الغياب المتكرر
      </h5>
      <div class="row">
        @foreach([
          ['sanctions_value_first_abcence',  'أول مرة غياب — يُخصم'],
          ['sanctions_value_second_abcence', 'ثاني مرة غياب — يُخصم'],
          ['sanctions_value_third_abcence',  'ثالث مرة غياب — يُخصم'],
          ['sanctions_value_forth_abcence',  'رابع مرة غياب فأكثر — يُخصم'],
        ] as [$field, $label])
        <div class="col-md-3 form-group">
          <label>{{ $label }}</label>
          <div class="input-group">
            <input type="number" class="form-control" name="{{ $field }}"
              step="0.01" min="0"
              value="{{ old($field,$data->$field) }}">
            <div class="input-group-append"><span class="input-group-text">× سعر اليوم</span></div>
          </div>
          <small class="text-muted">1 = يوم، 2 = يومان، 0.5 = نصف يوم</small>
        </div>
        @endforeach
      </div>

    </div>{{-- end card-body --}}

    <div class="card-footer">
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-save ml-1"></i>حفظ الإعدادات
      </button>
    </div>
  </form>
  @else
    <div class="card-body">
      <div class="alert alert-warning">
        لا توجد بيانات ضبط. <a href="{{ route('generalsetting.create') }}">أنشئ ضبطاً جديداً</a>
      </div>
    </div>
  @endif
</div>
</div>
@endsection

@section('js')
<script>
function setMode(mode) {
  // تحديث اختيار الراديو
  document.getElementById('mode'+mode).checked = true;

  // إظهار/إخفاء الحقول حسب الوضع
  document.querySelectorAll('.mode-field').forEach(el => el.style.display = 'none');
  document.querySelectorAll('.mode-' + mode).forEach(el => el.style.display = '');

  // تحديث تصميم البطاقات
  document.querySelectorAll('[onclick^="setMode"]').forEach(card => {
    card.style.opacity = '0.6';
  });
  event.currentTarget.style.opacity = '1';
}

// تطبيق الوضع الحالي عند التحميل
document.addEventListener('DOMContentLoaded', function () {
  const currentMode = {{ old('delay_calc_mode', $data->delay_calc_mode ?? 1) }};
  // إخفاء كل الحقول الخاصة أولاً
  document.querySelectorAll('.mode-field').forEach(el => el.style.display = 'none');
  // إظهار الوضع الحالي
  document.querySelectorAll('.mode-' + currentMode).forEach(el => el.style.display = '');
});
</script>
@endsection
