@extends('admin.layouts.admin')
@section('title') التقارير @endsection
@section('start') التقارير @endsection
@section('home') <a href="{{ route('reports.index') }}">التقارير</a> @endsection
@section('startpage') عرض @endsection

@section('css')
<style>
.report-card { transition:.2s; cursor:pointer; border:2px solid transparent; }
.report-card:hover { border-color:var(--primary,#3490dc); transform:translateY(-2px); box-shadow:0 6px 20px rgba(0,0,0,.12); }
.report-card .card-body { padding:30px 20px; text-align:center; }
.report-card i { font-size:2.5rem; margin-bottom:10px; }
.export-form select, .export-form input { margin-bottom:8px; }
</style>
@endsection

@section('content')
<div class="col-12">
  <h4 class="mb-4"><i class="fas fa-chart-bar ml-2 text-primary"></i>التقارير والتصدير</h4>

  <div class="row">

    {{-- ══ الحضور ══ --}}
    <div class="col-md-6 col-lg-3 mb-4">
      <div class="card report-card" data-toggle="collapse" data-target="#attendanceForm">
        <div class="card-body">
          <i class="fas fa-fingerprint text-primary"></i>
          <h5 class="mt-2 mb-0">الحضور والانصراف</h5>
          <small class="text-muted">Excel / طباعة PDF</small>
        </div>
      </div>
      <div class="collapse mt-2" id="attendanceForm">
        <div class="card export-form">
          <div class="card-body">
            <form action="{{ route('reports.attendance') }}" method="GET" target="_blank">
              <select name="employee_id" class="form-control form-control-sm">
                <option value="">-- جميع الموظفين --</option>
                @foreach($employees as $e)
                  <option value="{{ $e->id }}">{{ $e->employee_name_A }}</option>
                @endforeach
              </select>
              <input type="date" name="from_date" class="form-control form-control-sm" placeholder="من تاريخ">
              <input type="date" name="to_date"   class="form-control form-control-sm" placeholder="إلى تاريخ">
              <select name="status" class="form-control form-control-sm">
                <option value="">-- جميع الحالات --</option>
                <option value="1">حاضر</option><option value="2">غائب</option>
                <option value="3">إجازة</option><option value="4">إجازة رسمية</option>
              </select>
              <div class="btn-group w-100 mt-2">
                <button class="btn btn-sm btn-success" name="format" value="excel"><i class="fas fa-file-excel"></i> Excel</button>
                <button class="btn btn-sm btn-danger"  name="format" value="pdf"><i class="fas fa-print"></i> PDF</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    {{-- ══ الموظفون ══ --}}
    <div class="col-md-6 col-lg-3 mb-4">
      <div class="card report-card" data-toggle="collapse" data-target="#employeesForm">
        <div class="card-body">
          <i class="fas fa-users text-success"></i>
          <h5 class="mt-2 mb-0">الموظفون</h5>
          <small class="text-muted">Excel / طباعة PDF</small>
        </div>
      </div>
      <div class="collapse mt-2" id="employeesForm">
        <div class="card export-form">
          <div class="card-body">
            <form action="{{ route('reports.employees') }}" method="GET" target="_blank">
              <select name="department_id" class="form-control form-control-sm">
                <option value="">-- جميع الإدارات --</option>
                @foreach($departments as $d)
                  <option value="{{ $d->id }}">{{ $d->dep_name }}</option>
                @endforeach
              </select>
              <div class="btn-group w-100 mt-2">
                <button class="btn btn-sm btn-success" name="format" value="excel"><i class="fas fa-file-excel"></i> Excel</button>
                <button class="btn btn-sm btn-danger"  name="format" value="pdf"><i class="fas fa-print"></i> PDF</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    {{-- ══ السلف ══ --}}
    <div class="col-md-6 col-lg-3 mb-4">
      <div class="card report-card" data-toggle="collapse" data-target="#advancesForm">
        <div class="card-body">
          <i class="fas fa-hand-holding-usd text-warning"></i>
          <h5 class="mt-2 mb-0">السلف</h5>
          <small class="text-muted">Excel / طباعة PDF</small>
        </div>
      </div>
      <div class="collapse mt-2" id="advancesForm">
        <div class="card export-form">
          <div class="card-body">
            <form action="{{ route('reports.advances') }}" method="GET" target="_blank">
              <select name="employee_id" class="form-control form-control-sm">
                <option value="">-- جميع الموظفين --</option>
                @foreach($employees as $e)
                  <option value="{{ $e->id }}">{{ $e->employee_name_A }}</option>
                @endforeach
              </select>
              <input type="date" name="from_date" class="form-control form-control-sm">
              <input type="date" name="to_date"   class="form-control form-control-sm">
              <div class="btn-group w-100 mt-2">
                <button class="btn btn-sm btn-success" name="format" value="excel"><i class="fas fa-file-excel"></i> Excel</button>
                <button class="btn btn-sm btn-danger"  name="format" value="pdf"><i class="fas fa-print"></i> PDF</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    {{-- ══ الإجازات ══ --}}
    <div class="col-md-6 col-lg-3 mb-4">
      <div class="card report-card" data-toggle="collapse" data-target="#vacationsForm">
        <div class="card-body">
          <i class="fas fa-umbrella-beach text-info"></i>
          <h5 class="mt-2 mb-0">أرصدة الإجازات</h5>
          <small class="text-muted">Excel / طباعة PDF</small>
        </div>
      </div>
      <div class="collapse mt-2" id="vacationsForm">
        <div class="card export-form">
          <div class="card-body">
            <form action="{{ route('reports.vacations') }}" method="GET" target="_blank">
              <select name="employee_id" class="form-control form-control-sm">
                <option value="">-- جميع الموظفين --</option>
                @foreach($employees as $e)
                  <option value="{{ $e->id }}">{{ $e->employee_name_A }}</option>
                @endforeach
              </select>
              <div class="btn-group w-100 mt-2">
                <button class="btn btn-sm btn-success" name="format" value="excel"><i class="fas fa-file-excel"></i> Excel</button>
                <button class="btn btn-sm btn-danger"  name="format" value="pdf"><i class="fas fa-print"></i> PDF</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
@endsection
