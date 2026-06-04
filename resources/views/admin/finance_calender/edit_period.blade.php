@extends('admin.layouts.admin')
@section('title') تعديل الشهر المالي @endsection
@section('start') السنوات المالية @endsection
@section('home') <a href="{{ route('finance_calender.index') }}">السنوات المالية</a> @endsection
@section('startpage') تعديل شهر @endsection

@section('content')
<div class="col-md-10 mx-auto">
  <div class="card card-primary">
    <div class="card-header">
      <h3 class="card-title">
        <i class="fas fa-calendar-day ml-2"></i>
        تعديل الشهر المالي —
        <strong>{{ $period->Month->monthe_name ?? $period->year_of_month }}</strong>
        ({{ $period->year_of_month }})
      </h3>
    </div>

    <form action="{{ route('finance_cln_period.update', $period->id) }}" method="POST">
      @csrf
      @method('PUT')
      <div class="card-body">

        {{-- رسائل الخطأ --}}
        @if($errors->any())
          <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
          </div>
        @endif
        @if(session('errorUpdate'))
          <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('errorUpdate') }}
          </div>
        @endif

        <div class="row">
          {{-- تواريخ الرواتب --}}
          <div class="col-md-6">
            <div class="card card-outline card-success">
              <div class="card-header"><h5 class="mb-0"><i class="fas fa-money-bill-wave ml-1"></i>فترة احتساب الراتب</h5></div>
              <div class="card-body">
                <div class="form-group">
                  <label>تاريخ البداية <span class="text-danger">*</span></label>
                  <input type="date" name="start_date" class="form-control"
                    value="{{ old('start_date', $period->start_date) }}" required>
                </div>
                <div class="form-group">
                  <label>تاريخ النهاية <span class="text-danger">*</span></label>
                  <input type="date" name="end_date" class="form-control"
                    value="{{ old('end_date', $period->end_date) }}" required>
                </div>
                <div class="form-group">
                  <label>عدد أيام الشهر <span class="text-danger">*</span></label>
                  <input type="number" name="number_of_days" class="form-control"
                    value="{{ old('number_of_days', $period->number_of_days) }}"
                    min="1" max="31" required>
                  <small class="text-muted">يُستخدم في احتساب قيمة اليوم = الراتب ÷ عدد الأيام</small>
                </div>
                <div class="form-group">
                  <label>أيام العمل الفعلية</label>
                  <input type="number" name="working_days" class="form-control"
                    value="{{ old('working_days', $period->working_days ?? $period->number_of_days) }}"
                    min="0" max="31">
                  <small class="text-muted">أيام عمل فعلية بعد استبعاد الجمع/الإجازات الرسمية</small>
                </div>
              </div>
            </div>
          </div>

          {{-- تواريخ البصمة --}}
          <div class="col-md-6">
            <div class="card card-outline card-info">
              <div class="card-header"><h5 class="mb-0"><i class="fas fa-fingerprint ml-1"></i>فترة البصمة</h5></div>
              <div class="card-body">
                <div class="form-group">
                  <label>بداية فترة البصمة <span class="text-danger">*</span></label>
                  <input type="date" name="start_date_finger_print" class="form-control"
                    value="{{ old('start_date_finger_print', $period->start_date_finger_print) }}" required>
                  <small class="text-muted">يمكن أن تختلف عن فترة الراتب (مثلاً: من 26 لغاية 25)</small>
                </div>
                <div class="form-group">
                  <label>نهاية فترة البصمة <span class="text-danger">*</span></label>
                  <input type="date" name="end_date_finger_print" class="form-control"
                    value="{{ old('end_date_finger_print', $period->end_date_finger_print) }}" required>
                </div>
                <div class="form-group">
                  <label>استحقاق الإجازة (يوم/شهر)</label>
                  <input type="number" name="vacation_days_accrual" class="form-control"
                    value="{{ old('vacation_days_accrual', $period->vacation_days_accrual ?? '') }}"
                    min="0" step="0.01">
                  <small class="text-muted">
                    أتركه فارغاً لاستخدام الإعداد العام من الضبط ({{ $period->financeCalender->setting->monthly_vacation_balance ?? '—' }} يوم/شهر)
                  </small>
                </div>
                <div class="form-group">
                  <label>حالة الشهر</label>
                  <select name="is_open" class="form-control">
                    <option value="0" {{ ($period->is_open ?? 0) == 0 ? 'selected' : '' }}>مفتوح (جارٍ)</option>
                    <option value="1" {{ ($period->is_open ?? 0) == 1 ? 'selected' : '' }}>مغلق (منتهٍ)</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <div class="card-footer">
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save ml-1"></i> حفظ التعديلات
        </button>
        <a href="{{ route('finance_calender.index') }}" class="btn btn-secondary mr-2">
          <i class="fas fa-arrow-right ml-1"></i> رجوع
        </a>
      </div>
    </form>
  </div>
</div>
@endsection
