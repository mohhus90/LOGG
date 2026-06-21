@extends('admin.layouts.admin')
@section('title') {{ __('admin.att_bulk_title') }} @endsection
@section('start') {{ __('admin.att_title') }} @endsection
@section('home') <a href="{{ route('attendance.index') }}">{{ __('admin.att_title') }}</a> @endsection
@section('startpage') {{ __('admin.att_bulk_entry') }} @endsection
@section('css')
<style>
.att-table td,.att-table th{padding:5px 7px;font-size:13px;vertical-align:middle}
.att-table input[type=time]{width:105px}.att-table select{width:115px;font-size:12px}
tr.already-saved{background:#f0fff4!important}tr.marked-absent{background:#fff5f5!important}
.sticky-header th{position:sticky;top:0;z-index:10;background:#343a40}
</style>
@endsection
@section('content')
<div class="col-12">
<div class="card card-success">
<div class="card-header">
  <h3 class="card-title"><i class="fas fa-list-check ml-2"></i>{{ __('admin.att_bulk_title') }}
    <span class="badge badge-light mr-2">{{ $employees->count() }} {{ __('admin.att_employee') }}</span>
  </h3>
  <div class="card-tools">
    <a href="{{ route('attendance.excel_import_form') }}" class="btn btn-sm btn-warning"><i class="fas fa-file-excel"></i> Excel</a>
    <a href="{{ route('fingerprint_devices.index') }}" class="btn btn-sm btn-info mr-1"><i class="fas fa-fingerprint"></i> {{ __('admin.fingerprint') }}</a>
  </div>
</div>

@if($employees->isEmpty())
<div class="card-body text-center py-5">
  <i class="fas fa-users fa-3x text-muted mb-3"></i><h5>{{ __('admin.att_no_employees') }}</h5>
  <a href="{{ route('employees.create') }}" class="btn btn-success mt-2"><i class="fas fa-plus ml-1"></i>{{ __('admin.add') }}</a>
</div>
@else

<div class="card-body pb-1">
  <div class="row align-items-end">
    <div class="col-md-3">
      <form method="GET" action="{{ route('attendance.bulk_create') }}" id="dForm">
        <label>{{ __('admin.att_date') }} <span class="text-danger">*</span></label>
        <input type="date" name="date" class="form-control" value="{{ $date }}" onchange="document.getElementById('dForm').submit()">
      </form>
    </div>
    <div class="col-md-9">
      <label class="d-block">{{ __('admin.att_quick_actions') }}</label>
      <button type="button" class="btn btn-sm btn-success ml-1" onclick="markAll(1)">✅ {{ __('admin.att_all_present') }}</button>
      <button type="button" class="btn btn-sm btn-danger ml-1" onclick="markAll(2)">❌ {{ __('admin.att_all_absent') }}</button>
      <button type="button" class="btn btn-sm btn-warning ml-1" onclick="askTimes()">⏰ {{ __('admin.att_unified_times') }}</button>
      <button type="button" class="btn btn-sm btn-outline-secondary ml-1" onclick="clearTimes()">🗑 {{ __('admin.att_clear_times') }}</button>
      <small class="text-muted mr-2"><span class="badge badge-success">{{ count($existing) }}</span> {{ __('admin.att_pre_saved') }}</small>
    </div>
  </div>
  @if(session('success'))<div class="alert alert-success mt-2">{{ session('success') }}</div>@endif
</div>

<form action="{{ route('attendance.bulk_store') }}" method="POST">
@csrf
<input type="hidden" name="attendance_date" value="{{ $date }}">
<div class="card-body pt-0">
<div class="table-responsive">
<table class="table table-bordered table-sm att-table sticky-header">
<thead class="thead-dark">
<tr>
  <th>#</th>
  <th>{{ __('admin.emp_code') }}</th>
  <th>{{ __('admin.emp_name_ar') }}</th>
  <th>Finger ID</th>
  <th>{{ __('admin.att_shift') }}</th>
  <th>{{ __('admin.att_check_in') }} <br><input type="time" id="gIn" class="form-control form-control-sm d-inline-block mt-1" style="width:90px" onchange="setGIn(this.value)"></th>
  <th>{{ __('admin.att_check_out') }} <br><input type="time" id="gOut" class="form-control form-control-sm d-inline-block mt-1" style="width:90px" onchange="setGOut(this.value)"></th>
  <th>{{ __('admin.att_status') }}</th>
  <th>{{ __('admin.notes') }}</th>
</tr>
</thead>
<tbody>
@foreach($employees as $emp)
@php $saved=in_array($emp->id,$existing); $rec=$existingRecords[$emp->id]??null; @endphp
<tr class="{{ $saved?'already-saved':'' }}">
  <td>{{ $loop->iteration }}</td>
  <td><small>{{ $emp->employee_id }}</small></td>
  <td>{{ $emp->employee_name_A }}@if($saved) <span class="badge badge-success">✓</span>@endif</td>
  <td><code>{{ $emp->finger_id??'—' }}</code></td>
  <td>
    @if($emp->shifts_type)
      <small class="text-info">{{ $emp->shifts_type->type }}</small><br>
      <small class="text-muted">{{ $emp->shifts_type->from_time }}-{{ $emp->shifts_type->to_time }}</small>
    @else<small class="text-muted">—</small>@endif
  </td>
  <td><input type="time" name="records[{{ $emp->id }}][check_in]" class="form-control form-control-sm ci" value="{{ $rec?$rec->check_in_time:'' }}" {{ $saved?'readonly':'' }}></td>
  <td><input type="time" name="records[{{ $emp->id }}][check_out]" class="form-control form-control-sm co" value="{{ $rec?$rec->check_out_time:'' }}" {{ $saved?'readonly':'' }}></td>
  <td>
    <select name="records[{{ $emp->id }}][status]" class="form-control form-control-sm ss" onchange="onSC(this)" {{ $saved?'disabled':'' }}>
      <option value="1" {{ $rec&&$rec->status==1?'selected':(!$rec?'selected':'') }}>✅ {{ __('admin.att_present') }}</option>
      <option value="2" {{ $rec&&$rec->status==2?'selected':'' }}>❌ {{ __('admin.att_absent') }}</option>
      <option value="3" {{ $rec&&$rec->status==3?'selected':'' }}>🏖 {{ __('admin.att_vacation') }}</option>
      <option value="4" {{ $rec&&$rec->status==4?'selected':'' }}>📅 {{ __('admin.att_official_vacation') }}</option>
      <option value="5" {{ $rec&&$rec->status==5?'selected':'' }}>🏢 {{ __('admin.att_mission') }}</option>
    </select>
  </td>
  <td><input type="text" name="records[{{ $emp->id }}][notes]" class="form-control form-control-sm" value="{{ $rec?$rec->notes:'' }}" placeholder="—" {{ $saved?'readonly':'' }}></td>
</tr>
@endforeach
</tbody>
</table>
</div>
</div>
<div class="card-footer d-flex justify-content-between">
  <div>
    <button type="submit" class="btn btn-success"><i class="fas fa-save ml-1"></i>{{ __('admin.save') }} ({{ $employees->count()-count($existing) }} {{ __('admin.att_employee') }})</button>
    <a href="{{ route('attendance.index') }}" class="btn btn-secondary mr-2">{{ __('admin.back') }}</a>
  </div>
  <small class="text-muted align-self-center"><i class="fas fa-info-circle"></i> {{ __('admin.att_green_rows_saved') }}</small>
</div>
</form>
@endif
</div>
</div>
@endsection
@section('script')
<script>
function markAll(status) {
  document.querySelectorAll('select[name*="[status]"]:not(:disabled)').forEach(function(sel) {
    sel.value = String(status);
    onStatusChange(sel);
  });
}

function onStatusChange(sel) {
  var row = sel.closest('tr');
  if (!row) return;
  var ci = row.querySelector('input[name*="[check_in]"]');
  var co = row.querySelector('input[name*="[check_out]"]');
  if (sel.value === '2') {
    if (ci && !ci.readOnly) ci.value = '';
    if (co && !co.readOnly) co.value = '';
    row.classList.add('marked-absent');
  } else {
    row.classList.remove('marked-absent');
  }
}

function setGIn(val) {
  document.querySelectorAll('input[name*="[check_in]"]:not([readonly])').forEach(function(inp) {
    var row = inp.closest('tr');
    var sel = row ? row.querySelector('select[name*="[status]"]') : null;
    if (!sel || sel.value === '1') inp.value = val;
  });
}

function setGOut(val) {
  document.querySelectorAll('input[name*="[check_out]"]:not([readonly])').forEach(function(inp) {
    var row = inp.closest('tr');
    var sel = row ? row.querySelector('select[name*="[status]"]') : null;
    if (!sel || sel.value === '1') inp.value = val;
  });
}

function askTimes() {
  var si = prompt('{{ __('admin.att_enter_checkin') }}', '08:00');
  if (!si) return;
  var so = prompt('{{ __('admin.att_enter_checkout') }}', '17:00');
  if (!so) return;

  var gIn  = document.getElementById('gIn');
  var gOut = document.getElementById('gOut');
  if (gIn)  { gIn.value  = si; setGIn(si);   }
  if (gOut) { gOut.value = so; setGOut(so);  }
}

function clearTimes() {
  if (!confirm('{{ __('admin.att_clear_times_confirm') }}')) return;
  document.querySelectorAll(
    'input[name*="[check_in]"]:not([readonly]), input[name*="[check_out]"]:not([readonly])'
  ).forEach(function(inp) { inp.value = ''; });
}

document.addEventListener('DOMContentLoaded', function () {
  var gIn  = document.getElementById('gIn');
  var gOut = document.getElementById('gOut');
  if (gIn)  gIn.addEventListener('change', function() { setGIn(this.value);  });
  if (gOut) gOut.addEventListener('change', function() { setGOut(this.value); });
});
</script>
@endsection
