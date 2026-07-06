{{-- FILE: resources/views/admin/branch_commissions/calculate.blade.php --}}
@extends('admin.layouts.admin')
@section('title') احتساب عمولات الفروع @endsection
@section('start') الرواتب والمؤثرات @endsection
@section('home') <a href="{{ route('branch_commissions.index') }}">عمولات الفروع</a> @endsection
@section('startpage') احتساب العمولات @endsection

@section('css')
<style>
.achievement-bar { height:10px; border-radius:5px; background:#e9ecef; overflow:hidden; }
.achievement-bar-fill { height:100%; border-radius:5px; transition:.3s; }
.branch-card { border-right:4px solid #dee2e6; }
.branch-card.success { border-right-color:#28a745; }
.branch-card.warning { border-right-color:#ffc107; }
.branch-card.danger  { border-right-color:#dc3545; }
.branch-card.muted   { border-right-color:#6c757d; }
.ind-bar  { height:6px; border-radius:3px; background:#e9ecef; overflow:hidden; margin-top:3px; }
.ind-fill { height:100%; border-radius:3px; }
.basis-branch     { background:#cfe2ff; color:#084298; font-size:.72em; padding:1px 5px; border-radius:3px; }
.basis-individual { background:#d1e7dd; color:#0a3622; font-size:.72em; padding:1px 5px; border-radius:3px; }
</style>
@endsection

@section('content')
<div class="col-12">

  {{-- فلتر الشهر --}}
  <div class="d-flex align-items-center mb-3 flex-wrap" style="gap:8px">
    <form method="GET" class="form-inline">
      <select name="month" class="form-control ml-2">
        @foreach(['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'] as $i => $m)
          <option value="{{ $i+1 }}" {{ $month == $i+1 ? 'selected' : '' }}>{{ $m }}</option>
        @endforeach
      </select>
      <input type="number" name="year" class="form-control mr-2 ml-2" style="width:90px" value="{{ $year }}">
      <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
    </form>
    <a href="{{ route('branch_commissions.targets', ['month'=>$month,'year'=>$year]) }}"
       class="btn btn-outline-warning btn-sm">
      <i class="fas fa-bullseye ml-1"></i>تعديل الأهداف
    </a>
    <a href="{{ route('branch_commissions.employee_targets', ['month'=>$month,'year'=>$year]) }}"
       class="btn btn-outline-secondary btn-sm">
      <i class="fas fa-user-tag ml-1"></i>التارجت الفردي
    </a>
    <a href="{{ route('commissions_v2.sales', ['month'=>$month,'year'=>$year]) }}"
       class="btn btn-outline-info btn-sm">
      <i class="fas fa-cash-register ml-1"></i>تعديل المبيعات
    </a>
  </div>

  {{-- مفتاح الألوان --}}
  <div class="mb-3 d-flex flex-wrap" style="gap:6px;font-size:.8em">
    <span class="basis-individual px-2 py-1">بائع: تحقيق فردي</span>
    <span class="basis-branch px-2 py-1">مدير: تحقيق الفرع</span>
    <span class="badge badge-light border">البائع والمدير مستقلان — كل منهما يُحاسَب على أساسه</span>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if(empty($preview))
    <div class="card">
      <div class="card-body text-center py-5">
        <i class="fas fa-calculator fa-3x text-muted mb-3"></i>
        <h5>لا توجد خطط عمولة نشطة</h5>
        <a href="{{ route('branch_commissions.create') }}" class="btn btn-primary mt-2">
          إنشاء خطة عمولة
        </a>
      </div>
    </div>
  @else

  @php
    $grandTotal = 0;
    $entryIndex = 0;
    $monthNames = ['','يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
  @endphp

  <form action="{{ route('branch_commissions.confirm') }}" method="POST">
    @csrf
    <input type="hidden" name="month" value="{{ $month }}">
    <input type="hidden" name="year"  value="{{ $year }}">

    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="mb-0">
        <i class="fas fa-calculator ml-2 text-primary"></i>
        معاينة العمولات — {{ $monthNames[$month] }} {{ $year }}
      </h5>
      <button type="submit" class="btn btn-success">
        <i class="fas fa-check ml-1"></i>اعتماد العمولات المحددة وإضافتها للرواتب
      </button>
    </div>

    @foreach($preview as $item)
    @php
      $pct      = $item['achievement_pct'];
      $color    = $pct >= 100 ? 'success' : ($pct >= 70 ? 'warning' : ($pct >= 60 ? 'danger' : 'muted'));
      $barColor = $pct >= 100 ? '#28a745' : ($pct >= 70 ? '#ffc107' : ($pct >= 60 ? '#dc3545' : '#adb5bd'));
    @endphp

    <div class="card branch-card {{ $color }} mb-4">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
          <div>
            <h5 class="mb-0">
              <i class="fas fa-map-marker-alt text-danger ml-2"></i>
              {{ $item['plan']->branch->branch_name ?? '—' }}
              <small class="text-muted mr-2" style="font-size:.8em">{{ $item['plan']->name }}</small>
            </h5>
          </div>
          @if(!$item['error'])
          <div class="text-left">
            <span class="badge badge-light border" style="font-size:.9em">
              تارجت الفرع: <strong>{{ number_format($item['target'], 0) }} ج.م</strong>
            </span>
            <span class="badge badge-light border mr-1" style="font-size:.9em">
              الفعلي: <strong>{{ number_format($item['actual_sales'], 0) }} ج.م</strong>
            </span>
            <span class="badge badge-{{ $color }} mr-1" style="font-size:.9em">
              {{ number_format($pct, 1) }}% تحقيق الفرع
            </span>
          </div>
          @endif
        </div>

        @if(!$item['error'])
        <div class="achievement-bar mt-2">
          <div class="achievement-bar-fill" style="width:{{ min($pct,100) }}%; background:{{ $barColor }}"></div>
        </div>

        {{-- شريحة الفرع — أساس عمولة المدير فقط --}}
        @if($item['matched_tier'])
          @php $t = $item['matched_tier']; @endphp
          <small class="mt-1 d-block">
            <span class="basis-branch ml-1">أساس المدير</span>
            شريحة الفرع:
            {{ $t['from_pct'] }}%
            @if(!is_null($t['to_pct'])) — {{ $t['to_pct'] }}% @else فأكثر @endif
            | مدير <strong>{{ $t['manager_rate'] }}%</strong>
            @if(($t['seller_rate'] ?? 0) > 0)
              | بائع <strong>{{ $t['seller_rate'] }}%</strong>
              <span class="text-muted">(تُطبَّق فردياً على كل بائع حسب تحقيقه)</span>
            @endif
          </small>
        @else
          <small class="mt-1 d-block text-danger">
            <i class="fas fa-times-circle ml-1"></i>
            الفرع لم يحقق الحد الأدنى — المدير لا يستحق عمولة إدارة
            (البائعون يُحاسَبون على تحقيقهم الفردي بشكل مستقل)
          </small>
        @endif
        @endif
      </div>

      <div class="card-body p-0">
        @if($item['error'])
          <div class="alert alert-warning m-3 mb-0">
            <i class="fas fa-exclamation-triangle ml-1"></i>
            {{ $item['error'] }}
            <a href="{{ route('branch_commissions.targets', ['month'=>$month,'year'=>$year]) }}"
               class="btn btn-sm btn-warning mr-2">تحديد التارجت</a>
          </div>
        @elseif(empty($item['members']))
          <div class="alert alert-info m-3 mb-0">
            لا يوجد أعضاء في هذه الخطة.
            <a href="{{ route('branch_commissions.edit', $item['plan']->id) }}">تعديل الخطة</a>
          </div>
        @else
        <div class="table-responsive">
        <table class="table table-sm table-bordered mb-0">
          <thead class="thead-light">
            <tr>
              <th style="width:35px">
                <input type="checkbox" class="plan-check-all"
                  data-plan="{{ $item['plan']->id }}"
                  onchange="togglePlan(this)">
              </th>
              <th>الموظف</th>
              <th>الدور</th>
              <th class="text-center">مبيعاته</th>
              <th class="text-center">تارجته / تحقيقه</th>
              <th>تفصيل العمولة</th>
              <th class="text-center">إجمالي</th>
            </tr>
          </thead>
          <tbody>
            @php $planTotal = 0; @endphp
            @foreach($item['members'] as $memberData)
            @php
              $planTotal  += $memberData['commission'];
              $grandTotal += $memberData['commission'];
              $daysWorked  = $memberData['days_worked'];
              $daysTotal   = $memberData['days_in_month'];
              $partialMonth = $daysWorked < $daysTotal;
              $empAch      = $memberData['emp_achievement'];
              $empTier     = $memberData['emp_matched_tier'] ?? null;
              $isManager   = $memberData['member']->role === 'manager';
              // لون شريط التحقيق الفردي
              $indColor = $empAch === null ? '#adb5bd' : ($empAch >= 100 ? '#28a745' : ($empAch >= 70 ? '#ffc107' : ($empAch >= 60 ? '#dc3545' : '#adb5bd')));
            @endphp
            <tr class="{{ $memberData['commission'] == 0 ? 'table-light' : '' }}">
              {{-- checkbox --}}
              <td class="text-center">
                @if(count($memberData['breakdown']) > 0)
                  @foreach($memberData['breakdown'] as $bIdx => $bItem)
                    <input type="hidden" name="entries[{{ $entryIndex }}][employee_id]"
                      value="{{ $memberData['member']->employee_id }}">
                    <input type="hidden" name="entries[{{ $entryIndex }}][amount]"
                      value="{{ $bItem['amount'] }}">
                    <input type="hidden" name="entries[{{ $entryIndex }}][rule_name]"
                      value="{{ $bItem['type'] }}">
                    <input type="hidden" name="entries[{{ $entryIndex }}][plan_name]"
                      value="{{ $item['plan']->name }}">
                    <div>
                      <input type="checkbox" name="entries[{{ $entryIndex }}][approved]"
                        value="1"
                        class="com-check plan-{{ $item['plan']->id }}"
                        title="{{ $bItem['type'] }}: {{ number_format($bItem['amount'], 2) }} ج.م"
                        {{ $bItem['amount'] > 0 ? 'checked' : 'disabled' }}>
                    </div>
                    @php $entryIndex++; @endphp
                  @endforeach
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>

              {{-- الموظف --}}
              <td>
                <strong>{{ $memberData['member']->employee->employee_name_A ?? '—' }}</strong>
                <br><small class="text-muted">{{ $memberData['member']->employee->employee_id ?? '' }}</small>
                @if(!empty($memberData['is_replacement']))
                  <br><span class="badge badge-info" style="font-size:.7em">بديل</span>
                @endif
              </td>

              {{-- الدور --}}
              <td class="text-center">
                <span class="badge {{ $isManager ? 'badge-primary' : 'badge-success' }}">
                  {{ $isManager ? 'مدير فرع' : 'بائع' }}
                </span>
                @if($memberData['member']->also_as_seller)
                  <br><span class="badge badge-warning mt-1">+بائع</span>
                @endif
              </td>

              {{-- مبيعاته --}}
              <td class="text-center">
                <strong>{{ number_format($memberData['emp_sales'], 0) }}</strong>
                <br><small class="text-muted">ج.م</small>
                @if($partialMonth)
                  <br><small class="text-info">{{ $daysWorked }} / {{ $daysTotal }} يوم</small>
                @endif
              </td>

              {{-- التارجت / التحقيق --}}
              <td class="text-center" style="min-width:130px">
                @if($isManager)
                  {{-- المدير: على أساس الفرع --}}
                  <span class="basis-branch">أساس الفرع</span>
                  <div class="mt-1" style="font-size:.85em">
                    {{ number_format($item['actual_sales'],0) }}
                    / {{ number_format($item['target'],0) }}
                  </div>
                  <div class="ind-bar mt-1">
                    <div class="ind-fill" style="width:{{ min($pct,100) }}%; background:{{ $barColor }}"></div>
                  </div>
                  <small class="{{ $pct >= 100 ? 'text-success' : ($pct >= 60 ? 'text-warning' : 'text-danger') }} font-weight-bold">
                    {{ number_format($pct,1) }}%
                  </small>
                @else
                  {{-- البائع: على أساس فردي --}}
                  <span class="basis-individual">فردي</span>
                  @if($memberData['effective_target'] > 0)
                    <div class="mt-1" style="font-size:.85em">
                      {{ number_format($memberData['emp_sales'],0) }}
                      / {{ number_format($memberData['effective_target'],0) }}
                      @if($partialMonth)
                        <br><small class="text-muted">({{ $daysWorked }}د من {{ number_format($memberData['base_target'],0) }})</small>
                      @endif
                    </div>
                    <div class="ind-bar mt-1">
                      <div class="ind-fill" style="width:{{ min($empAch??0,100) }}%; background:{{ $indColor }}"></div>
                    </div>
                    <small class="{{ ($empAch??0) >= 100 ? 'text-success' : (($empAch??0) >= 60 ? 'text-warning' : 'text-danger') }} font-weight-bold">
                      {{ $empAch !== null ? number_format($empAch,1).'%' : '—' }}
                    </small>
                    @if($empTier)
                      <br><small class="text-success">
                        <i class="fas fa-check-circle"></i>
                        شريحة {{ $empTier['from_pct'] }}%@if(!is_null($empTier['to_pct']))—{{ $empTier['to_pct'] }}%@else+@endif
                      </small>
                    @else
                      <br><small class="text-danger">
                        <i class="fas fa-times-circle"></i> لم يحقق الحد الأدنى
                      </small>
                    @endif
                  @else
                    <small class="text-muted">لم يُحدَّد التارجت الفردي</small>
                  @endif
                @endif
              </td>

              {{-- تفصيل العمولة --}}
              <td style="font-size:.82em">
                @forelse($memberData['breakdown'] as $bItem)
                  <div class="mb-1">
                    <span class="{{ ($bItem['basis'] ?? '') === 'branch' ? 'basis-branch' : 'basis-individual' }} ml-1">
                      {{ ($bItem['basis'] ?? '') === 'branch' ? 'فرع' : 'فردي' }}
                    </span>
                    <span class="text-muted">{{ $bItem['type'] }}</span>
                    <span class="text-muted mr-1">[{{ number_format($bItem['achievement'] ?? 0, 1) }}%]</span>:
                    {{ number_format($bItem['base'], 0) }} × {{ $bItem['rate'] }}%
                    = <strong class="text-success">{{ number_format($bItem['amount'], 2) }} ج.م</strong>
                  </div>
                @empty
                  <span class="text-muted">لا توجد عمولة</span>
                @endforelse
              </td>

              {{-- الإجمالي --}}
              <td class="text-center {{ $memberData['commission'] > 0 ? 'text-success font-weight-bold' : 'text-muted' }}">
                {{ $memberData['commission'] > 0 ? number_format($memberData['commission'], 2).' ج.م' : '—' }}
              </td>
            </tr>
            @endforeach
          </tbody>
          @if($planTotal > 0)
          <tfoot>
            <tr class="table-success">
              <td colspan="6" class="text-left font-weight-bold">
                إجمالي عمولات {{ $item['plan']->branch->branch_name ?? '' }}
              </td>
              <td class="text-center text-success font-weight-bold">
                {{ number_format($planTotal, 2) }} ج.م
              </td>
            </tr>
          </tfoot>
          @endif
        </table>
        </div>
        @endif
      </div>
    </div>
    @endforeach

    {{-- الإجمالي الكلي --}}
    @if($grandTotal > 0)
    <div class="card bg-success text-white mb-3">
      <div class="card-body d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0">
          <i class="fas fa-coins ml-2"></i>إجمالي جميع العمولات
        </h5>
        <h4 class="mb-0">{{ number_format($grandTotal, 2) }} ج.م</h4>
      </div>
    </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
      <button type="submit" class="btn btn-success btn-lg">
        <i class="fas fa-check ml-1"></i>اعتماد العمولات المحددة وإضافتها للرواتب
      </button>
      <a href="{{ route('branch_commissions.index') }}" class="btn btn-secondary">رجوع</a>
    </div>

  </form>
  @endif
</div>
@endsection

@section('script')
<script>
function togglePlan(masterCb) {
  const planId = masterCb.dataset.plan;
  document.querySelectorAll('.plan-' + planId).forEach(cb => {
    if (!cb.disabled) cb.checked = masterCb.checked;
  });
}
</script>
@endsection
