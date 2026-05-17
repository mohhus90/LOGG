{{-- FILE: resources/views/admin/kpi/scores.blade.php --}}
@extends('admin.layouts.admin')
@section('title') إدخال قراءات KPI @endsection
@section('start') الأداء @endsection
@section('home') <a href="{{ route('kpi.definitions') }}">مؤشرات الأداء</a> @endsection
@section('startpage') إدخال القراءات @endsection

@section('css')
<style>
.kpi-grid td, .kpi-grid th { padding:6px 8px; font-size:13px; vertical-align:middle; }
.kpi-grid input[type=number] { width:90px; }
.achievement-cell { font-weight:700; }
.ach-great  { color:#28a745; }
.ach-good   { color:#17a2b8; }
.ach-warn   { color:#ffc107; }
.ach-bad    { color:#dc3545; }
.sticky-col { position:sticky; right:0; background:#fff; z-index:5; box-shadow:-2px 0 5px rgba(0,0,0,.05); }
</style>
@endsection

@section('content')
<div class="col-12">
<div class="card">
  <div class="card-header">
    <h3 class="card-title">
      <i class="fas fa-edit ml-2"></i>إدخال قراءات الأداء الشهرية
    </h3>
    <div class="card-tools">
      <a href="{{ route('kpi.report') }}" class="btn btn-sm btn-info">
        <i class="fas fa-chart-bar ml-1"></i>عرض التقرير
      </a>
    </div>
  </div>

  {{-- فلتر الشهر --}}
  <div class="card-body pb-0">
    <form method="GET" class="form-inline mb-3">
      <label class="ml-2">الشهر:</label>
      <select name="month" class="form-control ml-2">
        @foreach(['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'] as $i=>$m)
        <option value="{{ $i+1 }}" {{ $month==$i+1?'selected':'' }}>{{ $m }}</option>
        @endforeach
      </select>
      <input type="number" name="year" class="form-control mr-2 ml-2" style="width:90px" value="{{ $year }}">
      <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> عرض</button>
    </form>

    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($kpis->isEmpty())
      <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle ml-1"></i>
        لا توجد مؤشرات أداء مفعّلة.
        <a href="{{ route('kpi.create_definition') }}">أضف مؤشرات أولاً</a>
      </div>
    @else

    <div class="alert alert-info py-2" style="font-size:.88em">
      <i class="fas fa-info-circle ml-1"></i>
      أدخل القيمة الفعلية المحققة لكل موظف في كل مؤشر. سيتم احتساب النسب والتأثير المالي تلقائياً.
      <br>الأهداف:
      @foreach($kpis as $kpi)
        <strong>{{ $kpi->name }}</strong>: {{ $kpi->target_value }} {{ $kpi->measurement_unit }}
        @if(!$loop->last) | @endif
      @endforeach
    </div>
    @endif
  </div>

  @if($kpis->isNotEmpty())
  <form action="{{ route('kpi.save_scores') }}" method="POST">
    @csrf
    <input type="hidden" name="month" value="{{ $month }}">
    <input type="hidden" name="year" value="{{ $year }}">

    <div class="card-body pt-0">
    <div class="table-responsive">
    <table class="table table-bordered table-sm kpi-grid">
      <thead class="thead-dark">
        <tr>
          <th class="sticky-col">#</th>
          <th class="sticky-col" style="right:40px">الموظف</th>
          @foreach($kpis as $kpi)
          <th>
            <div>{{ $kpi->name }}</div>
            <small class="font-weight-normal text-warning">
              هدف: {{ $kpi->target_value }}{{ $kpi->measurement_unit }}
              | وزن: {{ $kpi->weight }}%
            </small>
            @if($kpi->affects_salary)
              <br><span class="badge badge-sm badge-warning">يؤثر على الراتب</span>
            @endif
          </th>
          @endforeach
          <th>مجموع النقاط</th>
        </tr>
      </thead>
      <tbody>
        @foreach($employees as $emp)
        @php $empScores = $scores->get($emp->id, collect())->keyBy('kpi_id'); @endphp
        <tr>
          <td class="sticky-col">{{ $loop->iteration }}</td>
          <td class="sticky-col" style="right:40px;min-width:150px">
            <strong>{{ $emp->employee_name_A }}</strong>
            <br><small class="text-muted">{{ $emp->employee_id }}</small>
          </td>
          @php $totalScore = 0; @endphp
          @foreach($kpis as $kpi)
          @php
            $existingScore  = $empScores->get($kpi->id);
            $actualValue    = $existingScore?->actual_value ?? '';
            $achievementPct = $existingScore?->achievement_pct ?? null;
            $achClass = '';
            if ($achievementPct !== null) {
              $achClass = $achievementPct >= 100 ? 'ach-great' : ($achievementPct >= 80 ? 'ach-good' : ($achievementPct >= 60 ? 'ach-warn' : 'ach-bad'));
            }
            $totalScore += $existingScore?->score ?? 0;
          @endphp
          <td>
            <input type="number" step="0.01" min="0"
              name="scores[{{ $emp->id }}][{{ $kpi->id }}]"
              class="form-control form-control-sm kpi-input"
              value="{{ $actualValue }}"
              data-target="{{ $kpi->target_value }}"
              data-weight="{{ $kpi->weight }}"
              onchange="calcRowScore(this, {{ $emp->id }})">
            @if($achievementPct !== null)
              <small class="achievement-cell {{ $achClass }}">
                {{ round($achievementPct) }}% تحقق
              </small>
            @endif
          </td>
          @endforeach
          <td class="font-weight-bold text-primary" id="totalScore_{{ $emp->id }}">
            {{ round($totalScore, 1) }}
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    </div>
    </div>

    <div class="card-footer">
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-save ml-1"></i>حفظ القراءات واحتساب التأثير المالي
      </button>
      <a href="{{ route('kpi.report', ['month'=>$month,'year'=>$year]) }}" class="btn btn-info mr-2">
        <i class="fas fa-chart-bar ml-1"></i>عرض التقرير
      </a>
    </div>
  </form>
  @endif
</div>
</div>
@endsection

@section('js')
<script>
// احتساب مبدئي للنسبة في الصف
function calcRowScore(inp, empId) {
  const target = parseFloat(inp.dataset.target) || 1;
  const weight = parseFloat(inp.dataset.weight) || 0;
  const actual = parseFloat(inp.value) || 0;
  const pct    = (actual / target) * 100;
  const score  = pct * weight / 100;

  // عرض النسبة تحت الحقل
  let small = inp.nextElementSibling;
  if (!small || small.tagName !== 'SMALL') {
    small = document.createElement('small');
    small.className = 'achievement-cell';
    inp.after(small);
  }
  small.textContent = Math.round(pct) + '% تحقق';
  small.className = 'achievement-cell ' + (
    pct>=100 ? 'ach-great' : pct>=80 ? 'ach-good' : pct>=60 ? 'ach-warn' : 'ach-bad'
  );

  // تحديث مجموع النقاط في الصف
  const row       = inp.closest('tr');
  const allInputs = row.querySelectorAll('.kpi-input');
  let total = 0;
  allInputs.forEach(i => {
    const t = parseFloat(i.dataset.target) || 1;
    const w = parseFloat(i.dataset.weight) || 0;
    const v = parseFloat(i.value) || 0;
    total += ((v/t)*100) * w / 100;
  });
  const totalCell = document.getElementById('totalScore_' + empId);
  if (totalCell) totalCell.textContent = total.toFixed(1);
}
</script>
@endsection
