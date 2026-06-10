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
    <h5 class="section-title"><i class="fas fa-building ml-2"></i>بيانات الشركة</h5>
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
              alt="Logo" style="height:50px;margin-left:10px;border-radius:6px;border:1px solid #dee2e6;object-fit:contain;padding:2px">
          @endif
        </div>
        <input type="file" name="logo_file" class="form-control-file mt-1" accept="image/*"
          onchange="previewLogo(this)">
        <small class="text-muted">PNG, JPG, SVG — أقصى 2MB</small>
        <div id="logoPreview" class="mt-1"></div>
      </div>
    </div>

    <hr>

    {{-- ══ إعدادات التأخير والانصراف المبكر ══ --}}
    <h5 class="section-title"><i class="fas fa-clock ml-2"></i>إعدادات التأخير والانصراف المبكر</h5>

    <div class="row">
      <div class="col-md-3 form-group">
        <label>بعد كم دقيقة يُحسب تأخير الحضور</label>
        <div class="input-group">
          <input type="number" class="form-control" name="after_minute_calc_delay"
            step="0.01" min="0"
            value="{{ old('after_minute_calc_delay', $data->after_minute_calc_delay ?? 0) }}">
          <div class="input-group-append"><span class="input-group-text">د</span></div>
        </div>
        <small class="text-muted">0 = أي تأخير يُحتسب — الدقائق المسموحة لا تُحتسب</small>
      </div>
      <div class="col-md-3 form-group">
        <label>بعد كم دقيقة يُحسب انصراف مبكر</label>
        <div class="input-group">
          <input type="number" class="form-control" name="after_minute_calc_early"
            step="0.01" min="0"
            value="{{ old('after_minute_calc_early', $data->after_minute_calc_early ?? 0) }}">
          <div class="input-group-append"><span class="input-group-text">د</span></div>
        </div>
        <small class="text-muted">0 = أي انصراف مبكر يُحتسب</small>
      </div>
      <div class="col-md-3 form-group">
        <label>طريقة احتساب التأخير والانصراف المبكر</label>
        <select class="form-control" name="delay_calc_mode" id="delayCalcMode"
          onchange="toggleDelayMode()">
          <option value="1" {{ ($data->delay_calc_mode ?? 1) == 1 ? 'selected' : '' }}>
            وضع 1 — دقيقة × مضاعف
          </option>
          <option value="2" {{ ($data->delay_calc_mode ?? 1) == 2 ? 'selected' : '' }}>
            وضع 2 — جزء اليوم (ربع / نصف / يوم)
          </option>
          <option value="3" {{ ($data->delay_calc_mode ?? 1) == 3 ? 'selected' : '' }}>
            وضع 3 — هرمي مدمج (دقيقة ثم جزء اليوم)
          </option>
        </select>
        <small class="text-muted">
          وضع 1: كل دقيقة تأخير × مضاعف<br>
          وضع 2: يُخصم ربع/نصف/يوم حسب الحدود<br>
          وضع 3: أقل من X دقيقة × مضاعف، ثم الحدود
        </small>
      </div>
      {{-- مضاعف الدقيقة: يظهر في وضع 1 و 3 --}}
      <div class="col-md-3 form-group" id="sanctionsMinuteWrap">
        <label>مضاعف خصم الدقيقة (وضع 1 / المرحلة الأولى في وضع 3)</label>
        <div class="input-group">
          <input type="number" class="form-control" name="sanctions_value_minute_delay"
            step="0.01" min="0"
            value="{{ old('sanctions_value_minute_delay', $data->sanctions_value_minute_delay ?? 0) }}">
          <div class="input-group-append"><span class="input-group-text">×</span></div>
        </div>
        <small class="text-muted">
          0 = احتساب تلقائي من الراتب (×1)<br>
          مثال: 2 = دقيقة التأخير تُخصم ضعفين
        </small>
      </div>
    </div>

    {{-- حدود جزء اليوم: يظهر في وضع 2 و 3 --}}
    <div id="dayFractionWrap" style="{{ in_array($data->delay_calc_mode ?? 1, [2,3]) ? '' : 'display:none' }}">
      <div class="row">
        {{-- وضع 3 فقط: حد المرحلة الأولى --}}
        <div class="col-md-3 form-group" id="tier1Wrap">
          <label>حد المرحلة الأولى (وضع 3 فقط)</label>
          <div class="input-group">
            <input type="number" class="form-control" name="delay_tier1_minutes"
              step="1" min="0"
              value="{{ old('delay_tier1_minutes', $data->delay_tier1_minutes ?? 0) }}">
            <div class="input-group-append"><span class="input-group-text">د</span></div>
          </div>
          <small class="text-muted">التأخير أقل من هذا: دقيقة × مضاعف. مثال: 15</small>
        </div>
        <div class="col-md-3 form-group">
          <label>حد ربع اليوم</label>
          <div class="input-group">
            <input type="number" class="form-control" name="after_minute_quarterday"
              step="1" min="0"
              value="{{ old('after_minute_quarterday', $data->after_minute_quarterday ?? 0) }}">
            <div class="input-group-append"><span class="input-group-text">د</span></div>
          </div>
          <small class="text-muted">تأخير ≥ هذا → ربع يوم. مثال: 15</small>
        </div>
        <div class="col-md-3 form-group">
          <label>حد نصف اليوم</label>
          <div class="input-group">
            <input type="number" class="form-control" name="delay_halfday_minutes"
              step="1" min="0"
              value="{{ old('delay_halfday_minutes', $data->delay_halfday_minutes ?? 0) }}">
            <div class="input-group-append"><span class="input-group-text">د</span></div>
          </div>
          <small class="text-muted">تأخير ≥ هذا → نصف يوم. مثال: 30</small>
        </div>
        <div class="col-md-3 form-group">
          <label>حد اليوم الكامل</label>
          <div class="input-group">
            <input type="number" class="form-control" name="delay_fullday_minutes"
              step="1" min="0"
              value="{{ old('delay_fullday_minutes', $data->delay_fullday_minutes ?? 0) }}">
            <div class="input-group-append"><span class="input-group-text">د</span></div>
          </div>
          <small class="text-muted">تأخير ≥ هذا → يوم كامل. مثال: 60</small>
        </div>
      </div>
      <div class="alert alert-info py-2">
        <i class="fas fa-info-circle ml-1"></i>
        <strong>مثال اللائحة:</strong>
        وضع 3 — حد المرحلة الأولى = 15د | ربع يوم = 15د | نصف يوم = 30د | يوم كامل = 60د | مضاعف المرحلة الأولى = 2
      </div>
    </div>

    {{-- ══ حدود الانصراف المبكر ══ --}}
    <h5 class="section-title mt-2"><i class="fas fa-sign-out-alt ml-2"></i>حدود خصم الانصراف المبكر</h5>
    <div class="alert alert-info py-2">
      <i class="fas fa-info-circle ml-1"></i>
      هذه الحدود مستقلة عن وضع التأخير وتتجاوزه. إذا الانصراف المبكر ≥ الحد → يُطبَّق الخصم المحدد.
      <strong>عدم إتمام اليوم = يوم + نصف (1.5 يوم)</strong> مثل اللائحة.
      اتركها 0 لاستخدام نفس وضع التأخير.
    </div>
    <div class="row">
      <div class="col-md-3 form-group">
        <label>حد نصف يوم (انصراف مبكر)</label>
        <div class="input-group">
          <input type="number" class="form-control" name="early_departure_halfday_minutes"
            step="1" min="0"
            value="{{ old('early_departure_halfday_minutes', $data->early_departure_halfday_minutes ?? 0) }}">
          <div class="input-group-append"><span class="input-group-text">د</span></div>
        </div>
        <small class="text-muted">0 = معطّل</small>
      </div>
      <div class="col-md-3 form-group">
        <label>حد يوم كامل (انصراف مبكر)</label>
        <div class="input-group">
          <input type="number" class="form-control" name="early_departure_fullday_minutes"
            step="1" min="0"
            value="{{ old('early_departure_fullday_minutes', $data->early_departure_fullday_minutes ?? 0) }}">
          <div class="input-group-append"><span class="input-group-text">د</span></div>
        </div>
        <small class="text-muted">0 = معطّل</small>
      </div>
      <div class="col-md-3 form-group">
        <label>حد يوم + نصف (عدم إتمام اليوم)</label>
        <div class="input-group">
          <input type="number" class="form-control" name="early_departure_fullplushalf_minutes"
            step="1" min="0"
            value="{{ old('early_departure_fullplushalf_minutes', $data->early_departure_fullplushalf_minutes ?? 0) }}">
          <div class="input-group-append"><span class="input-group-text">د</span></div>
        </div>
        <small class="text-muted">0 = معطّل. مثال: 240 = خروج قبل 4 ساعات = يوم+نصف</small>
      </div>
    </div>

    <hr>

    {{-- ══ إعدادات الأوفرتايم ══ --}}
    <h5 class="section-title"><i class="fas fa-business-time ml-2"></i>إعدادات الأوفرتايم (العمل الإضافي)</h5>
    <div class="alert alert-info py-2">
      <i class="fas fa-info-circle ml-1"></i>
      مضاعف الأوفرتايم يُطبَّق على الشركة كلها.
      <strong>0 = تعطيل الأوفرتايم لجميع الموظفين بغض النظر عن إعداداتهم الفردية.</strong>
    </div>
    <div class="row">
      <div class="col-md-3 form-group">
        <label>مضاعف الأوفرتايم</label>
        <div class="input-group">
          <input type="number" class="form-control" name="overtime_multiplier"
            step="0.01" min="0" max="10"
            value="{{ old('overtime_multiplier', $data->overtime_multiplier ?? 1.5) }}">
          <div class="input-group-append"><span class="input-group-text">× سعر الساعة</span></div>
        </div>
        <small class="text-muted">1.5 = مرة ونصف | 0 = تعطيل للشركة كلها</small>
      </div>
    </div>
    <div class="alert alert-light py-2 border">
      <i class="fas fa-info-circle ml-1 text-success"></i>
      <strong>بدل الإجازة</strong> (يوم الراحة المعمول فيه) يُضبط في قسم مستقل:
      <a href="{{ route('leave_compensation.index') }}" class="btn btn-sm btn-success mr-2">
        <i class="fas fa-umbrella-beach ml-1"></i> إعدادات بدل الإجازة
      </a>
    </div>

    <hr>

    {{-- ══ إعدادات الإذونات ══ --}}
    <h5 class="section-title"><i class="fas fa-id-card ml-2"></i>إعدادات الإذونات</h5>
    <div class="alert alert-info py-2">
      <i class="fas fa-info-circle ml-1"></i>
      الإذن يُطرح من دقائق التأخير أو الانصراف المبكر قبل الاحتساب.
      إذا الإذن ≥ دقائق التأخير → لا خصم. إذا الإذن جزئي → يُحتسب الباقي فقط.
    </div>
    <div class="row">
      <div class="col-md-4 form-group">
        <label>عدد الإذونات المسموح بها في اليوم</label>
        <div class="input-group">
          <input type="number" class="form-control" name="max_permissions_per_day"
            step="1" min="0"
            value="{{ old('max_permissions_per_day', $data->max_permissions_per_day ?? 1) }}">
          <div class="input-group-append"><span class="input-group-text">إذن</span></div>
        </div>
        <small class="text-muted">0 = لا تُسمح إذونات</small>
      </div>
      <div class="col-md-4 form-group">
        <label>أقصى مدة إذن في اليوم (بالدقائق)</label>
        <div class="input-group">
          <input type="number" class="form-control" name="max_permission_minutes_per_day"
            step="1" min="0"
            value="{{ old('max_permission_minutes_per_day', $data->max_permission_minutes_per_day ?? 60) }}">
          <div class="input-group-append"><span class="input-group-text">دقيقة</span></div>
        </div>
        <small class="text-muted">0 = بدون حد أقصى</small>
      </div>
    </div>

    <hr>

    {{-- ══ طريقة احتساب الأسعار ══ --}}
    <h5 class="section-title"><i class="fas fa-calculator ml-2"></i>طريقة احتساب سعر اليوم والساعة والدقيقة</h5>
    <div class="alert alert-info py-2">
      <i class="fas fa-info-circle ml-1"></i>
      سعر الدقيقة = سعر الساعة ÷ 60 دائماً.
      سعر الساعة = سعر اليوم ÷ مقسوم الساعة.
      سعر اليوم = الراتب ÷ مقسوم اليوم.
    </div>
    <div class="row align-items-end">
      <div class="col-md-4 form-group">
        <label>مقسوم سعر اليوم</label>
        <select class="form-control" name="day_rate_divisor_type" id="dayDivisorType"
          onchange="toggleDayCustom()">
          <option value="1" {{ ($data->day_rate_divisor_type ?? 1) == 1 ? 'selected' : '' }}>÷ 26 يوم (الشائع)</option>
          <option value="2" {{ ($data->day_rate_divisor_type ?? 1) == 2 ? 'selected' : '' }}>÷ 30 يوم</option>
          <option value="3" {{ ($data->day_rate_divisor_type ?? 1) == 3 ? 'selected' : '' }}>÷ أيام الشهر الفعلية</option>
          <option value="4" {{ ($data->day_rate_divisor_type ?? 1) == 4 ? 'selected' : '' }}>÷ عدد مخصص</option>
        </select>
      </div>
      <div class="col-md-2 form-group" id="dayCustomWrap"
        style="{{ ($data->day_rate_divisor_type ?? 1) == 4 ? '' : 'display:none' }}">
        <label>العدد المخصص</label>
        <div class="input-group">
          <input type="number" class="form-control" name="day_rate_divisor_custom"
            step="0.01" min="1"
            value="{{ old('day_rate_divisor_custom', $data->day_rate_divisor_custom ?? 26) }}">
          <div class="input-group-append"><span class="input-group-text">يوم</span></div>
        </div>
      </div>
      <div class="col-md-4 form-group">
        <label>مقسوم سعر الساعة</label>
        <select class="form-control" name="hour_rate_divisor_type" id="hourDivisorType"
          onchange="toggleHourCustom()">
          <option value="1" {{ ($data->hour_rate_divisor_type ?? 1) == 1 ? 'selected' : '' }}>÷ 8 ساعات (الافتراضي)</option>
          <option value="2" {{ ($data->hour_rate_divisor_type ?? 1) == 2 ? 'selected' : '' }}>÷ ساعات الشيفت</option>
          <option value="3" {{ ($data->hour_rate_divisor_type ?? 1) == 3 ? 'selected' : '' }}>÷ عدد مخصص</option>
        </select>
      </div>
      <div class="col-md-2 form-group" id="hourCustomWrap"
        style="{{ ($data->hour_rate_divisor_type ?? 1) == 3 ? '' : 'display:none' }}">
        <label>العدد المخصص</label>
        <div class="input-group">
          <input type="number" class="form-control" name="hour_rate_divisor_custom"
            step="0.01" min="1"
            value="{{ old('hour_rate_divisor_custom', $data->hour_rate_divisor_custom ?? 8) }}">
          <div class="input-group-append"><span class="input-group-text">ساعة</span></div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-6">
        <div class="alert alert-secondary py-2" id="ratePreview" style="font-size:0.9rem"></div>
      </div>
    </div>

    <hr>

    {{-- ══ التأمينات الاجتماعية ══ --}}
    <h5 class="section-title"><i class="fas fa-shield-alt ml-2"></i>نظام التأمينات الاجتماعية (القانون المصري)</h5>
    <div class="alert alert-warning py-2">
      <i class="fas fa-balance-scale ml-1"></i>
      <strong>الافتراضي وفق القانون المصري:</strong>
      نسبة الموظف 11% — نسبة الشركة 18.75%
    </div>
    <div class="row">
      <div class="col-md-4 form-group">
        <label>نسبة اشتراك الموظف</label>
        <div class="input-group">
          <input type="number" class="form-control" name="employee_insurance_rate"
            step="0.01" min="0" max="100"
            value="{{ old('employee_insurance_rate', $data->employee_insurance_rate ?? 11.00) }}">
          <div class="input-group-append"><span class="input-group-text">%</span></div>
        </div>
      </div>
      <div class="col-md-4 form-group">
        <label>نسبة اشتراك الشركة</label>
        <div class="input-group">
          <input type="number" class="form-control" name="company_insurance_rate"
            step="0.01" min="0" max="100"
            value="{{ old('company_insurance_rate', $data->company_insurance_rate ?? 18.75) }}">
          <div class="input-group-append"><span class="input-group-text">%</span></div>
        </div>
      </div>
      <div class="col-md-4 form-group">
        <label>إجمالي النسبة</label>
        <div class="input-group">
          <input type="text" class="form-control bg-light" readonly id="total_insurance_rate"
            value="{{ number_format(($data->employee_insurance_rate ?? 11) + ($data->company_insurance_rate ?? 18.75), 2) }}%">
        </div>
        <small class="text-muted">موظف + شركة</small>
      </div>
    </div>

    <hr>

    {{-- ══ إعدادات الإجازات ══ --}}
    <h5 class="section-title"><i class="fas fa-umbrella-beach ml-2"></i>إعدادات الإجازات</h5>
    <div class="row">
      <div class="col-md-3 form-group">
        <label>رصيد الإجازة الاعتيادية السنوية (يوم)</label>
        <div class="input-group">
          <input type="number" class="form-control" name="annual_vacation_days" step="0.5" min="0"
            value="{{ old('annual_vacation_days', $data->annual_vacation_days ?? 21) }}">
          <div class="input-group-append"><span class="input-group-text">يوم</span></div>
        </div>
      </div>
      <div class="col-md-3 form-group">
        <label>رصيد الإجازة العارضة السنوية (يوم)</label>
        <div class="input-group">
          <input type="number" class="form-control" name="casual_vacation_days" step="0.5" min="0"
            value="{{ old('casual_vacation_days', $data->casual_vacation_days ?? 6) }}">
          <div class="input-group-append"><span class="input-group-text">يوم</span></div>
        </div>
      </div>
      <div class="col-md-3 form-group">
        <label>رصيد الإجازة الشهري (تلقائي)</label>
        <div class="input-group">
          <input type="number" class="form-control" name="monthly_vacation_balance" step="0.01" min="0"
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
          <input type="number" class="form-control" name="first_balance_begain_vacation" step="0.01" min="0"
            value="{{ old('first_balance_begain_vacation', $data->first_balance_begain_vacation ?? 0) }}">
          <div class="input-group-append"><span class="input-group-text">يوم</span></div>
        </div>
      </div>
      <div class="col-md-3 form-group">
        <label>بعد كم يوم ينزل رصيد الإجازة</label>
        <div class="input-group">
          <input type="number" class="form-control" name="after_days_begain_vacation" step="0.01" min="0"
            value="{{ old('after_days_begain_vacation', $data->after_days_begain_vacation ?? 0) }}">
          <div class="input-group-append"><span class="input-group-text">يوم</span></div>
        </div>
        <small class="text-muted">مثال: 180 يوم (6 أشهر من التعيين)</small>
      </div>
    </div>

    <hr>

    {{-- ══ عقوبات الغياب ══ --}}
    <h5 class="section-title"><i class="fas fa-user-slash ml-2"></i>عقوبات الغياب المتكرر</h5>
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
          <input type="number" class="form-control" name="{{ $field }}" step="0.01" min="0"
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
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('logoPreview').innerHTML =
        '<img src="' + e.target.result + '" style="height:55px;border-radius:6px;border:1px solid #dee2e6;margin-top:4px">';
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function toggleDelayMode() {
  var mode = parseInt(document.getElementById('delayCalcMode').value);
  // وضع 1: مضاعف الدقيقة فقط
  // وضع 2: حدود جزء اليوم فقط
  // وضع 3: مضاعف الدقيقة + حدود جزء اليوم + حد المرحلة الأولى
  document.getElementById('sanctionsMinuteWrap').style.display = (mode === 1 || mode === 3) ? '' : 'none';
  document.getElementById('dayFractionWrap').style.display     = (mode === 2 || mode === 3) ? '' : 'none';
  var tier1 = document.getElementById('tier1Wrap');
  if (tier1) tier1.style.display = (mode === 3) ? '' : 'none';
}



