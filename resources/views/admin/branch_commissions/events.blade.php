{{-- FILE: resources/views/admin/branch_commissions/events.blade.php --}}
@extends('admin.layouts.admin')
@section('title') أحداث منتصف الشهر @endsection
@section('start') الرواتب والمؤثرات @endsection
@section('home') <a href="{{ route('branch_commissions.index') }}">عمولات الفروع</a> @endsection
@section('startpage') أحداث منتصف الشهر @endsection

@section('css')
<style>
.event-card { border-right:4px solid #ffc107; border-radius:6px; }
</style>
@endsection

@section('content')
<div class="col-12">

  {{-- فلتر الشهر --}}
  <div class="card card-outline card-warning mb-3">
    <div class="card-body py-2">
      <form method="GET" class="form-inline">
        <label class="ml-2 font-weight-bold">الشهر:</label>
        <select name="month" class="form-control ml-2">
          @foreach(['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'] as $i => $m)
            <option value="{{ $i+1 }}" {{ $month == $i+1 ? 'selected' : '' }}>{{ $m }}</option>
          @endforeach
        </select>
        <input type="number" name="year" class="form-control mr-2 ml-2" style="width:90px" value="{{ $year }}">
        <button type="submit" class="btn btn-warning"><i class="fas fa-search"></i></button>
      </form>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

  @php
    $monthNames = ['','يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
  @endphp

  <div class="row">

    {{-- ── قائمة الأحداث الحالية ── --}}
    <div class="col-md-7">
      <div class="card">
        <div class="card-header bg-warning text-dark">
          <h5 class="mb-0">
            <i class="fas fa-exchange-alt ml-2"></i>
            أحداث {{ $monthNames[$month] }} {{ $year }}
          </h5>
        </div>
        <div class="card-body p-0">
          @if($events->isEmpty())
            <div class="text-center py-5 text-muted">
              <i class="fas fa-calendar-check fa-2x mb-3"></i>
              <p>لا توجد أحداث مسجلة لهذا الشهر</p>
            </div>
          @else
          <div class="table-responsive">
          <table class="table table-sm table-bordered mb-0">
            <thead class="thead-dark">
              <tr>
                <th>الفرع</th>
                <th>الموظف</th>
                <th class="text-center">آخر يوم حضور</th>
                <th>البديل</th>
                <th class="text-center">توزيع؟</th>
                <th style="width:50px"></th>
              </tr>
            </thead>
            <tbody>
              @foreach($events as $ev)
              @php
                $daysPresent = $ev->last_day_present;
                $daysAbsent  = $daysInMonth - $daysPresent;
              @endphp
              <tr>
                <td><i class="fas fa-map-marker-alt text-danger ml-1"></i>{{ $ev->branch->branch_name ?? '—' }}</td>
                <td>
                  <strong>{{ $ev->employee->employee_name_A ?? '—' }}</strong>
                  <br>
                  <small class="text-muted">
                    حضر {{ $daysPresent }} يوم | غاب {{ $daysAbsent }} يوم
                  </small>
                </td>
                <td class="text-center">
                  <span class="badge badge-warning">يوم {{ $daysPresent }}</span>
                </td>
                <td>
                  @if($ev->replacement)
                    <span class="text-primary">
                      <i class="fas fa-user-check ml-1"></i>{{ $ev->replacement->employee_name_A }}
                    </span>
                    <br><small class="text-muted">يغطي {{ $daysAbsent }} يوم</small>
                  @else
                    <span class="text-muted">—</span>
                  @endif
                </td>
                <td class="text-center">
                  @if($ev->redistribute_target)
                    <span class="badge badge-info">
                      <i class="fas fa-share-alt ml-1"></i>يتوزع على الزملاء
                    </span>
                  @else
                    <span class="text-muted">—</span>
                  @endif
                </td>
                <td class="text-center">
                  <a href="{{ route('branch_commissions.delete_event', $ev->id) }}"
                     class="btn btn-xs btn-danger"
                     onclick="return confirm('حذف هذا الحدث؟')">
                    <i class="fas fa-trash"></i>
                  </a>
                </td>
              </tr>
              @if($ev->notes)
              <tr class="table-light">
                <td colspan="6" style="font-size:.82em; color:#6c757d">
                  <i class="fas fa-comment ml-1"></i>{{ $ev->notes }}
                </td>
              </tr>
              @endif
              @endforeach
            </tbody>
          </table>
          </div>
          @endif
        </div>
        <div class="card-footer py-2">
          <a href="{{ route('branch_commissions.employee_targets', ['month'=>$month,'year'=>$year]) }}"
             class="btn btn-sm btn-outline-primary">
            <i class="fas fa-bullseye ml-1"></i>إدارة التارجت الفردي
          </a>
          <a href="{{ route('branch_commissions.calculate', ['month'=>$month,'year'=>$year]) }}"
             class="btn btn-sm btn-primary mr-1">
            <i class="fas fa-calculator ml-1"></i>احتساب العمولات
          </a>
        </div>
      </div>
    </div>

    {{-- ── إضافة حدث جديد ── --}}
    <div class="col-md-5">
      <div class="card card-warning card-outline">
        <div class="card-header">
          <h5 class="mb-0">
            <i class="fas fa-plus-circle ml-2"></i>إضافة حدث جديد
          </h5>
        </div>
        <form action="{{ route('branch_commissions.save_event') }}" method="POST">
          @csrf
          <input type="hidden" name="month" value="{{ $month }}">
          <input type="hidden" name="year"  value="{{ $year }}">

          <div class="card-body">
            @if($errors->any())
              <div class="alert alert-danger py-2">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
              </div>
            @endif

            {{-- الفرع --}}
            <div class="form-group">
              <label>الفرع <span class="text-danger">*</span></label>
              <select name="branch_id" class="form-control" id="evBranch" required onchange="loadBranchEmployees(this)">
                <option value="">— اختر الفرع —</option>
                @foreach($branches as $br)
                  <option value="{{ $br->id }}" {{ old('branch_id') == $br->id ? 'selected' : '' }}>
                    {{ $br->branch_name }}
                  </option>
                @endforeach
              </select>
            </div>

            {{-- الموظف الغائب --}}
            <div class="form-group">
              <label>الموظف الذي غادر / غاب <span class="text-danger">*</span></label>
              <select name="employee_id" class="form-control" id="evEmployee" required>
                <option value="">— اختر أولاً الفرع —</option>
                @foreach($employees as $emp)
                  <option value="{{ $emp->id }}" data-branch="{{ $emp->branch_id ?? '' }}"
                    {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                    {{ $emp->employee_name_A }} ({{ $emp->employee_id }})
                  </option>
                @endforeach
              </select>
              <small class="text-muted">
                ملاحظة: يمكن اختيار أي موظف حتى لو لم يكن ضمن أعضاء الخطة (كالبديل القادم من فرع آخر)
              </small>
            </div>

            {{-- آخر يوم حضور --}}
            <div class="form-group">
              <label>آخر يوم حضور <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="number" name="last_day_present" class="form-control"
                  id="evLastDay"
                  value="{{ old('last_day_present') }}"
                  min="1" max="{{ $daysInMonth }}" required
                  oninput="calcAbsentDays(this)"
                  placeholder="مثال: 19">
                <div class="input-group-append">
                  <span class="input-group-text">من {{ $daysInMonth }} يوم</span>
                </div>
              </div>
              <small class="text-muted" id="absentInfo">
                @if(old('last_day_present'))
                  أيام الغياب: {{ $daysInMonth - old('last_day_present') }} يوم
                @endif
              </small>
            </div>

            <hr>

            {{-- نوع التعامل مع أيام الغياب --}}
            <div class="form-group">
              <label class="font-weight-bold">ماذا يحدث بتارجت أيام الغياب؟</label>
              <div class="mt-2">
                <div class="custom-control custom-radio mb-2">
                  <input type="radio" id="opt_replacement" name="gap_option" value="replacement"
                    class="custom-control-input" {{ old('gap_option', 'replacement') === 'replacement' ? 'checked' : '' }}
                    onchange="toggleGapOption(this.value)">
                  <label class="custom-control-label" for="opt_replacement">
                    <i class="fas fa-user-check text-primary ml-1"></i>
                    يأخذها بديل (موظف آخر يغطي الأيام المتبقية)
                  </label>
                </div>
                <div class="custom-control custom-radio mb-2">
                  <input type="radio" id="opt_redistribute" name="gap_option" value="redistribute"
                    class="custom-control-input" {{ old('gap_option') === 'redistribute' ? 'checked' : '' }}
                    onchange="toggleGapOption(this.value)">
                  <label class="custom-control-label" for="opt_redistribute">
                    <i class="fas fa-share-alt text-info ml-1"></i>
                    يتوزع على الزملاء بنسب تارجتهم (بدون بديل)
                  </label>
                </div>
                <div class="custom-control custom-radio">
                  <input type="radio" id="opt_none" name="gap_option" value="none"
                    class="custom-control-input" {{ old('gap_option') === 'none' ? 'checked' : '' }}
                    onchange="toggleGapOption(this.value)">
                  <label class="custom-control-label" for="opt_none">
                    <i class="fas fa-minus-circle text-secondary ml-1"></i>
                    يُهمل (التارجت يقل بشكل مباشر)
                  </label>
                </div>
              </div>
            </div>

            {{-- حقل البديل (يظهر فقط عند اختيار "بديل") --}}
            <div id="replacementField" class="{{ old('gap_option', 'replacement') !== 'replacement' ? 'd-none' : '' }}">
              <div class="form-group">
                <label>الموظف البديل</label>
                <select name="replacement_employee_id" class="form-control">
                  <option value="">— بدون بديل —</option>
                  @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ old('replacement_employee_id') == $emp->id ? 'selected' : '' }}>
                      {{ $emp->employee_name_A }} ({{ $emp->employee_id }})
                    </option>
                  @endforeach
                </select>
              </div>
            </div>

            {{-- hidden inputs --}}
            <input type="hidden" name="redistribute_target" id="redistributeHidden" value="{{ old('gap_option') === 'redistribute' ? '1' : '0' }}">

            {{-- ملاحظات --}}
            <div class="form-group">
              <label>ملاحظات</label>
              <input type="text" name="notes" class="form-control" value="{{ old('notes') }}"
                placeholder="مثال: شادي غادر الفرع يوم 19 وأُرسل لفرع أخر">
            </div>

            {{-- مثال توضيحي --}}
            <div class="alert alert-light border p-2" style="font-size:.8em">
              <strong>مثال:</strong><br>
              • شادي آخر يوم حضور = 19 → بديل = أحمد رجب → (تارجت الـ11 يوم المتبقية تذهب لأحمد رجب)<br>
              • أحمد رجب غاب عن فرعه الأصلي 11 يوم → توزيع على الزملاء → (11/30 من تارجته تتوزع بنسب تارجتهم)
            </div>
          </div>

          <div class="card-footer">
            <button type="submit" class="btn btn-warning btn-block">
              <i class="fas fa-save ml-1"></i>حفظ الحدث
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- معاينة التارجت الفعلي بعد الأحداث --}}
  @if($events->isNotEmpty())
  <div class="card mt-2">
    <div class="card-header">
      <h5 class="mb-0">
        <i class="fas fa-table ml-2 text-info"></i>
        معاينة التارجت الفعلي بعد الأحداث — {{ $monthNames[$month] }} {{ $year }}
      </h5>
    </div>
    <div class="card-body p-0">
      @foreach($plans as $plan)
      @php
        $effectiveTargets = app(\App\Http\Controllers\Admin\BranchCommissionsController::class)
            ->getEffectiveTargets($plan, $month, $year);
        if (empty($effectiveTargets)) continue;
        $branchTarget = \App\Models\BranchTarget::where('com_code', auth()->guard('admin')->user()->com_code)
            ->where('branch_id', $plan->branch_id)->where('month', $month)->where('year', $year)
            ->value('target_amount') ?? 0;
      @endphp
      @if(collect($effectiveTargets)->isNotEmpty())
      <div class="px-3 pt-3 pb-1">
        <h6 class="font-weight-bold text-primary">
          <i class="fas fa-map-marker-alt ml-1"></i>
          {{ $plan->branch->branch_name ?? '—' }}
        </h6>
      </div>
      <div class="table-responsive px-3 pb-3">
      <table class="table table-sm table-bordered">
        <thead class="thead-light">
          <tr>
            <th>الموظف</th>
            <th class="text-center">التارجت الأصلي</th>
            <th class="text-center">التعديلات</th>
            <th class="text-center">التارجت الفعلي</th>
            <th class="text-center">% من إجمالي الفرع</th>
          </tr>
        </thead>
        <tbody>
          @php $totalEffective = 0; @endphp
          @foreach($effectiveTargets as $empId => $data)
          @php $totalEffective += $data['effective']; @endphp
          <tr>
            <td>
              <strong>{{ $data['employee']->employee_name_A ?? '—' }}</strong>
              @if(!empty($data['adjustments']))
                <br>
                @foreach($data['adjustments'] as $adj)
                  @if($adj['type'] === 'departure')
                    <small class="text-warning">
                      <i class="fas fa-sign-out-alt ml-1"></i>
                      غادر يوم {{ $adj['last_day'] }} (حضر {{ $adj['days_present'] }} يوم | غاب {{ $adj['days_absent'] }} يوم)
                    </small>
                  @elseif($adj['type'] === 'replacement')
                    <small class="text-primary">
                      <i class="fas fa-user-check ml-1"></i>
                      بديل لـ {{ $effectiveTargets[$adj['for']]['employee']->employee_name_A ?? $adj['for'] }}
                      ({{ $adj['days'] }} يوم)
                    </small>
                  @elseif($adj['type'] === 'redistribution')
                    <small class="text-info">
                      <i class="fas fa-share-alt ml-1"></i>
                      أضيف من توزيع
                      {{ $effectiveTargets[$adj['from']]['employee']->employee_name_A ?? $adj['from'] }}
                      ({{ round($adj['ratio']*100) }}%)
                    </small>
                  @endif
                  <br>
                @endforeach
              @endif
            </td>
            <td class="text-center">{{ number_format($data['base'], 0) }}</td>
            <td class="text-center">
              @php $adj_total = $data['effective'] - $data['base']; @endphp
              @if($adj_total != 0)
                <span class="{{ $adj_total > 0 ? 'text-success' : 'text-danger' }}">
                  {{ $adj_total > 0 ? '+' : '' }}{{ number_format($adj_total, 0) }}
                </span>
              @else
                <span class="text-muted">—</span>
              @endif
            </td>
            <td class="text-center font-weight-bold">
              {{ number_format($data['effective'], 0) }}
            </td>
            <td class="text-center">
              @if($branchTarget > 0)
                {{ round($data['effective'] / $branchTarget * 100, 1) }}%
              @else
                —
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
        <tfoot class="table-light font-weight-bold">
          <tr>
            <td colspan="3">الإجمالي</td>
            <td class="text-center">{{ number_format($totalEffective, 0) }}</td>
            <td class="text-center">
              @if($branchTarget > 0)
                <span class="{{ abs($totalEffective - $branchTarget) < 2 ? 'text-success' : 'text-warning' }}">
                  {{ round($totalEffective / $branchTarget * 100, 1) }}%
                </span>
              @endif
            </td>
          </tr>
        </tfoot>
      </table>
      </div>
      @endif
      @endforeach
    </div>
  </div>
  @endif

