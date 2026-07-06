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
.filter-bar { background:#f8f9fa; border-radius:8px; padding:14px 16px; margin-bottom:16px; }
.sort-link { color:inherit; text-decoration:none; white-space:nowrap; }
.sort-link:hover { text-decoration:underline; }
.sort-icon { font-size:.75em; opacity:.6; }
</style>
@endsection

@section('content')
<div class="col-12">

  {{-- فلتر --}}
  <div class="filter-bar">
    <form method="GET" action="{{ route('kpi.report') }}">
      <input type="hidden" name="sort" value="{{ $sort }}">
      <input type="hidden" name="dir"  value="{{ $dir }}">
      <div class="row align-items-end">
        <div class="col-md-2 mb-2">
          <label class="small font-weight-bold mb-1">الشهر</label>
          <select name="month" class="form-control form-control-sm">
            @foreach(['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'] as $i=>$m)
            <option value="{{ $i+1 }}" {{ $month==$i+1?'selected':'' }}>{{ $m }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-1 mb-2">
          <label class="small font-weight-bold mb-1">السنة</label>
          <input type="number" name="year" class="form-control form-control-sm" style="width:80px" value="{{ $year }}">
        </div>
        <div class="col-md-3 mb-2">
          <label class="small font-weight-bold mb-1">الموظف</label>
          <select name="employee_id" class="form-control form-control-sm">
            <option value="">-- كل الموظفين --</option>
            @foreach($employees as $emp)
            <option value="{{ $emp->id }}" {{ $employeeId == $emp->id ? 'selected' : '' }}>
              {{ $emp->employee_name_A }}
            </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2 mb-2">
          <label class="small font-weight-bold mb-1">مؤشر محدد</label>
          <select name="kpi_id" class="form-control form-control-sm">
            <option value="">-- كل المؤشرات --</option>
            @foreach($kpiDefs as $kpi)
            <option value="{{ $kpi->id }}" {{ $kpiId == $kpi->id ? 'selected' : '' }}>{{ $kpi->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2 mb-2">
          <label class="small font-weight-bold mb-1">الفئة</label>
          <select name="category" class="form-control form-control-sm">
            <option value="">-- كل الفئات --</option>
            <option value="performance"  {{ $category === 'performance'  ? 'selected' : '' }}>أداء</option>
            <option value="quality"      {{ $category === 'quality'      ? 'selected' : '' }}>جودة</option>
            <option value="attendance"   {{ $category === 'attendance'   ? 'selected' : '' }}>حضور</option>
            <option value="sales"        {{ $category === 'sales'        ? 'selected' : '' }}>مبيعات</option>
            <option value="custom"       {{ $category === 'custom'       ? 'selected' : '' }}>مخصص</option>
          </select>
        </div>
        <div class="col-md-2 mb-2 d-flex" style="gap:6px;padding-top:20px">
          <button type="submit" class="btn btn-primary btn-sm flex-fill">
            <i class="fas fa-search"></i> بحث
          </button>
          <a href="{{ route('kpi.report') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-undo"></i>
          </a>
        </div>
      </div>
    </form>
  </div>

  @if($byEmployee->isEmpty())
    <div class="alert alert-info">
      <i class="fas fa-info-circle ml-1"></i>
      لا توجد بيانات أداء لهذا الشهر بالفلتر المحدد.
      <a href="{{ route('kpi.scores',['month'=>$month,'year'=>$year]) }}" class="alert-link mr-2">إدخال القراءات</a>
    </div>
  @else

  @php
    $months = ['','يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
  @endphp

  {{-- بطاقات أول 3 --}}
  @if(!$employeeId && !$kpiId)
  <div class="row mb-4">
    @foreach($byEmployee->take(3) as $rank => $data)
    @php $colors = ['rank-1','rank-2','rank-3']; @endphp
    <div class="col-md-4 mb-2">
      <div class="card {{ $colors[$loop->index] ?? '' }} text-center" style="border-radius:12px">
        <div class="card-body py-3">
          <div style="font-size:2em">{{ ['🥇','🥈','🥉'][$loop->index] ?? '' }}</div>
          <h5 class="mb-0">{{ $data['employee']->employee_name_A ?? '' }}</h5>
          <div class="score-badge mt-1">{{ $data['total_score'] }}</div>
          <small>نقطة | متوسط تحقيق {{ $data['avg_achievement'] }}%</small>
          <div class="mt-2">
            @if($data['net_effect'] > 0)
              <span class="badge badge-light text-success">مكافأة +{{ number_format($data['net_effect'],2) }} ج.م</span>
            @elseif($data['net_effect'] < 0)
              <span class="badge badge-light text-danger">خصم {{ number_format(abs($data['net_effect']),2) }} ج.م</span>
            @endif
          </div>
        </div>
      </div>
    </div>
    @endforeach
  </div>
  @endif

  {{-- جدول تفصيلي مع ترتيب --}}
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">
        <i class="fas fa-chart-bar ml-2 text-primary"></i>
        التفاصيل الكاملة — {{ $months[$month] }} {{ $year }}
        <small class="text-muted">({{ $byEmployee->count() }} موظف)</small>
      </h5>
      <a href="{{ route('kpi.scores',['month'=>$month,'year'=>$year]) }}" class="btn btn-sm btn-warning">
        <i class="fas fa-edit ml-1"></i>تعديل القراءات
      </a>
    </div>
    <div class="card-body p-0">
    <div class="table-responsive">
    <table class="table table-bordered table-sm mb-0">
      <thead class="thead-dark">
        <tr>
          <th style="width:40px">ترتيب</th>
          <th>
            @php $nameDir = ($sort==='name' && $dir==='desc') ? 'asc' : 'desc'; @endphp
            <a class="sort-link" href="{{ route('kpi.report', array_merge(request()->query(), ['sort'=>'name','dir'=>$nameDir])) }}">
              الموظف
              @if($sort==='name') <i class="fas fa-sort-{{ $dir==='desc'?'down':'up' }} sort-icon"></i>
              @else <i class="fas fa-sort sort-icon"></i> @endif
            </a>
          </th>
          <th>
            @php $achDir = ($sort==='achievement' && $dir==='desc') ? 'asc' : 'desc'; @endphp
            <a class="sort-link" href="{{ route('kpi.report', array_merge(request()->query(), ['sort'=>'achievement','dir'=>$achDir])) }}">
              متوسط التحقق %
              @if($sort==='achievement') <i class="fas fa-sort-{{ $dir==='desc'?'down':'up' }} sort-icon"></i>
              @else <i class="fas fa-sort sort-icon"></i> @endif
            </a>
          </th>
          <th>
            @php $scoreDir = ($sort==='score' && $dir==='desc') ? 'asc' : 'desc'; @endphp
            <a class="sort-link" href="{{ route('kpi.report', array_merge(request()->query(), ['sort'=>'score','dir'=>$scoreDir])) }}">
              مجموع النقاط
              @if($sort==='score') <i class="fas fa-sort-{{ $dir==='desc'?'down':'up' }} sort-icon"></i>
              @else <i class="fas fa-sort sort-icon"></i> @endif
            </a>
          </th>
          <th class="text-success">المكافآت</th>
          <th class="text-danger">الخصومات</th>
          <th>
            @php $bonusDir = ($sort==='bonus' && $dir==='desc') ? 'asc' : 'desc'; @endphp
            <a class="sort-link" href="{{ route('kpi.report', array_merge(request()->query(), ['sort'=>'bonus','dir'=>$bonusDir])) }}">
              صافي التأثير
              @if($sort==='bonus') <i class="fas fa-sort-{{ $dir==='desc'?'down':'up' }} sort-icon"></i>
              @else <i class="fas fa-sort sort-icon"></i> @endif
            </a>
          </th>
          <th style="width:40px">تفاصيل</th>
        </tr>
      </thead>
      <tbody>
        @foreach($byEmployee as $empId => $data)
        <tr>
          <td class="text-center font-weight-bold text-muted">{{ $loop->iteration }}</td>
          <td>
            <strong>{{ $data['employee']->employee_name_A ?? '—' }}</strong>
            <br><small class="text-muted">{{ $data['employee']->employee_id ?? '' }}</small>
          </td>
          <td>
            @php $avgPct = $data['avg_achievement']; @endphp
            <div class="bar-wrap">
              <div class="bar-fill" style="width:{{ min($avgPct,100) }}%;background:{{ $avgPct>=100?'#28a745':($avgPct>=80?'#17a2b8':($avgPct>=60?'#ffc107':'#dc3545')) }}"></div>
            </div>
            <small class="{{ $avgPct>=100?'text-success':($avgPct>=80?'text-info':($avgPct>=60?'text-warning':'text-danger')) }}">
              {{ $avgPct }}%
            </small>
          </td>
          <td class="text-primary font-weight-bold text-center">{{ $data['total_score'] }}</td>
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
          <td colspan="8" class="bg-light p-0">
            <table class="table table-sm mb-0">
              <thead class="thead-light">
                <tr>
                  <th>المؤشر</th>
                  <th>الفئة</th>
                  <th>الهدف</th>
                  <th>الفعلي</th>
                  <th>التحقق %</th>
                  <th>الوزن</th>
                  <th>النقاط</th>
                  <th>التأثير المالي</th>
                </tr>
              </thead>
              <tbody>
                @foreach($data['scores'] as $sc)
                @php
                  $catLabels = ['performance'=>'أداء','quality'=>'جودة','attendance'=>'حضور','sales'=>'مبيعات','custom'=>'مخصص'];
                @endphp
                <tr>
                  <td><strong>{{ $sc->kpi->name ?? '—' }}</strong></td>
                  <td>
                    <span class="badge badge-secondary" style="font-size:.7em">
                      {{ $catLabels[$sc->kpi->category ?? ''] ?? ($sc->kpi->category ?? '') }}
                    </span>
                  </td>
                  <td>{{ $sc->kpi->target_value ?? '—' }} {{ $sc->kpi->measurement_unit ?? '' }}</td>
                  <td>{{ $sc->actual_value }} {{ $sc->kpi->measurement_unit ?? '' }}</td>
                  <td>
                    <span class="{{ $sc->achievement_pct>=100?'text-success':($sc->achievement_pct>=80?'text-info':'text-danger') }} font-weight-bold">
                      {{ $sc->achievement_pct }}%
                    </span>
                  </td>
                  <td>{{ $sc->kpi->weight ?? '—' }}</td>
                  <td class="text-primary font-weight-bold">{{ $sc->score }}</td>
                  <td>
                    @if($sc->salary_effect_amount > 0)
                      <span class="{{ $sc->effect_direction==1?'text-success':'text-danger' }} font-weight-bold">
                        {{ $sc->effect_direction==1?'+':'-' }}{{ number_format($sc->salary_effect_amount,2) }} ج.م
                      </span>
                    @else
                      <span class="text-muted">—</span>
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
      {{-- إجماليات --}}
      <tfoot class="table-dark">
        <tr>
          <th colspan="2" class="text-right">الإجمالي</th>
          <th class="text-center">{{ round($byEmployee->avg('avg_achievement'),1) }}% متوسط</th>
          <th class="text-center text-warning">{{ round($byEmployee->sum('total_score'),1) }}</th>
          <th class="text-success">{{ number_format($byEmployee->sum('total_bonus'),2) }}</th>
          <th class="text-danger">{{ number_format($byEmployee->sum('total_deduction'),2) }}</th>
          <th class="{{ $byEmployee->sum('net_effect') >= 0 ? 'text-success' : 'text-danger' }}">
            {{ $byEmployee->sum('net_effect') >= 0 ? '+' : '' }}{{ number_format($byEmployee->sum('net_effect'),2) }}
          </th>
          <th></th>
        </tr>
      </tfoot>
    </table>
    </div>
    </div>
  </div>

  @endif
</div>
@endsection
