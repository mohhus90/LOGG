@extends('admin.layouts.admin')
@section('title') الضبط العام @endsection
@section('start') الضبط العام @endsection
@section('home') <a href="{{ route('generalsetting.index') }}">الضبط العام</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
@if(!isset($data) || empty($data))
  <div class="alert alert-warning">
    لا توجد بيانات ضبط. <a href="{{ route('generalsetting.create') }}" class="btn btn-success btn-sm ml-2">إنشاء الضبط</a>
  </div>
@else

  @if(session('success'))
    <div class="alert alert-success alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      {{ session('success') }}
    </div>
  @endif

  {{-- بطاقة الشركة --}}
  <div class="row">
    <div class="col-md-4">
      <div class="card text-center">
        <div class="card-body py-4">
          @if($data->image)
            <img src="{{ asset('storage/' . $data->image) }}"
              alt="Logo" style="max-height:100px;max-width:200px;object-fit:contain;border-radius:8px">
          @else
            <div style="width:100px;height:100px;background:#e9ecef;border-radius:50%;margin:auto;
                display:flex;align-items:center;justify-content:center">
              <i class="fas fa-building fa-2x text-muted"></i>
            </div>
          @endif
          <h4 class="mt-3 mb-1 font-weight-bold" style="color:var(--primary)">{{ $data->com_name }}</h4>
          <span class="badge badge-{{ $data->saysem_status ? 'success' : 'danger' }} p-2">
            {{ $data->saysem_status ? 'النظام مفعّل' : 'النظام معطّل' }}
          </span>
        </div>
        <div class="card-footer">
          <a href="{{ route('generalsetting.edit') }}" class="btn btn-primary btn-block">
            <i class="fas fa-edit ml-1"></i> تعديل الإعدادات
          </a>
        </div>
      </div>
    </div>

    <div class="col-md-8">
      {{-- بيانات الاتصال --}}
      <div class="card mb-3">
        <div class="card-header"><h5 class="mb-0"><i class="fas fa-phone ml-2"></i>بيانات الاتصال</h5></div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-4"><small class="text-muted d-block">الهاتف</small><strong>{{ $data->phone ?: '—' }}</strong></div>
            <div class="col-md-4"><small class="text-muted d-block">البريد الإلكتروني</small><strong>{{ $data->email ?: '—' }}</strong></div>
            <div class="col-md-4"><small class="text-muted d-block">العنوان</small><strong>{{ $data->address ?: '—' }}</strong></div>
          </div>
        </div>
      </div>

      {{-- إعدادات التأخير والانصراف المبكر --}}
      @php $mode = (int)($data->delay_calc_mode ?? 1); @endphp
      <div class="card mb-3">
        <div class="card-header">
          <h5 class="mb-0"><i class="fas fa-clock ml-2"></i>إعدادات التأخير والانصراف المبكر</h5>
        </div>
        <div class="card-body">

          {{-- وضع الاحتساب --}}
          <div class="row mb-3">
            <div class="col-md-6">
              <small class="text-muted d-block">طريقة الاحتساب</small>
              <strong>
                @if($mode == 1) وضع 1 — بالدقيقة (دقيقة × مضاعف)
                @elseif($mode == 2) وضع 2 — جزء اليوم (ربع / نصف / يوم)
                @else وضع 3 — هرمي مدمج (دقيقة ثم جزء اليوم)
                @endif
              </strong>
            </div>
            <div class="col-md-6">
              <small class="text-muted d-block">قيمة خصم الدقيقة (مضاعف)</small>
              <strong>
                @if($mode == 2)
                  <span class="text-muted">غير مستخدم في وضع جزء اليوم</span>
                @elseif(($data->sanctions_value_minute_delay ?? 0) == 0)
                  تلقائي من الراتب
                @else
                  {{ $data->sanctions_value_minute_delay }} ×
                @endif
              </strong>
            </div>
          </div>

          {{-- دقائق السماح --}}
          <div class="row text-center mb-3">
            <div class="col-md-6 mb-2">
              <div class="p-2 rounded" style="background:#f8f9fa">
                <div class="h4 font-weight-bold text-secondary mb-0">{{ $data->after_minute_calc_delay ?? 0 }} د</div>
                <small class="text-muted">سماح التأخير</small>
              </div>
            </div>
            <div class="col-md-6 mb-2">
              <div class="p-2 rounded" style="background:#f8f9fa">
                <div class="h4 font-weight-bold text-secondary mb-0">{{ $data->after_minute_calc_early ?? 0 }} د</div>
                <small class="text-muted">سماح الانصراف المبكر</small>
              </div>
            </div>
          </div>

          {{-- حدود جزء اليوم (تظهر في وضعي 2 و3) --}}
          @if($mode == 2 || $mode == 3)
          <div class="alert alert-warning py-2 mb-2" style="font-size:.85rem">
            <i class="fas fa-layer-group ml-1"></i>
            <strong>حدود جزء اليوم — التأخير:</strong>
          </div>
          <div class="row text-center mb-3">
            @if($mode == 3)
            <div class="col-md-3 mb-2">
              <div class="p-2 rounded border" style="background:#fff8e1">
                <div class="h5 font-weight-bold text-warning mb-0">{{ $data->delay_tier1_minutes ?? 0 }} د</div>
                <small class="text-muted">حد المرحلة الأولى</small>
                <div class="text-muted" style="font-size:.75rem">(دقيقة × مضاعف حتى هذا الحد)</div>
              </div>
            </div>
            @endif
            <div class="col-md-3 mb-2">
              <div class="p-2 rounded border" style="background:#fff3e0">
                <div class="h5 font-weight-bold text-warning mb-0">{{ $data->after_minute_quarterday ?? 0 }} د</div>
                <small class="text-muted">خصم ربع يوم بعد</small>
              </div>
            </div>
            <div class="col-md-3 mb-2">
              <div class="p-2 rounded border" style="background:#fce4ec">
                <div class="h5 font-weight-bold text-danger mb-0">{{ $data->delay_halfday_minutes ?? 0 }} د</div>
                <small class="text-muted">خصم نصف يوم بعد</small>
              </div>
            </div>
            <div class="col-md-3 mb-2">
              <div class="p-2 rounded border" style="background:#ffebee">
                <div class="h5 font-weight-bold text-danger mb-0">{{ $data->delay_fullday_minutes ?? 0 }} د</div>
                <small class="text-muted">خصم يوم كامل بعد</small>
              </div>
            </div>
          </div>

          <div class="alert alert-danger py-2 mb-2" style="font-size:.85rem">
            <i class="fas fa-sign-out-alt ml-1"></i>
            <strong>حدود جزء اليوم — الانصراف المبكر:</strong>
          </div>
          <div class="row text-center mb-2">
            <div class="col-md-4 mb-2">
              <div class="p-2 rounded border" style="background:#e8f5e9">
                <div class="h5 font-weight-bold text-success mb-0">{{ $data->early_departure_halfday_minutes ?? 0 }} د</div>
                <small class="text-muted">خصم نصف يوم بعد</small>
              </div>
            </div>
            <div class="col-md-4 mb-2">
              <div class="p-2 rounded border" style="background:#fce4ec">
                <div class="h5 font-weight-bold text-danger mb-0">{{ $data->early_departure_fullday_minutes ?? 0 }} د</div>
                <small class="text-muted">خصم يوم كامل بعد</small>
              </div>
            </div>
            <div class="col-md-4 mb-2">
              <div class="p-2 rounded border" style="background:#ffebee">
                <div class="h5 font-weight-bold text-danger mb-0">{{ $data->early_departure_fullplushalf_minutes ?? 0 }} د</div>
                <small class="text-muted">خصم يوم + نصف بعد</small>
              </div>
            </div>
          </div>
          @else
          {{-- وضع 1: عرض مبسّط --}}
          <div class="row text-center">
            <div class="col-md-4 mb-2">
              <div class="p-2 rounded" style="background:#f8f9fa">
                <div class="h4 font-weight-bold text-warning mb-0">{{ $data->after_minute_quarterday ?? 0 }} د</div>
                <small class="text-muted">ربع يوم بعد (د)</small>
              </div>
            </div>
            <div class="col-md-4 mb-2">
              <div class="p-2 rounded" style="background:#f8f9fa">
                <div class="h4 font-weight-bold text-danger mb-0">{{ $data->after_time_half_daycut ?? 0 }}</div>
                <small class="text-muted">نصف يوم بعد (م)</small>
              </div>
            </div>
            <div class="col-md-4 mb-2">
              <div class="p-2 rounded" style="background:#f8f9fa">
                <div class="h4 font-weight-bold text-danger mb-0">{{ $data->after_time_allday_daycut ?? 0 }}</div>
                <small class="text-muted">يوم كامل بعد (م)</small>
              </div>
            </div>
          </div>
          @endif

        </div>
      </div>
    </div>
  </div>

  {{-- الأوفرتايم --}}
  <div class="card mb-3">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-business-time ml-2"></i>إعدادات الأوفرتايم</h5></div>
    <div class="card-body">
      @php $overtimeMult = (float)($data->overtime_multiplier ?? 1.5); @endphp
      <div class="row text-center">
        <div class="col-md-4">
          <div class="p-3 rounded" style="background:#f8f9fa">
            @if($overtimeMult == 0)
              <div class="h2 font-weight-bold text-danger mb-0">معطّل</div>
              <small class="text-muted">الأوفرتايم معطّل (المضاعف = 0)</small>
            @else
              <div class="h2 font-weight-bold text-success mb-0">{{ $overtimeMult }}×</div>
              <small class="text-muted">مضاعف سعر الساعة للأوفرتايم</small>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- طريقة احتساب الأسعار --}}
  <div class="card mb-3">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-calculator ml-2"></i>طريقة احتساب سعر اليوم والساعة والدقيقة</h5></div>
    <div class="card-body">
      @php
        $dayType  = $data->day_rate_divisor_type  ?? 1;
        $hourType = $data->hour_rate_divisor_type ?? 1;
        $dayLabels  = [1 => '÷ 26 يوم', 2 => '÷ 30 يوم', 3 => '÷ أيام الشهر الفعلية', 4 => '÷ ' . ($data->day_rate_divisor_custom ?? 26) . ' يوم (مخصص)'];
        $hourLabels = [1 => '÷ 8 ساعات', 2 => '÷ ساعات الشيفت', 3 => '÷ ' . ($data->hour_rate_divisor_custom ?? 8) . ' ساعة (مخصص)'];
      @endphp
      <div class="row text-center">
        <div class="col-md-4">
          <div class="p-3 rounded" style="background:#f8f9fa">
            <div class="h5 font-weight-bold text-primary mb-1">{{ $dayLabels[$dayType] ?? '÷ 26 يوم' }}</div>
            <small class="text-muted">مقسوم سعر اليوم</small>
          </div>
        </div>
        <div class="col-md-4">
          <div class="p-3 rounded" style="background:#f8f9fa">
            <div class="h5 font-weight-bold text-primary mb-1">{{ $hourLabels[$hourType] ?? '÷ 8 ساعات' }}</div>
            <small class="text-muted">مقسوم سعر الساعة</small>
          </div>
        </div>
        <div class="col-md-4">
          <div class="p-3 rounded" style="background:#fff3cd">
            <div class="h5 font-weight-bold text-warning mb-1">÷ 60 دقيقة</div>
            <small class="text-muted">مقسوم سعر الدقيقة (ثابت دائماً)</small>
          </div>
        </div>
      </div>
      <div class="alert alert-info py-2 mt-3 mb-0" style="font-size:0.88rem">
        <i class="fas fa-info-circle ml-1"></i>
        <strong>المعادلة:</strong>
        سعر اليوم = الراتب <strong>{{ $dayLabels[$dayType] ?? '÷ 26' }}</strong> &nbsp;|&nbsp;
        سعر الساعة = سعر اليوم <strong>{{ $hourLabels[$hourType] ?? '÷ 8' }}</strong> &nbsp;|&nbsp;
        سعر الدقيقة = سعر الساعة <strong>÷ 60</strong>
      </div>
    </div>
  </div>

  {{-- التأمينات الاجتماعية --}}
  <div class="card mb-3">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-shield-alt ml-2"></i>نظام التأمينات الاجتماعية</h5></div>
    <div class="card-body">
      <div class="row text-center">
        <div class="col-md-4">
          <div class="p-3 rounded" style="background:#f8f9fa">
            <div class="h3 font-weight-bold text-info mb-0">{{ $data->employee_insurance_rate ?? 11 }}%</div>
            <small class="text-muted">نسبة اشتراك الموظف</small>
          </div>
        </div>
        <div class="col-md-4">
          <div class="p-3 rounded" style="background:#f8f9fa">
            <div class="h3 font-weight-bold text-primary mb-0">{{ $data->company_insurance_rate ?? 18.75 }}%</div>
            <small class="text-muted">نسبة اشتراك الشركة</small>
          </div>
        </div>
        <div class="col-md-4">
          <div class="p-3 rounded" style="background:#fff3cd">
            <div class="h3 font-weight-bold text-warning mb-0">
              {{ number_format(($data->employee_insurance_rate ?? 11) + ($data->company_insurance_rate ?? 18.75), 2) }}%
            </div>
            <small class="text-muted">إجمالي (موظف + شركة)</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- إعدادات الإجازات --}}
  <div class="card mb-3">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-umbrella-beach ml-2"></i>إعدادات الإجازات</h5></div>
    <div class="card-body">
      <div class="row text-center">
        @foreach([
          ['annual_vacation_days',          'إجازة سنوية (يوم)',           'success'],
          ['casual_vacation_days',          'إجازة عارضة (يوم)',           'info'],
          ['monthly_vacation_balance',      'رصيد شهري (يوم)',             'success'],
          ['first_balance_begain_vacation', 'رصيد أول المدة (يوم)',        'info'],
          ['after_days_begain_vacation',    'بعد كم يوم ينزل الرصيد',     'warning'],
        ] as [$field, $label, $color])
        <div class="col-md-4 col-6 mb-3">
          <div class="p-2 rounded" style="background:#f8f9fa">
            <div class="h4 font-weight-bold text-{{ $color }} mb-0">{{ $data->$field ?? 0 }}</div>
            <small class="text-muted">{{ $label }}</small>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>

  {{-- عقوبات الغياب --}}
  <div class="card">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-user-slash ml-2"></i>عقوبات الغياب</h5></div>
    <div class="card-body">
      <div class="row text-center">
        @foreach([
          ['sanctions_value_first_abcence',  '×أول غياب',   'warning'],
          ['sanctions_value_second_abcence', '×ثاني غياب',  'danger'],
          ['sanctions_value_third_abcence',  '×ثالث غياب',  'danger'],
          ['sanctions_value_forth_abcence',  '×رابع فأكثر', 'danger'],
        ] as [$field, $label, $color])
        <div class="col-md-3 mb-2">
          <div class="p-3 rounded" style="background:#f8f9fa">
            <div class="h3 font-weight-bold text-{{ $color }} mb-0">{{ $data->$field ?? 0 }}</div>
            <small class="text-muted">{{ $label }} سعر اليوم</small>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>

@endif
</div>
@endsection