function toggleDayCustom() {
  var t = document.getElementById('dayDivisorType').value;
  document.getElementById('dayCustomWrap').style.display = (t == 4) ? '' : 'none';
  updateRatePreview();
}

function toggleHourCustom() {
  var t = document.getElementById('hourDivisorType').value;
  document.getElementById('hourCustomWrap').style.display = (t == 3) ? '' : 'none';
  updateRatePreview();
}

function updateRatePreview() {
  var dayType  = parseInt(document.getElementById('dayDivisorType').value);
  var hourType = parseInt(document.getElementById('hourDivisorType').value);
  var dayCustom  = parseFloat(document.querySelector('[name=day_rate_divisor_custom]').value)  || 26;
  var hourCustom = parseFloat(document.querySelector('[name=hour_rate_divisor_custom]').value) || 8;

  var dayLabels  = {1:'26', 2:'30', 3:'أيام الشهر', 4: dayCustom.toString()};
  var hourLabels = {1:'8',  2:'ساعات الشيفت', 3: hourCustom.toString()};

  var dayDiv  = dayType  == 3 ? '(أيام الشهر)' : dayLabels[dayType];
  var hourDiv = hourType == 2 ? 'ساعات الشيفت' : hourLabels[hourType];

  var preview = document.getElementById('ratePreview');
  if (!preview) return;
  preview.innerHTML =
    '<strong>المعادلة المضبوطة:</strong><br>' +
    'سعر اليوم = الراتب ÷ <strong>' + dayDiv + '</strong> &nbsp;|&nbsp; ' +
    'سعر الساعة = سعر اليوم ÷ <strong>' + hourDiv + '</strong> &nbsp;|&nbsp; ' +
    'سعر الدقيقة = سعر الساعة ÷ <strong>60</strong>';
}

function updateTotalInsurance() {
  var emp = parseFloat(document.querySelector('[name=employee_insurance_rate]').value) || 0;
  var com = parseFloat(document.querySelector('[name=company_insurance_rate]').value) || 0;
  document.getElementById('total_insurance_rate').value = (emp + com).toFixed(2) + '%';
}

document.addEventListener('DOMContentLoaded', function() {
  document.querySelector('[name=employee_insurance_rate]').addEventListener('input', updateTotalInsurance);
  document.querySelector('[name=company_insurance_rate]').addEventListener('input', updateTotalInsurance);
  document.querySelector('[name=day_rate_divisor_custom]').addEventListener('input', updateRatePreview);
  document.querySelector('[name=hour_rate_divisor_custom]').addEventListener('input', updateRatePreview);
  updateRatePreview();
  toggleDelayMode();
});
</script>
@endsection
