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

      {{-- إعدادات التأخير --}}
      <div class="card mb-3">
        <div class="card-header"><h5 class="mb-0"><i class="fas fa-clock ml-2"></i>إعدادات التأخير والانصراف المبكر</h5></div>
        <div class="card-body">
          <div class="row mb-2">
            <div class="col-md-6">
              <small class="text-muted d-block">طريقة الاحتساب</small>
              <strong>
                @php $mode = $data->delay_calc_mode ?? 1; @endphp
                @if($mode == 1) بالدقيقة
                @elseif($mode == 2) نصف/يوم بعد X مرة
                @else مدمج (دقيقة + مرات)
                @endif
              </strong>
            </div>
            <div class="col-md-6">
              <small class="text-muted d-block">خصم الدقيقة</small>
              <strong>{{ $data->sanctions_value_minute_delay ?? 0 }} ج.م
                <span class="text-muted small">{{ ($data->sanctions_value_minute_delay ?? 0) == 0 ? '(تلقائي من الراتب)' : '' }}</span>
              </strong>
            </div>
          </div>
          <div class="row text-center">
            @foreach([
              ['after_minute_calc_delay',   'تأخير بعد (د)',    'warning'],
              ['after_minute_calc_early',   'انصراف مبكر (د)', 'warning'],
              ['after_minute_quarterday',   'ربع يوم بعد (د)', 'danger'],
              ['after_time_half_daycut',    'نصف يوم بعد (م)', 'danger'],
              ['after_time_allday_daycut',  'يوم كامل بعد (م)','danger'],
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
    </div>
  </div>

  {{-- الأوفرتايم --}}
  <div class="card mb-3">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-business-time ml-2"></i>إعدادات الأوفرتايم</h5></div>
    <div class="card-body">
      <div class="row text-center">
        <div class="col-md-4">
          <div class="p-3 rounded" style="background:#f8f9fa">
            <div class="h2 font-weight-bold text-success mb-0">{{ $data->overtime_multiplier ?? 1.5 }}×</div>
            <small class="text-muted">مضاعف سعر الساعة للأوفرتايم</small>
          </div>
        </div>
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
