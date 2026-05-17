{{-- FILE: resources/views/admin/kpi/report.blade.php --}}
@extends('admin.layouts.admin')
@section('title') تقرير الأداء الشهري @endsection
@section('start') الأداء @endsection
@section('home') <a href="{{ route('kpi.definitions') }}">مؤشرات الأداء</a> @endsection
@section('startpage') التقرير الشهري @endsection

@section('css')
<style>
.rank-1 { background:linear-gradient(135deg,#FFD700,#FFA500); color:#fff; }
.rank-2 { background:linear-gradient(135deg,#C0C0C0,#A8A8A8); color:#fff; }
.rank-3 { background:linear-gradient(135deg,#CD7F32,#A0522D); color:#fff; }
.score-badge { font-size:1.4em; font-weight:700; }
.bar-wrap { height:8px; background:#e9ecef; border-radius:4px; }
.bar-fill  { height:8px; border-radius:4px; transition:.5s; }
</style>
@endsection

@section('content')
<div class="col-12">

  {{-- فلتر --}}
  <form method="GET" class="form-inline mb-3">
    <select name="month" class="form-control ml-2">
      @foreach(['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'] as $i=>$m)
      <option value="{{ $i+1 }}" {{ $month==$i+1?'selected':'' }}>{{ $m }}</option>
      @endforeach
    </select>
    <input type="number" name="year" class="form-control mr-2 ml-2" style="width:90px" value="{{ $year }}">
    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
    <a href="{{ route('kpi.scores',['month'=>$month,'year'=>$year]) }}" class="btn btn-warning mr-2">
      <i class="fas fa-edit ml-1"></i>تعديل القراءات
    </a>
  </form>

  @if($byEmployee->isEmpty())
    <div class="alert alert-info">لا توجد بيانات أداء لهذا الشهر. قم بإدخال القراءات أولاً.</div>
  @else

  {{-- بطاقات الترتيب --}}
  <div class="row mb-4">
    @foreach($byEmployee->take(3) as $rank => $data)
    @php $colors = ['rank-1','rank-2','rank-3']; @endphp
    <div class="col-md-4">
      <div class="card {{ $colors[$loop->index] ?? '' }} text-center" style="border-radius:12px">
        <div class="card-body py-3">
          <div style="font-size:2em">{{ ['🥇','🥈','🥉'][$loop->index] ?? '' }}</div>
          <h5 class="mb-0">{{ $data['employee']->employee_name_A ?? '' }}</h5>
          <div class="score-badge mt-1">{{ round($data['total_score'],1) }}</div>
          <small>نقطة</small>
          <div class="mt-2">
            @if($data['net_effect'] > 0)
              <span class="badge badge-light text-success">مكافأة {{ number_format($data['net_effect'],2) }} ج.م</span>
            @elseif($data['net_effect'] < 0)
              <span class="badge badge-light text-danger">خصم {{ number_format(abs($data['net_effect']),2) }} ج.م</span>
            @endif
          </div>
        </div>
      </div>
    </div>
    @endforeach
  </div>

  {{-- جدول تفصيلي --}}
  <div class="card">
    <div class="card-header"><h4 class="mb-0">📊 التفاصيل الكاملة</h4></div>
    <div class="card-body p-0">
    <div class="table-responsive">
    <table class="table table-bordered table-sm mb-0">
      <thead class="thead-dark">
        <tr>
          <th>ترتيب</th>
          <th>الموظف</th>
          <th>متوسط التحقق %</th>
          <th>مجموع النقاط</th>
          <th>إجمالي المكافآت</th>
          <th>إجمالي الخصومات</th>
          <th>صافي التأثير</th>
          <th>تفاصيل</th>
        </tr>
      </thead>
      <tbody>
        @foreach($byEmployee as $empId => $data)
        <tr>
          <td class="text-center font-weight-bold">{{ $loop->iteration }}</td>
          <td>
            <strong>{{ $data['employee']->employee_name_A ?? '—' }}</strong>
            <br><small class="text-muted">{{ $data['employee']->employee_id ?? '' }}</small>
          </td>
          <td>
            @php $avgPct = round($data['avg_achievement'],1); @endphp
            <div class="bar-wrap">
              <div class="bar-fill" style="width:{{ min($avgPct,100) }}%;background:{{ $avgPct>=100?'#28a745':($avgPct>=80?'#17a2b8':($avgPct>=60?'#ffc107':'#dc3545')) }}"></div>
            </div>
            <small class="{{ $avgPct>=100?'text-success':($avgPct>=80?'text-info':($avgPct>=60?'text-warning':'text-danger')) }}">
              {{ $avgPct }}%
            </small>
          </td>
          <td class="text-primary font-weight-bold text-center">
            {{ round($data['total_score'],1) }}
          </td>
          <td class="text-success">{{ number_format($data['total_bonus'],2) }}</td>
          <td class="text-danger">{{ number_format($data['total_deduction'],2) }}</td>
          <td>
            @if($data['net_effect'] > 0)
              <span class="text-success font-weight-bold">+{{ number_format($data['net_effect'],2) }}</span>
            @elseif($data['net_effect'] < 0)
              <span class="text-danger font-weight-bold">{{ number_format($data['net_effect'],2) }}</span>
            @else
              <span class="text-muted">0.00</span>
            @endif
          </td>
          <td>
            <button class="btn btn-xs btn-outline-info" type="button"
              data-toggle="collapse" data-target="#detail_{{ $empId }}">
              <i class="fas fa-chevron-down"></i>
            </button>
          </td>
        </tr>
        {{-- تفاصيل المؤشرات --}}
        <tr class="collapse" id="detail_{{ $empId }}">
          <td colspan="8" class="bg-light">
            <table class="table table-sm mb-0">
              <thead><tr><th>المؤشر</th><th>الهدف</th><th>الفعلي</th><th>التحقق</th><th>النقاط</th><th>التأثير المالي</th></tr></thead>
              <tbody>
                @foreach($data['scores'] as $sc)
                <tr>
                  <td>{{ $sc->kpi->name ?? '—' }}</td>
                  <td>{{ $sc->kpi->target_value ?? '—' }}</td>
                  <td>{{ $sc->actual_value }}</td>
                  <td>
                    <span class="{{ $sc->achievement_pct>=100?'text-success':($sc->achievement_pct>=80?'text-info':'text-danger') }}">
                      {{ $sc->achievement_pct }}%
                    </span>
                  </td>
                  <td>{{ $sc->score }}</td>
                  <td>
                    @if($sc->salary_effect_amount > 0)
                      <span class="{{ $sc->effect_direction==1?'text-success':'text-danger' }}">
                        {{ $sc->effect_direction==1?'+':'-' }}{{ number_format($sc->salary_effect_amount,2) }}
                      </span>
                    @else —
                    @endif
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    </div>
    </div>
  </div>
  @endif
</div>
@endsection