</div>
@endsection

@section('script')
<script>
function calcAbsentDays(input) {
  const days = parseInt(input.value) || 0;
  const total = parseInt(input.max) || 30;
  const absent = total - days;
  document.getElementById('absentInfo').textContent =
    days > 0 ? 'أيام الغياب: ' + absent + ' يوم (من يوم ' + (days+1) + ' إلى نهاية الشهر)' : '';
}

function toggleGapOption(val) {
  const repField = document.getElementById('replacementField');
  const redistH  = document.getElementById('redistributeHidden');

  repField.classList.toggle('d-none', val !== 'replacement');
  redistH.value = (val === 'redistribute') ? '1' : '0';
}

function loadBranchEmployees(sel) {
  const branchId = sel.value;
  const empSel   = document.getElementById('evEmployee');

  // الحصول على الموظفين المسجلين في خطة هذا الفرع
  const planEmployees = @json($plans->mapWithKeys(fn($p) => [$p->branch_id => $p->members->map(fn($m) => $m->employee_id)]));

  const empIds = planEmployees[branchId] || [];

  Array.from(empSel.options).forEach(opt => {
    if (!opt.value) return;
    // نظهر جميع الموظفين (ليس فقط أعضاء الخطة — قد يكون البديل من فرع آخر)
    opt.style.display = '';
  });
}

// تهيئة عند التحميل
document.addEventListener('DOMContentLoaded', function() {
  const lastDay = document.getElementById('evLastDay');
  if (lastDay && lastDay.value) calcAbsentDays(lastDay);
  toggleGapOption(document.querySelector('input[name="gap_option"]:checked')?.value || 'replacement');
});
</script>
@endsection
