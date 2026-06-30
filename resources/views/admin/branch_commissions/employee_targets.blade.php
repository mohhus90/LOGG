{{-- FILE: resources/views/admin/branch_commissions/employee_targets.blade.php --}}
@extends('admin.layouts.admin')
@section('title') التارجت الفردي للموظفين @endsection
@section('start') الرواتب والمؤثرات @endsection
@section('home') <a href="{{ route('branch_commissions.index') }}">عمولات الفروع</a> @endsection
@section('startpage') التارجت الفردي @endsection

@section('content')
<div class="col-12">

  {{-- فلتر الشهر --}}
  <div class="card card-outline card-primary mb-3">
    <div class="card-body py-2">
      <form method="GET" class="form-inline">
        <label class="ml-2 font-weight-bold">الشهر:</label>
        <select name="month" class="form-control ml-2">
          @foreach(['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'] as $i => $m)
            <option value="{{ $i+1 }}" {{ $month == $i+1 ? 'selected' : '' }}>{{ $m }}</option>
          @endforeach
        </select>
        <input type="number" name="year" class="form-control mr-2 ml-2" style="width:90px" value="{{ $year }}">
        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
      </form>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if($plans->isEmpty())
    <div class="card"><div class="card-body text-center py-5 text-muted">
      لا توجد خطط نشطة.
      <a href="{{ route('branch_commissions.create') }}" class="btn btn-primary btn-sm mr-2">إنشاء خطة</a>
    </div></div>
  @else

  <form action="{{ route('branch_commissions.save_employee_targets') }}" method="POST">
    @csrf
    <input type="hidden" name="month" value="{{ $month }}">
    <input type="hidden" name="year"  value="{{ $year }}">

    @php
      $monthNames = ['','يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
    @endphp

    @foreach($plans as $plan)
    @php
      $branchTarget = $branchTargets->get($plan->branch_id)?->target_amount ?? 0;
    @endphp
    <div class="card mb-4">
      <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
          <i class="fas fa-map-marker-alt ml-2"></i>
          {{ $plan->branch->branch_name ?? '—' }} — {{ $plan->name }}
          <small class="mr-3" style="font-size:.8em">
            التارجت الكلي للفرع:
            <strong>{{ $branchTarget > 0 ? number_format($branchTarget, 0) . ' ج.م' : 'غير محدد' }}</strong>
          </small>
        </h5>
      </div>
      <div class="card-body p-0">
        <table class="table table-bordered mb-0">
          <thead class="thead-light">
            <tr>
              <th>الموظف</th>
              <th>الدور</th>
              <th style="width:230px">التارجت الفردي (ج.م)</th>
              <th class="text-center text-muted" style="font-size:.85em;width:120px">
                % من التارجت الكلي
              </th>
            </tr>
          </thead>
          <tbody>
            @php $planTotal = 0; @endphp
            @foreach($plan->members as $member)
            @php
              $key = $plan->id . '_' . $member->employee_id;
              $saved = $existing->get($key)?->target_amount ?? '';
              if ($saved) $planTotal += (float)$saved;
            @endphp
            <tr>
              <td>
                <strong>{{ $member->employee->employee_name_A ?? '—' }}</strong>
                <br><small class="text-muted">{{ $member->employee->employee_id ?? '' }}</small>
              </td>
              <td>
                <span class="badge {{ $member->role === 'manager' ? 'badge-primary' : 'badge-success' }}">
                  {{ $member->role === 'manager' ? 'مدير فرع' : 'بائع' }}
                </span>
                @if($member->also_as_seller)
                  <span class="badge badge-warning">+بائع</span>
                @endif
              </td>
              <td>
                <div class="input-group input-group-sm">
                  <input type="number"
                    name="targets[{{ $plan->id }}][{{ $member->employee_id }}]"
                    class="form-control emp-target"
                    data-plan="{{ $plan->id }}"
                    data-branch-target="{{ $branchTarget }}"
                    value="{{ $saved }}"
                    min="0" step="0.01"
                    placeholder="أدخل التارجت..."
                    oninput="updatePercentage(this)">
                  <div class="input-group-append">
                    <span class="input-group-text">ج.م</span>
                  </div>
                </div>
              </td>
              <td class="text-center">
                <span class="pct-display text-muted" id="pct_{{ $plan->id }}_{{ $member->employee_id }}" style="font-size:.9em">
                  @if($saved && $branchTarget > 0)
                    {{ round((float)$saved / $branchTarget * 100, 1) }}%
                  @else
                    —
                  @endif
                </span>
              </td>
            </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr class="table-light font-weight-bold">
              <td colspan="2">إجمالي التارجت المدخل</td>
              <td>
                <span class="plan-total" id="total_{{ $plan->id }}">
                  {{ number_format($planTotal, 0) }}
                </span> ج.م
                @if($branchTarget > 0)
                  @php $diff = $branchTarget - $planTotal; @endphp
                  <br>
                  <small class="{{ abs($diff) < 1 ? 'text-success' : 'text-danger' }}">
                    @if(abs($diff) < 1)
                      <i class="fas fa-check-circle"></i> يساوي التارجت الكلي
                    @elseif($diff > 0)
                      <i class="fas fa-exclamation-triangle"></i> باقي {{ number_format($diff, 0) }} ج.م لم يتم توزيعها
                    @else
                      <i class="fas fa-exclamation-triangle"></i> تجاوز بـ {{ number_format(abs($diff), 0) }} ج.م
                    @endif
                  </small>
                @endif
              </td>
              <td class="text-center">
                @if($branchTarget > 0 && $planTotal > 0)
                  {{ round($planTotal / $branchTarget * 100, 1) }}%
                @endif
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
    @endforeach

    <div class="d-flex justify-content-between align-items-center mb-4">
      <button type="submit" class="btn btn-success btn-lg">
        <i class="fas fa-save ml-1"></i>حفظ التارجت الفردي
      </button>
      <div>
        <a href="{{ route('branch_commissions.events', ['month'=>$month,'year'=>$year]) }}"
           class="btn btn-warning btn-sm">
          <i class="fas fa-exchange-alt ml-1"></i>أحداث منتصف الشهر
        </a>
        <a href="{{ route('branch_commissions.calculate', ['month'=>$month,'year'=>$year]) }}"
           class="btn btn-primary btn-sm mr-1">
          <i class="fas fa-calculator ml-1"></i>احتساب العمولات
        </a>
      </div>
    </div>
  </form>
  @endif
</div>
@endsection

@section('script')
<script>
function updatePercentage(input) {
  const planId  = input.dataset.plan;
  const branchT = parseFloat(input.dataset.branchTarget) || 0;
  const empId   = input.name.match(/\[(\d+)\]$/)[1];
  const val     = parseFloat(input.value) || 0;

  const pctEl = document.getElementById('pct_' + planId + '_' + empId);
  if (pctEl) {
    pctEl.textContent = branchT > 0 ? (val / branchT * 100).toFixed(1) + '%' : '—';
  }

  // تحديث الإجمالي
  let total = 0;
  document.querySelectorAll('.emp-target[data-plan="' + planId + '"]').forEach(inp => {
    total += parseFloat(inp.value) || 0;
  });
  const totalEl = document.getElementById('total_' + planId);
  if (totalEl) {
    totalEl.textContent = total.toLocaleString('ar-EG', {maximumFractionDigits: 0});
  }
}
</script>
@endsection
