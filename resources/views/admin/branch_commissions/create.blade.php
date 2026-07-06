{{-- FILE: resources/views/admin/branch_commissions/create.blade.php --}}
@extends('admin.layouts.admin')
@section('title') {{ isset($plan) ? 'تعديل خطة عمولة' : 'خطة عمولة جديدة' }} @endsection
@section('start') الرواتب والمؤثرات @endsection
@section('home') <a href="{{ route('branch_commissions.index') }}">عمولات الفروع</a> @endsection
@section('startpage') {{ isset($plan) ? 'تعديل' : 'إضافة' }} @endsection

@section('css')
<style>
.tier-row { background:#f8f9fa; border-radius:6px; padding:10px 12px; margin-bottom:8px; border:1px solid #dee2e6; }
.member-row { border-bottom:1px solid #f0f0f0; padding:6px 0; }
.member-row:last-child { border-bottom:none; }
.role-badge { display:inline-block; padding:3px 10px; border-radius:12px; font-size:.82em; font-weight:600; cursor:pointer; }
.role-badge.seller { background:#d4edda; color:#155724; }
.role-badge.manager { background:#cce5ff; color:#004085; }
</style>
@endsection

@section('content')
<div class="col-md-11 mx-auto">

@php $isEdit = isset($plan); @endphp
@php $existingMembers = $isEdit ? $plan->members->keyBy('employee_id') : collect(); @endphp

<form action="{{ $isEdit ? route('branch_commissions.update', $plan->id) : route('branch_commissions.store') }}"
      method="POST" id="planForm">
  @csrf

  {{-- ① معلومات الخطة --}}
  <div class="card card-primary">
    <div class="card-header">
      <h3 class="card-title">
        <i class="fas fa-store ml-2"></i>① بيانات الخطة
      </h3>
    </div>
    <div class="card-body">
      @if($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
      @endif

      <div class="row">
        <div class="col-md-5 form-group">
          <label>اسم الخطة <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control" required
            value="{{ old('name', $plan->name ?? '') }}"
            placeholder="مثال: عمولات فرع بدلة المنصورة">
        </div>
        <div class="col-md-4 form-group">
          <label>الفرع <span class="text-danger">*</span></label>
          <select name="branch_id" class="form-control" required>
            <option value="">— اختر الفرع —</option>
            @foreach($branches as $br)
              <option value="{{ $br->id }}"
                {{ old('branch_id', $plan->branch_id ?? '') == $br->id ? 'selected' : '' }}>
                {{ $br->branch_name }}
              </option>
            @endforeach
          </select>
        </div>
        @if($isEdit)
        <div class="col-md-3 form-group">
          <label>الحالة</label>
          <div class="mt-2">
            <div class="custom-control custom-switch">
              <input type="checkbox" class="custom-control-input" id="isActive"
                name="is_active" value="1" {{ $plan->is_active ? 'checked' : '' }}>
              <label class="custom-control-label" for="isActive">نشطة</label>
            </div>
          </div>
        </div>
        @endif
      </div>
      <div class="form-group">
        <label>وصف (اختياري)</label>
        <input type="text" name="description" class="form-control"
          value="{{ old('description', $plan->description ?? '') }}"
          placeholder="ملاحظات عن هذه الخطة...">
      </div>
    </div>
  </div>

  {{-- ② شرائح العمولة --}}
  <div class="card card-success">
    <div class="card-header">
      <h3 class="card-title">
        <i class="fas fa-layer-group ml-2"></i>② شرائح العمولة حسب نسبة تحقيق التارجت
      </h3>
    </div>
    <div class="card-body">
      <div class="alert alert-info py-2 mb-3" style="font-size:.88em">
        <i class="fas fa-info-circle ml-1"></i>
        حدد نسب العمولة لكل شريحة تحقيق. مثال: من 70% إلى 100% → بائع 1%، مدير 0.5%.
        <br>
        <strong>ملاحظة:</strong> الشريحة "من X فأكثر": اترك حقل "إلى" فارغاً (مثال: من 100% → تعني أكثر من 100%).
      </div>

      {{-- رأس الجدول --}}
      <div class="row font-weight-bold text-muted mb-2 px-2" style="font-size:.85em">
        <div class="col-md-3">من % (تحقيق التارجت)</div>
        <div class="col-md-3">إلى % (اتركه فارغاً = فأكثر)</div>
        <div class="col-md-2">عمولة البائع %</div>
        <div class="col-md-2">عمولة المدير %</div>
        <div class="col-md-2"></div>
      </div>

      <div id="tiersContainer">
        @if($isEdit && $plan->tiers)
          @foreach($plan->tiers as $i => $tier)
          <div class="tier-row">
            <div class="row align-items-center">
              <div class="col-md-3">
                <div class="input-group">
                  <input type="number" name="tier_from_pct[]" class="form-control"
                    value="{{ $tier['from_pct'] }}" min="0" max="999" step="0.01" placeholder="مثال: 60">
                  <div class="input-group-append"><span class="input-group-text">%</span></div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="input-group">
                  <input type="number" name="tier_to_pct[]" class="form-control"
                    value="{{ $tier['to_pct'] ?? '' }}" min="0" max="999" step="0.01" placeholder="∞ (بدون حد أعلى)">
                  <div class="input-group-append"><span class="input-group-text">%</span></div>
                </div>
              </div>
              <div class="col-md-2">
                <div class="input-group">
                  <input type="number" name="tier_seller[]" class="form-control"
                    value="{{ $tier['seller_rate'] ?? 0 }}" min="0" max="100" step="0.01">
                  <div class="input-group-append"><span class="input-group-text">%</span></div>
                </div>
              </div>
              <div class="col-md-2">
                <div class="input-group">
                  <input type="number" name="tier_manager[]" class="form-control"
                    value="{{ $tier['manager_rate'] ?? 0 }}" min="0" max="100" step="0.01">
                  <div class="input-group-append"><span class="input-group-text">%</span></div>
                </div>
              </div>
              <div class="col-md-2">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTier(this)">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </div>
          </div>
          @endforeach
        @else
          {{-- شرائح افتراضية للفهم --}}
          <div class="tier-row">
            <div class="row align-items-center">
              <div class="col-md-3">
                <div class="input-group">
                  <input type="number" name="tier_from_pct[]" class="form-control" value="60" min="0" max="999" step="0.01">
                  <div class="input-group-append"><span class="input-group-text">%</span></div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="input-group">
                  <input type="number" name="tier_to_pct[]" class="form-control" value="70" min="0" max="999" step="0.01" placeholder="∞">
                  <div class="input-group-append"><span class="input-group-text">%</span></div>
                </div>
              </div>
              <div class="col-md-2">
                <div class="input-group">
                  <input type="number" name="tier_seller[]" class="form-control" value="0.5" min="0" max="100" step="0.01">
                  <div class="input-group-append"><span class="input-group-text">%</span></div>
                </div>
              </div>
              <div class="col-md-2">
                <div class="input-group">
                  <input type="number" name="tier_manager[]" class="form-control" value="0.25" min="0" max="100" step="0.01">
                  <div class="input-group-append"><span class="input-group-text">%</span></div>
                </div>
              </div>
              <div class="col-md-2">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTier(this)"><i class="fas fa-trash"></i></button>
              </div>
            </div>
          </div>
          <div class="tier-row">
            <div class="row align-items-center">
              <div class="col-md-3">
                <div class="input-group">
                  <input type="number" name="tier_from_pct[]" class="form-control" value="70" min="0" max="999" step="0.01">
                  <div class="input-group-append"><span class="input-group-text">%</span></div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="input-group">
                  <input type="number" name="tier_to_pct[]" class="form-control" value="100" min="0" max="999" step="0.01" placeholder="∞">
                  <div class="input-group-append"><span class="input-group-text">%</span></div>
                </div>
              </div>
              <div class="col-md-2">
                <div class="input-group">
                  <input type="number" name="tier_seller[]" class="form-control" value="1.0" min="0" max="100" step="0.01">
                  <div class="input-group-append"><span class="input-group-text">%</span></div>
                </div>
              </div>
              <div class="col-md-2">
                <div class="input-group">
                  <input type="number" name="tier_manager[]" class="form-control" value="0.5" min="0" max="100" step="0.01">
                  <div class="input-group-append"><span class="input-group-text">%</span></div>
                </div>
              </div>
              <div class="col-md-2">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTier(this)"><i class="fas fa-trash"></i></button>
              </div>
            </div>
          </div>
          <div class="tier-row">
            <div class="row align-items-center">
              <div class="col-md-3">
                <div class="input-group">
                  <input type="number" name="tier_from_pct[]" class="form-control" value="100" min="0" max="999" step="0.01">
                  <div class="input-group-append"><span class="input-group-text">%</span></div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="input-group">
                  <input type="number" name="tier_to_pct[]" class="form-control" value="" min="0" max="999" step="0.01" placeholder="∞ (فأكثر)">
                  <div class="input-group-append"><span class="input-group-text">%</span></div>
                </div>
              </div>
              <div class="col-md-2">
                <div class="input-group">
                  <input type="number" name="tier_seller[]" class="form-control" value="1.25" min="0" max="100" step="0.01">
                  <div class="input-group-append"><span class="input-group-text">%</span></div>
                </div>
              </div>
              <div class="col-md-2">
                <div class="input-group">
                  <input type="number" name="tier_manager[]" class="form-control" value="1.0" min="0" max="100" step="0.01">
                  <div class="input-group-append"><span class="input-group-text">%</span></div>
                </div>
              </div>
              <div class="col-md-2">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTier(this)"><i class="fas fa-trash"></i></button>
              </div>
            </div>
          </div>
        @endif
      </div>

      <button type="button" class="btn btn-sm btn-outline-success mt-2" onclick="addTier()">
        <i class="fas fa-plus ml-1"></i>إضافة شريحة
      </button>

      {{-- مثال توضيحي --}}
      <div class="card bg-light border-0 mt-3">
        <div class="card-body py-2 px-3" style="font-size:.82em">
          <strong>مثال — بدلة المنصورة:</strong>
          <ul class="mb-0 mt-1 pr-3">
            <li>أقل من 60% → لا عمولة (لا تضيف شريحة لهذا النطاق)</li>
            <li>60% إلى 70% → بائع 0.5%، مدير 0.25%</li>
            <li>70% إلى 100% → بائع 1%، مدير 0.5%</li>
            <li>أكثر من 100% → بائع 1.25%، مدير 1% (اترك "إلى" فارغاً)</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  {{-- ③ أعضاء الخطة --}}
  <div class="card card-warning">
    <div class="card-header">
      <h3 class="card-title">
        <i class="fas fa-users ml-2"></i>③ أعضاء الخطة (بائعون ومديرو فروع)
      </h3>
    </div>
    <div class="card-body">
      <div class="alert alert-warning py-2 mb-3" style="font-size:.88em">
        <i class="fas fa-exclamation-triangle ml-1"></i>
        <strong>طريقة الاحتساب:</strong>
        البائع يأخذ عمولة على <strong>مبيعاته الفردية</strong>.
        مدير الفرع يأخذ عمولة على <strong>إجمالي مبيعات الفرع</strong>.
        إذا كان المدير يُحتسب كبائع أيضاً، يأخذ العمولتين.
      </div>

      {{-- بحث سريع --}}
      <div class="form-group">
        <input type="text" id="empSearch" class="form-control form-control-sm"
          placeholder="🔍 بحث عن موظف..." oninput="filterEmployees(this.value)" style="max-width:300px">
      </div>

      <div class="table-responsive" style="max-height:400px; overflow-y:auto;">
      <table class="table table-sm table-bordered mb-0">
        <thead class="thead-light" style="position:sticky;top:0;z-index:1;">
          <tr>
            <th style="width:40px">
              <input type="checkbox" id="checkAll" onclick="toggleAll(this)">
            </th>
            <th>الموظف</th>
            <th class="text-center">الدور في الخطة</th>
            <th class="text-center">يُحتسب أيضاً كبائع؟<br><small class="text-muted">(للمديرين فقط)</small></th>
          </tr>
        </thead>
        <tbody id="employeesTable">
          @foreach($employees as $emp)
          @php
            $member = $existingMembers->get($emp->id);
            $isChecked = $member !== null;
          @endphp
          <tr class="member-row {{ !$isChecked ? '' : '' }}" data-name="{{ $emp->employee_name_A }}">
            <td class="text-center">
              <input type="checkbox" name="members[]" value="{{ $emp->id }}"
                id="emp_{{ $emp->id }}"
                class="emp-check"
                {{ $isChecked ? 'checked' : '' }}
                onchange="toggleMemberRow(this)">
            </td>
            <td>
              <label for="emp_{{ $emp->id }}" class="mb-0" style="cursor:pointer;font-weight:{{ $isChecked ? '600' : '400' }}">
                {{ $emp->employee_name_A }}
              </label>
              <br><small class="text-muted">{{ $emp->employee_id }}</small>
            </td>
            <td class="text-center">
              <div class="member-controls {{ !$isChecked ? 'd-none' : '' }}" id="ctrl_{{ $emp->id }}">
                <div class="btn-group btn-group-sm" role="group">
                  <input type="radio" name="member_role[{{ $emp->id }}]" value="seller"
                    id="role_seller_{{ $emp->id }}"
                    {{ ($member && $member->role === 'manager') ? '' : 'checked' }}
                    class="d-none role-radio"
                    onchange="onRoleChange({{ $emp->id }})">
                  <label for="role_seller_{{ $emp->id }}"
                    class="btn btn-sm {{ ($member && $member->role === 'manager') ? 'btn-outline-success' : 'btn-success' }}">
                    <i class="fas fa-user-tie ml-1"></i>بائع
                  </label>
                  <input type="radio" name="member_role[{{ $emp->id }}]" value="manager"
                    id="role_manager_{{ $emp->id }}"
                    {{ ($member && $member->role === 'manager') ? 'checked' : '' }}
                    class="d-none role-radio"
                    onchange="onRoleChange({{ $emp->id }})">
                  <label for="role_manager_{{ $emp->id }}"
                    class="btn btn-sm {{ ($member && $member->role === 'manager') ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="fas fa-user-shield ml-1"></i>مدير فرع
                  </label>
                </div>
              </div>
            </td>
            <td class="text-center">
              <div class="member-controls {{ !$isChecked ? 'd-none' : '' }}" id="also_ctrl_{{ $emp->id }}">
                @php $showAlso = $member && $member->role === 'manager'; @endphp
                <div id="also_seller_wrap_{{ $emp->id }}" class="{{ !$showAlso ? 'd-none' : '' }}">
                  <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input"
                      id="also_{{ $emp->id }}"
                      name="member_also_seller[{{ $emp->id }}]"
                      value="1"
                      {{ ($member && $member->also_as_seller) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="also_{{ $emp->id }}">
                      نعم (يأخذ عمولة بائع أيضاً)
                    </label>
                  </div>
                </div>
                <span id="also_na_{{ $emp->id }}" class="{{ $showAlso ? 'd-none' : '' }} text-muted" style="font-size:.82em">—</span>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
      </div>
    </div>
  </div>

  <div class="card-footer bg-white">
    <button type="submit" class="btn btn-primary btn-lg">
      <i class="fas fa-save ml-1"></i>{{ $isEdit ? 'حفظ التعديلات' : 'إنشاء الخطة' }}
    </button>
    <a href="{{ route('branch_commissions.index') }}" class="btn btn-secondary mr-2">رجوع</a>
  </div>

</form>
</div>
@endsection

@section('script')
<script>
// ── الشرائح ──
function addTier() {
  const container = document.getElementById('tiersContainer');
  const div = document.createElement('div');
  div.className = 'tier-row';
  div.innerHTML = `
    <div class="row align-items-center">
      <div class="col-md-3">
        <div class="input-group">
          <input type="number" name="tier_from_pct[]" class="form-control" min="0" max="999" step="0.01" placeholder="مثال: 70">
          <div class="input-group-append"><span class="input-group-text">%</span></div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="input-group">
          <input type="number" name="tier_to_pct[]" class="form-control" min="0" max="999" step="0.01" placeholder="∞ (بدون حد أعلى)">
          <div class="input-group-append"><span class="input-group-text">%</span></div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="input-group">
          <input type="number" name="tier_seller[]" class="form-control" value="0" min="0" max="100" step="0.01">
          <div class="input-group-append"><span class="input-group-text">%</span></div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="input-group">
          <input type="number" name="tier_manager[]" class="form-control" value="0" min="0" max="100" step="0.01">
          <div class="input-group-append"><span class="input-group-text">%</span></div>
        </div>
      </div>
      <div class="col-md-2">
        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTier(this)"><i class="fas fa-trash"></i></button>
      </div>
    </div>`;
  container.appendChild(div);
}

function removeTier(btn) {
  const rows = document.querySelectorAll('.tier-row');
  if (rows.length <= 1) { alert('يجب أن تكون هناك شريحة واحدة على الأقل'); return; }
  btn.closest('.tier-row').remove();
}

// ── الموظفون ──
function toggleMemberRow(cb) {
  const empId = cb.value;
  const ctrl  = document.getElementById('ctrl_' + empId);
  const alsoCtrl = document.getElementById('also_ctrl_' + empId);
  const label = cb.closest('tr').querySelector('label');

  if (cb.checked) {
    ctrl.classList.remove('d-none');
    alsoCtrl.classList.remove('d-none');
    label.style.fontWeight = '600';
    // افتراض: بائع
    document.getElementById('role_seller_' + empId).checked = true;
    onRoleChange(empId);
  } else {
    ctrl.classList.add('d-none');
    alsoCtrl.classList.add('d-none');
    label.style.fontWeight = '400';
  }
}

function onRoleChange(empId) {
  const isManager = document.getElementById('role_manager_' + empId).checked;
  const wrap = document.getElementById('also_seller_wrap_' + empId);
  const na   = document.getElementById('also_na_' + empId);

  wrap.classList.toggle('d-none', !isManager);
  na.classList.toggle('d-none', isManager);

  // تحديث مظهر الأزرار
  const selLabel = document.querySelector(`label[for="role_seller_${empId}"]`);
  const mgrLabel = document.querySelector(`label[for="role_manager_${empId}"]`);
  selLabel.className = 'btn btn-sm ' + (isManager ? 'btn-outline-success' : 'btn-success');
  mgrLabel.className = 'btn btn-sm ' + (isManager ? 'btn-primary' : 'btn-outline-primary');
}

function toggleAll(masterCb) {
  document.querySelectorAll('.emp-check').forEach(cb => {
    if (cb.checked !== masterCb.checked) {
      cb.checked = masterCb.checked;
      toggleMemberRow(cb);
    }
  });
}

function filterEmployees(query) {
  const q = query.trim().toLowerCase();
  document.querySelectorAll('#employeesTable tr').forEach(tr => {
    const name = (tr.dataset.name || '').toLowerCase();
    tr.style.display = (!q || name.includes(q)) ? '' : 'none';
  });
}
</script>
@endsection
