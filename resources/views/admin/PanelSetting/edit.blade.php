@extends('admin.layouts.admin')
@section('title') تعديل الضبط العام @endsection
@section('start') الضبط العام @endsection
@section('home') <a href="{{ route('generalsetting.index') }}">الضبط العام</a> @endsection
@section('startpage') تعديل @endsection

@section('content')
<div class="col-12">
<div class="card">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-cog ml-2"></i>تعديل إعدادات الشركة</h3>
  </div>

@if(!isset($data) || empty($data))
  <div class="card-body">
    <div class="alert alert-warning">
      لا توجد بيانات ضبط.
      <a href="{{ route('generalsetting.create') }}" class="btn btn-success btn-sm ml-2">إنشاء الضبط الآن</a>
    </div>
  </div>
@else

<form method="POST" action="{{ route('generalsetting.update') }}" enctype="multipart/form-data">
  @csrf
  @method('PUT')
  <input type="hidden" name="id" value="{{ $data->id }}">

  <div class="card-body">

    @if(session('success'))
      <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {{ session('success') }}
      </div>
    @endif
    @if(session('errorUpdate'))
      <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {{ session('errorUpdate') }}
      </div>
    @endif

    {{-- ══ بيانات الشركة ══ --}}
    <h5 class="section-title">
      <i class="fas fa-building ml-2"></i>بيانات الشركة
    </h5>
    <div class="row">
      <div class="col-md-4 form-group">
        <label>اسم الشركة <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="com_name"
          value="{{ old('com_name', $data->com_name) }}" required>
      </div>
      <div class="col-md-4 form-group">
        <label>هاتف الشركة</label>
        <input type="text" class="form-control" name="phone"
          value="{{ old('phone', $data->phone) }}" placeholder="01xxxxxxxxx">
      </div>
      <div class="col-md-4 form-group">
        <label>البريد الإلكتروني</label>
        <input type="email" class="form-control" name="email"
          value="{{ old('email', $data->email) }}">
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 form-group">
        <label>العنوان</label>
        <input type="text" class="form-control" name="address"
          value="{{ old('address', $data->address) }}">
      </div>
      <div class="col-md-2 form-group">
        <label>حالة النظام</label>
        <select class="form-control" name="saysem_status">
          <option value="1" {{ ($data->saysem_status ?? 1) == 1 ? 'selected' : '' }}>✅ مفعّل</option>
          <option value="0" {{ ($data->saysem_status ?? 1) == 0 ? 'selected' : '' }}>❌ معطّل</option>
        </select>
      </div>
      <div class="col-md-4 form-group">
        <label>شعار الشركة (Logo)</label>
        <div class="d-flex align-items-center">
          @if($data->image && file_exists(storage_path('app/public/' . $data->image)))
            <img src="{{ request()->getSchemeAndHttpHost() . request()->getBasePath() . '/public/storage/' . $data->image }}"
              alt="Logo" style="height:50px;margin-left:10px;border-radius:6px;
              border:1px solid #dee2e6;object-fit:contain;padding:2px">
          @endif
        </div>
        <input type="file" name="logo_file" class="form-control-file mt-1" accept="image/*"
          onchange="previewLogo(this)">
        <small class="text-muted">PNG, JPG, SVG — أقصى 2MB</small>
        <div id="logoPreview" class="mt-1"></div>
      </div>
    </div>

    <hr>

    {{-- ══ إعدادات التأخير ══ --}}
    <h5 class="section-title">
      <i class="fas fa-clock ml-2"></i>إعدادات التأخير والانصراف المبكر
    </h5>
    <div class="row">
      <div class="col-md-3 form-group">
        <label>بعد كم دقيقة يُحسب تأخير الحضور</label>
        <div class="input-group">
          <input type="number" class="form-control" name="after_minute_calc_delay"
            step="0.01" min="0"
            value="{{ old('after_minute_calc_delay', $data->after_minute_calc_delay ?? 0) }}">
          <div class="input-group-append"><span class="input-group-text">د</span></div>
        </div>
        <small class="text-muted">0 = أي تأخير يُحتسب</small>
      </div>
      <div class="col-md-3 form-group">
        <label>بعد كم دقيقة يُحسب انصراف مبكر</label>
        <div class="input-group">
          <input type="number" class="form-control" name="after_minute_calc_early"
            step="0.01" min="0"
            value="{{ old('after_minute_calc_early', $data->after_minute_calc_early ?? 0) }}">
          <div class="input-group-append"><span class="input-group-text">د</span></div>
        </div>
      </div>
      <div class="col-md-3 form-group">
        <label>قيمة خصم الدقيقة (تأخير/مبكر)</label>
        <div class="input-group">
          <input type="number" class="form-control" name="sanctions_value_minute_delay"
            step="0.01" min="0"
            value="{{ old('sanctions_value_minute_delay', $data->sanctions_value_minute_delay ?? 0) }}">
          <div class="input-group-append"><span class="input-group-text">ج.م</span></div>
        </div>
        <small class="text-muted">0 = يُحتسب تلقائياً من الراتب</small>
      </div>
      <div class="col-md-3 form-group">
        <label>طريقة احتساب التأخير</label>
        <select class="form-control" name="delay_calc_mode">
          <option value="1" {{ ($data->delay_calc_mode ?? 1) == 1 ? 'selected' : '' }}>بالدقيقة</option>
          <option value="2" {{ ($data->delay_calc_mode ?? 1) == 2 ? 'selected' : '' }}>نصف/يوم بعد X مرة</option>
          <option value="3" {{ ($data->delay_calc_mode ?? 1) == 3 ? 'selected' : '' }}>مدمج (دقيقة + مرات)</option>
        </select>
      </div>
    </div>

    <div class="row">
      <div class="col-md-3 form-group">
        <label>بعد كم دقيقة مجموعة يُخصم ربع يوم</label>
        <div class="input-group">
          <input type="number" class="form-control" name="after_minute_quarterday"
            step="0.01" min="0"
            value="{{ old('after_minute_quarterday', $data->after_minute_quarterday ?? 0) }}">
          <div class="input-group-append"><span class="input-group-text">د</span></div>
        </div>
        <small class="text-muted">0 = معطّل</small>
      </div>
      <div class="col-md-3 form-group">
        <label>بعد كم مرة يُخصم نصف يوم</label>
        <div class="input-group">
          <input type="number" class="form-control" name="after_time_half_daycut"
            step="0.01" min="0"
            value="{{ old('after_time_half_daycut', $data->after_time_half_daycut ?? 0) }}">
          <div class="input-group-append"><span class="input-group-text">مرة</span></div>
        </div>
        <small class="text-muted">0 = معطّل</small>
      </div>
      <div class="col-md-3 form-group">
        <label>بعد كم مرة يُخصم يوم كامل</label>
        <div class="input-group">
          <input type="number" class="form-control" name="after_time_allday_daycut"
            step="0.01" min="0"
            value="{{ old('after_time_allday_daycut', $data->after_time_allday_daycut ?? 0) }}">
          <div class="input-group-append"><span class="input-group-text">مرة</span></div>
        </div>
        <small class="text-muted">0 = معطّل</small>
      </div>
    </div>

    <hr>

    {{-- ══ إعدادات الأوفرتايم ══ --}}
    <h5 class="section-title">
      <i class="fas fa-business-time ml-2"></i>إعدادات الأوفرتايم (العمل الإضافي)
    </h5>
    <div class="alert alert-info py-2">
      <i class="fas fa-info-circle ml-1"></i>
      مضاعف الأوفرتايم يُطبَّق على الشركة كلها. يمكن تخصيص قيمة مختلفة لكل موظف من صفحة الموظف.
    </div>
    <div class="row">
      <div class="col-md-4 form-group">
        <label>مضاعف الأوفرتايم</label>
        <div class="input-group">
          <input type="number" class="form-control" name="overtime_multiplier"
            step="0.01" min="0" max="10"
            value="{{ old('overtime_multiplier', $data->overtime_multiplier ?? 1.5) }}">
          <div class="input-group-append"><span class="input-group-text">× سعر الساعة</span></div>
        </div>
        <small class="text-muted">1.5 = مرة ونصف | 2 = مرتين | 0 = بدون أوفرتايم</small>
      </div>
    </div>

    <hr>

    {{-- ══ نظام التأمينات الاجتماعية ══ --}}
    <h5 class="section-title">
      <i class="fas fa-shield-alt ml-2"></i>نظام التأمينات الاجتماعية (القانون المصري)
    </h5>
    <div class="alert alert-warning py-2">
      <i class="fas fa-balance-scale ml-1"></i>
      <strong>الافتراضي وفق القانون المصري:</strong>
      نسبة الموظف 11% — نسبة الشركة 18.75% — يُطبَّق على راتب التأمين الخاص بكل موظف
    </div>
    <div class="row">
      <div class="col-md-4 form-group">
        <label>نسبة اشتراك الموظف في التأمينات</label>
        <div class="input-group">
          <input type="number" class="form-control" name="employee_insurance_rate"
            step="0.01" min="0" max="100"
            value="{{ old('employee_insurance_rate', $data->employee_insurance_rate ?? 11.00) }}">
          <div class="input-group-append"><span class="input-group-text">%</span></div>
        </div>
        <small class="text-muted">تُخصم من راتب التأمين للموظف</small>
      </div>
      <div class="col-md-4 form-group">
        <label>نسبة اشتراك الشركة في التأمينات</label>
        <div class="input-group">
          <input type="number" class="form-control" name="company_insurance_rate"
            step="0.01" min="0" max="100"
            value="{{ old('company_insurance_rate', $data->company_insurance_rate ?? 18.75) }}">
          <div class="input-group-append"><span class="input-group-text">%</span></div>
        </div>
        <small class="text-muted">تتحملها الشركة عن كل موظف</small>
      </div>
      <div class="col-md-4 form-group">
        <label>إجمالي النسبة المدفوعة للتأمينات</label>
        <div class="input-group">
          <input type="text" class="form-control bg-light" readonly
            id="total_insurance_rate"
            value="{{ number_format(($data->employee_insurance_rate ?? 11) + ($data->company_insurance_rate ?? 18.75), 2) }}%">
        </div>
        <small class="text-muted">موظف + شركة</small>
      </div>
    </div>

    <hr>

    {{-- ══ إعدادات الإجازات ══ --}}
    <h5 class="section-title">
      <i class="fas fa-umbrella-beach ml-2"></i>إعدادات الإجازات
    </h5>
    <div class="alert alert-info py-2">
      <i class="fas fa-balance-scale ml-1"></i>
      <strong>القانون المصري:</strong>
      الموظف العادي (أقل من 10 سنوات): 21 يوم |
      الموظف ذو الخبرة (10 سنوات فأكثر): 30 يوم |
      فوق 50 سنة: 30 يوم
    </div>
    <div class="row">
      <div class="col-md-3 form-group">
        <label>رصيد الإجازة الاعتيادية السنوية (يوم)</label>
        <div class="input-group">
          <input type="number" class="form-control" name="annual_vacation_days"
            step="0.5" min="0"
            value="{{ old('annual_vacation_days', $data->annual_vacation_days ?? 21) }}">
          <div class="input-group-append"><span class="input-group-text">يوم</span></div>
        </div>
        <small class="text-muted">افتراضي: 21 يوم</small>
      </div>
      <div class="col-md-3 form-group">
        <label>رصيد الإجازة العارضة السنوية (يوم)</label>
        <div class="input-group">
          <input type="number" class="form-control" name="casual_vacation_days"
            step="0.5" min="0"
            value="{{ old('casual_vacation_days', $data->casual_vacation_days ?? 6) }}">
          <div class="input-group-append"><span class="input-group-text">يوم</span></div>
        </div>
        <small class="text-muted">افتراضي: 6 أيام</small>
      </div>
      <div class="col-md-3 form-group">
        <label>رصيد الإجازة الشهري (ينزل تلقائياً)</label>
        <div class="input-group">
          <input type="number" class="form-control" name="monthly_vacation_balance"
            step="0.01" min="0"
            value="{{ old('monthly_vacation_balance', $data->monthly_vacation_balance ?? 1.75) }}">
          <div class="input-group-append"><span class="input-group-text">يوم</span></div>
        </div>
        <small class="text-muted">21÷12 = 1.75 يوم/شهر</small>
      </div>
    </div>
    <div class="row">
      <div class="col-md-3 form-group">
        <label>رصيد أول المدة لبداية الإجازة</label>
        <div class="input-group">
          <input type="number" class="form-control" name="first_balance_begain_vacation"
            step="0.01" min="0"
            value="{{ old('first_balance_begain_vacation', $data->first_balance_begain_vacation ?? 0) }}">
          <div class="input-group-append"><span class="input-group-text">يوم</span></div>
        </div>
      </div>
      <div class="col-md-3 form-group">
        <label>بعد كم يوم ينزل رصيد الإجازة</label>
        <div class="input-group">
          <input type="number" class="form-control" name="after_days_begain_vacation"
            step="0.01" min="0"
            value="{{ old('after_days_begain_vacation', $data->after_days_begain_vacation ?? 0) }}">
          <div class="input-group-append"><span class="input-group-text">يوم</span></div>
        </div>
        <small class="text-muted">مثال: 180 يوم (6 أشهر من تاريخ التعيين)</small>
      </div>
    </div>

    <hr>

    {{-- ══ عقوبات الغياب ══ --}}
    <h5 class="section-title">
      <i class="fas fa-user-slash ml-2"></i>عقوبات الغياب المتكرر
    </h5>
    <div class="alert alert-warning py-2">
      <i class="fas fa-info-circle ml-1"></i>
      القيمة = مضاعف سعر اليوم. مثال: 1 = يوم كامل، 2 = يومان، 0.5 = نصف يوم
    </div>
    <div class="row">
      @foreach([
        ['sanctions_value_first_abcence',  'أول مرة غياب'],
        ['sanctions_value_second_abcence', 'ثاني مرة غياب'],
        ['sanctions_value_third_abcence',  'ثالث مرة غياب'],
        ['sanctions_value_forth_abcence',  'رابع مرة فأكثر'],
      ] as [$field, $label])
      <div class="col-md-3 form-group">
        <label>{{ $label }} — يُخصم</label>
        <div class="input-group">
          <input type="number" class="form-control" name="{{ $field }}"
            step="0.01" min="0"
            value="{{ old($field, $data->$field ?? 0) }}">
          <div class="input-group-append"><span class="input-group-text">× يوم</span></div>
        </div>
      </div>
      @endforeach
    </div>

  </div>{{-- end card-body --}}

  <div class="card-footer">
    <button type="submit" class="btn btn-primary">
      <i class="fas fa-save ml-1"></i> حفظ الإعدادات
    </button>
    <a href="{{ route('generalsetting.index') }}" class="btn btn-secondary mr-2">
      <i class="fas fa-eye ml-1"></i> عرض
    </a>
  </div>
</form>

@endif
</div>
</div>
@endsection

@section('script')
<script>
function previewLogo(input) {
  var preview = document.getElementById('logoPreview');
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) {
      preview.innerHTML = '<img src="' + e.target.result +
        '" style="height:55px;border-radius:6px;border:1px solid #dee2e6;margin-top:4px">';
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function updateTotalInsurance() {
  var emp = parseFloat(document.querySelector('[name=employee_insurance_rate]').value) || 0;
  var com = parseFloat(document.querySelector('[name=company_insurance_rate]').value) || 0;
  document.getElementById('total_insurance_rate').value = (emp + com).toFixed(2) + '%';
}

document.addEventListener('DOMContentLoaded', function() {
  document.querySelector('[name=employee_insurance_rate]').addEventListener('input', updateTotalInsurance);
  document.querySelector('[name=company_insurance_rate]').addEventListener('input', updateTotalInsurance);
});
</script>
@endsection
