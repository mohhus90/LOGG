@extends('admin.layouts.admin')
@section('title') إدخال حضور دفعي @endsection
@section('start') الحضور والانصراف @endsection
@section('home') <a href="{{ route('attendance.index') }}">الحضور والانصراف</a> @endsection
@section('startpage') إدخال دفعي @endsection
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
  <h3 class="card-title"><i class="fas fa-list-check ml-2"></i>إدخال حضور دفعي
    <span class="badge badge-light mr-2">{{ $employees->count() }} موظف</span>
  </h3>
  <div class="card-tools">
    <a href="{{ route('attendance.excel_import_form') }}" class="btn btn-sm btn-warning"><i class="fas fa-file-excel"></i> Excel</a>
    <a href="{{ route('fingerprint_devices.index') }}" class="btn btn-sm btn-info mr-1"><i class="fas fa-fingerprint"></i> بصمة</a>
  </div>
</div>

@if($employees->isEmpty())
<div class="card-body text-center py-5">
  <i class="fas fa-users fa-3x text-muted mb-3"></i><h5>لا يوجد موظفون في النظام</h5>
  <a href="{{ route('employees.create') }}" class="btn btn-success mt-2"><i class="fas fa-plus ml-1"></i>إضافة موظف</a>
</div>
@else

<div class="card-body pb-1">
  <div class="row align-items-end">
    <div class="col-md-3">
      <form method="GET" action="{{ route('attendance.bulk_create') }}" id="dForm">
        <label>التاريخ <span class="text-danger">*</span></label>
        <input type="date" name="date" class="form-control" value="{{ $date }}" onchange="document.getElementById('dForm').submit()">
      </form>
    </div>
    <div class="col-md-9">
      <label class="d-block">إجراءات سريعة</label>
      <button type="button" class="btn btn-sm btn-success ml-1" onclick="markAll(1)">✅ الكل حضر</button>
      <button type="button" class="btn btn-sm btn-danger ml-1" onclick="markAll(2)">❌ الكل غياب</button>
      <button type="button" class="btn btn-sm btn-warning ml-1" onclick="askTimes()">⏰ أوقات موحدة</button>
      <button type="button" class="btn btn-sm btn-outline-secondary ml-1" onclick="clearTimes()">🗑 مسح الأوقات</button>
      <small class="text-muted mr-2"><span class="badge badge-success">{{ count($existing) }}</span> مسجل مسبقاً</small>
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
  <th>#</th><th>كود</th><th>اسم الموظف</th><th>Finger ID</th><th>الشيفت</th>
  <th>حضور <br><input type="time" id="gIn" class="form-control form-control-sm d-inline-block mt-1" style="width:90px" onchange="setGIn(this.value)"></th>
  <th>انصراف <br><input type="time" id="gOut" class="form-control form-control-sm d-inline-block mt-1" style="width:90px" onchange="setGOut(this.value)"></th>
  <th>الحالة</th><th>ملاحظات</th>
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
      <option value="1" {{ $rec&&$rec->status==1?'selected':(!$rec?'selected':'') }}>✅ حضر</option>
      <option value="2" {{ $rec&&$rec->status==2?'selected':'' }}>❌ غياب</option>
      <option value="3" {{ $rec&&$rec->status==3?'selected':'' }}>🏖 إجازة</option>
      <option value="4" {{ $rec&&$rec->status==4?'selected':'' }}>📅 إج. رسمية</option>
      <option value="5" {{ $rec&&$rec->status==5?'selected':'' }}>🏢 مأمورية</option>
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
    <button type="submit" class="btn btn-success"><i class="fas fa-save ml-1"></i>حفظ ({{ $employees->count()-count($existing) }} موظف)</button>
    <a href="{{ route('attendance.index') }}" class="btn btn-secondary mr-2">رجوع</a>
  </div>
  <small class="text-muted align-self-center"><i class="fas fa-info-circle"></i> الصفوف الخضراء مسجلة مسبقاً</small>
</div>
</form>
@endif
</div>
</div>
@endsection
@section('js')
<script>
function markAll(v){document.querySelectorAll('.ss:not(:disabled)').forEach(s=>{s.value=v;onSC(s);});}
function onSC(s){const r=s.closest('tr');if(s.value==='2'){r.querySelectorAll('.ci,.co').forEach(i=>{if(!i.readOnly)i.value='';});r.classList.add('marked-absent');}else{r.classList.remove('marked-absent');}}
function setGIn(v){document.querySelectorAll('.ci:not([readonly])').forEach(i=>{const r=i.closest('tr');if(r.querySelector('.ss').value==='1')i.value=v;});}
function setGOut(v){document.querySelectorAll('.co:not([readonly])').forEach(i=>{const r=i.closest('tr');if(r.querySelector('.ss').value==='1')i.value=v;});}
function askTimes(){const si=prompt('وقت الحضور الافتراضي:','08:00');const so=prompt('وقت الانصراف الافتراضي:','17:00');if(!si||!so)return;document.getElementById('gIn').value=si;setGIn(si);document.getElementById('gOut').value=so;setGOut(so);}
function clearTimes(){if(!confirm('مسح الأوقات؟'))return;document.querySelectorAll('.ci:not([readonly]),.co:not([readonly])').forEach(i=>i.value='');}
</script>
@endsection
