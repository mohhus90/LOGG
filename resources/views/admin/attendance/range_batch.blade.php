@extends('admin.layouts.admin')
@section('title') {{ __('admin.att_range_batch_title') }} @endsection
@section('start') {{ __('admin.att_title') }} @endsection
@section('home') <a href="{{ route('attendance.index') }}">{{ __('admin.att_title') }}</a> @endsection
@section('startpage') {{ __('admin.att_range_batch_entry') }} @endsection
@section('css')
<style>
.emp-list{max-height:380px;overflow-y:auto;overflow-x:hidden;border:1px solid #dee2e6;border-radius:4px}
.emp-list .form-check{display:flex;align-items:center;gap:8px;padding:6px 12px;border-bottom:1px solid #f1f1f1;margin:0}
.emp-list .form-check:last-child{border-bottom:none}
.emp-list .form-check:hover{background:#f8f9fa}
.emp-list .form-check-input{position:static;margin:0;flex:0 0 auto}
.emp-list .form-check-label{margin-bottom:0;flex:1 1 auto}
</style>
@endsection
@section('content')
<div class="col-12">
<div class="card card-success">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-calendar-alt ml-2"></i>{{ __('admin.att_range_batch_title') }}</h3>
  </div>

  @if(session('success'))<div class="alert alert-success mx-3 mt-3">{!! session('success') !!}</div>@endif
  @if($errors->any())
    <div class="alert alert-danger mx-3 mt-3">
      <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ route('attendance.range_batch_store') }}" id="rangeBatchForm">
    @csrf
    <div class="card-body">
      <div class="alert alert-info"><i class="fas fa-info-circle ml-1"></i>{{ __('admin.att_range_batch_info') }}</div>

      <div class="row">
        <div class="col-md-3 form-group">
          <label>{{ __('admin.att_from_date') }} <span class="text-danger">*</span></label>
          <input type="date" name="from_date" class="form-control" value="{{ old('from_date', today()->format('Y-m-d')) }}" required>
        </div>
        <div class="col-md-3 form-group">
          <label>{{ __('admin.att_to_date') }} <span class="text-danger">*</span></label>
          <input type="date" name="to_date" class="form-control" value="{{ old('to_date', today()->format('Y-m-d')) }}" required>
        </div>
        <div class="col-md-3 form-group">
          <label>{{ __('admin.att_status') }} <span class="text-danger">*</span></label>
          <select name="status" id="statusSelect" class="form-control" required>
            <option value="1" {{ old('status')=='1'?'selected':'' }}>✅ {{ __('admin.att_present') }}</option>
            <option value="2" {{ old('status')=='2'?'selected':'' }}>❌ {{ __('admin.att_absent') }}</option>
            <option value="3" {{ old('status')=='3'?'selected':'' }}>🏖 {{ __('admin.att_vacation') }}</option>
            <option value="4" {{ old('status')=='4'?'selected':'' }}>📅 {{ __('admin.att_official_vacation') }}</option>
            <option value="5" {{ old('status')=='5'?'selected':'' }}>🏢 {{ __('admin.att_mission') }}</option>
            <option value="6" {{ old('status')=='6'?'selected':'' }}>🛌 {{ __('admin.att_weekly_vacation') }}</option>
          </select>
        </div>
        <div class="col-md-3 form-group d-flex align-items-end">
          <small class="text-muted"><span id="dayCount">—</span> {{ __('admin.att_days_count') }}</small>
        </div>
      </div>

      <div class="row" id="timesRow">
        <div class="col-md-3 form-group">
          <label>{{ __('admin.att_check_in') }}</label>
          <input type="time" name="check_in_time" class="form-control" value="{{ old('check_in_time') }}">
        </div>
        <div class="col-md-3 form-group">
          <label>{{ __('admin.att_check_out') }}</label>
          <input type="time" name="check_out_time" class="form-control" value="{{ old('check_out_time') }}">
        </div>
        <div class="col-md-6 form-group">
          <label>{{ __('admin.notes') }}</label>
          <input type="text" name="notes" class="form-control" value="{{ old('notes') }}">
        </div>
      </div>

      <div class="row">
        <div class="col-md-6 form-group form-check ml-2">
          <input type="checkbox" name="skip_weekly_off" id="skipWeeklyOff" class="form-check-input" value="1" {{ old('skip_weekly_off','1')?'checked':'' }}>
          <label class="form-check-label" for="skipWeeklyOff">{{ __('admin.att_skip_weekly_off') }}</label>
        </div>
        <div class="col-md-6 form-group form-check ml-2">
          <input type="checkbox" name="overwrite_existing" id="overwriteExisting" class="form-check-input" value="1" {{ old('overwrite_existing')?'checked':'' }}>
          <label class="form-check-label" for="overwriteExisting">{{ __('admin.att_overwrite_existing') }}</label>
          <small class="form-text text-muted">{{ __('admin.att_overwrite_hint') }}</small>
        </div>
      </div>

      <hr>

      <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
        <label class="mb-0">{{ __('admin.att_select_employees') }} <span class="text-danger">*</span>
          <span class="badge badge-success mr-1"><span id="selCount">0</span> {{ __('admin.att_selected_count') }}</span>
        </label>
        <div class="d-flex flex-wrap" style="gap:6px">
          <select id="filterDept" class="form-control form-control-sm d-inline-block" style="width:150px">
            <option value="">{{ __('admin.att_filter_dept') }} — {{ __('admin.all') }}</option>
            @foreach($departments as $d)<option value="{{ $d->id }}">{{ $d->dep_name }}</option>@endforeach
          </select>
          <select id="filterBranch" class="form-control form-control-sm d-inline-block" style="width:150px">
            <option value="">{{ __('admin.att_filter_branch') }} — {{ __('admin.all') }}</option>
            @foreach($branches as $br)<option value="{{ $br->id }}">{{ $br->branch_name }}</option>@endforeach
          </select>
          <select id="filterClient" class="form-control form-control-sm d-inline-block" style="width:150px">
            <option value="">{{ __('admin.att_filter_client') }} — {{ __('admin.all') }}</option>
            @foreach($clients as $cl)<option value="{{ $cl->id }}">{{ $cl->client_name }}</option>@endforeach
          </select>
          <input type="text" id="empSearch" class="form-control form-control-sm d-inline-block" style="width:200px" placeholder="{{ __('admin.att_search_employee') }}">
          <button type="button" class="btn btn-sm btn-outline-success" onclick="toggleAll(true)">{{ __('admin.att_select_all') }}</button>
          <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAll(false)">{{ __('admin.att_deselect_all') }}</button>
        </div>
      </div>

      @if($employees->isEmpty())
        <div class="text-center py-4 text-muted">{{ __('admin.att_no_employees') }}</div>
      @else
        <div class="emp-list">
          @foreach($employees as $emp)
          <div class="form-check emp-row" data-name="{{ mb_strtolower($emp->employee_name_A) }}"
               data-dept="{{ $emp->emp_departments_id }}" data-branch="{{ $emp->branches_id }}" data-client="{{ $emp->client_id }}">
            <input type="checkbox" name="employee_ids[]" class="form-check-input emp-check" id="emp{{ $emp->id }}" value="{{ $emp->id }}"
              {{ collect(old('employee_ids'))->contains($emp->id) ? 'checked' : '' }}>
            <label class="form-check-label d-block" for="emp{{ $emp->id }}">
              {{ $emp->employee_name_A }}
              <small class="text-muted">({{ $emp->employee_id }})</small>
            </label>
          </div>
          @endforeach
        </div>
      @endif
    </div>

    <div class="card-footer">
      <button type="submit" class="btn btn-success" {{ $employees->isEmpty() ? 'disabled' : '' }}>
        <i class="fas fa-save ml-1"></i>{{ __('admin.att_range_submit') }}
      </button>
      <a href="{{ route('attendance.index') }}" class="btn btn-secondary mr-2">{{ __('admin.back') }}</a>
    </div>
  </form>
</div>
</div>
@endsection
@section('script')
<script>
function updateSelCount() {
  document.getElementById('selCount').textContent = document.querySelectorAll('.emp-check:checked').length;
}

function toggleAll(state) {
  document.querySelectorAll('.emp-row').forEach(function(row) {
    if (row.style.display === 'none') return;
    var cb = row.querySelector('.emp-check');
    if (cb) cb.checked = state;
  });
  updateSelCount();
}

function toggleTimesRow() {
  var status = document.getElementById('statusSelect').value;
  document.getElementById('timesRow').style.display = (status === '1') ? '' : 'none';
}

function updateDayCount() {
  var from = document.querySelector('input[name=from_date]').value;
  var to   = document.querySelector('input[name=to_date]').value;
  var el   = document.getElementById('dayCount');
  if (!from || !to) { el.textContent = '—'; return; }
  var d1 = new Date(from), d2 = new Date(to);
  var diff = Math.round((d2 - d1) / (1000 * 60 * 60 * 24)) + 1;
  el.textContent = diff > 0 ? diff : '—';
}

document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.emp-check').forEach(function(cb) {
    cb.addEventListener('change', updateSelCount);
  });
  updateSelCount();

  document.getElementById('statusSelect').addEventListener('change', toggleTimesRow);
  toggleTimesRow();

  document.querySelector('input[name=from_date]').addEventListener('change', updateDayCount);
  document.querySelector('input[name=to_date]').addEventListener('change', updateDayCount);
  updateDayCount();

  function applyFilters() {
    var term   = document.getElementById('empSearch').value.trim().toLowerCase();
    var dept   = document.getElementById('filterDept').value;
    var branch = document.getElementById('filterBranch').value;
    var client = document.getElementById('filterClient').value;

    document.querySelectorAll('.emp-row').forEach(function(row) {
      var visible = row.dataset.name.indexOf(term) !== -1
        && (!dept   || row.dataset.dept   === dept)
        && (!branch || row.dataset.branch === branch)
        && (!client || row.dataset.client === client);
      row.style.display = visible ? '' : 'none';
    });
  }

  document.getElementById('empSearch').addEventListener('input', applyFilters);
  document.getElementById('filterDept').addEventListener('change', applyFilters);
  document.getElementById('filterBranch').addEventListener('change', applyFilters);
  document.getElementById('filterClient').addEventListener('change', applyFilters);

  document.getElementById('rangeBatchForm').addEventListener('submit', function(e) {
    if (document.querySelectorAll('.emp-check:checked').length === 0) {
      e.preventDefault();
      alert('{{ __('admin.att_select_employee') }}');
    }
  });
});
</script>
@endsection
