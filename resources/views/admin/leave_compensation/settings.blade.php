@extends('admin.layouts.admin')
@section('title') بدل الإجازة @endsection
@section('start') شئون الموظفين @endsection
@section('home') <a href="#">بدل الإجازة</a> @endsection
@section('startpage') الإعدادات @endsection

@section('content')
<div class="col-12">
<div class="card">
  <div class="card-header" style="background:#28a745;color:#fff">
    <h3 class="card-title">
      <i class="fas fa-umbrella-beach ml-2"></i>
      إعدادات بدل الإجازة (يوم الراحة الأسبوعي المعمول فيه)
    </h3>
  </div>

  @if(session('success'))
    <div class="alert alert-success mx-3 mt-3 alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      {{ session('success') }}
    </div>
  @endif

  <form method="POST" action="{{ route('leave_compensation.update') }}">
    @csrf
    <div class="card-body">

      <div class="alert alert-info">
        <i class="fas fa-info-circle ml-1"></i>
        يُحتسب بدل الإجازة تلقائياً عندما يبصم موظف في يوم راحته الأسبوعية المحددة مسبقاً.
        يمكن إلغاء البدل يدوياً من سجل الحضور.
      </div>

      {{-- نوع الاحتساب --}}
      <h5 class="section-title"><i class="fas fa-cog ml-2"></i>طريقة الاحتساب</h5>
      <div class="row">
        <div class="col-md-4 form-group">
          <label>نوع الاحتساب <span class="text-danger">*</span></label>
          <select class="form-control" name="comp_type" id="compType" onchange="toggleCompType()">
            <option value="1" {{ $settings->comp_type == 1 ? 'selected' : '' }}>
              نوع 1 — مضاعف سعر اليوم
            </option>
            <option value="2" {{ $settings->comp_type == 2 ? 'selected' : '' }}>
              نوع 2 — مبلغ ثابت (وظيفة / فرع / إدارة)
            </option>
          </select>
        </div>

        {{-- النوع 1: مضاعف --}}
        <div class="col-md-3 form-group" id="multiplierWrap">
          <label>مضاعف سعر اليوم</label>
          <div class="input-group">
            <input type="number" class="form-control" name="day_multiplier"
              step="0.1" min="0"
              value="{{ old('day_multiplier', $settings->day_multiplier) }}">
            <div class="input-group-append"><span class="input-group-text">× اليوم</span></div>
          </div>
          <small class="text-muted">مثال: 1.5 = يوم ونصف | 2 = يومان</small>
        </div>

        {{-- النوع 2: المستوى --}}
        <div class="col-md-3 form-group" id="levelWrap" style="display:none">
          <label>تحديد المبلغ حسب</label>
          <select class="form-control" name="fixed_level" id="fixedLevel" onchange="showRatesTable()">
            <option value="job"        {{ $settings->fixed_level == 'job'        ? 'selected' : '' }}>الوظيفة</option>
            <option value="branch"     {{ $settings->fixed_level == 'branch'     ? 'selected' : '' }}>الفرع</option>
            <option value="department" {{ $settings->fixed_level == 'department' ? 'selected' : '' }}>الإدارة</option>
          </select>
        </div>
      </div>

      {{-- جداول المعدلات للنوع 2 --}}
      <div id="ratesSection" style="display:none">
        <hr>
        <h5 class="section-title"><i class="fas fa-table ml-2"></i>المبالغ الثابتة</h5>

        {{-- جدول الوظائف --}}
        <div id="jobsTable" class="rates-table">
          <table class="table table-bordered table-sm">
            <thead class="thead-light">
              <tr>
                <th>الوظيفة</th>
                <th style="width:200px">مبلغ بدل الإجازة (ج.م)</th>
              </tr>
            </thead>
            <tbody>
              @foreach($jobs as $job)
              <tr>
                <td>{{ $job->job_name }}</td>
                <td>
                  <div class="input-group input-group-sm">
                    <input type="number" class="form-control"
                      name="rates_job[{{ $job->id }}]"
                      step="0.01" min="0"
                      value="{{ old('rates_job.'.$job->id, $rates->get('job_'.$job->id)?->amount ?? '') }}"
                      placeholder="0">
                    <div class="input-group-append"><span class="input-group-text">ج.م</span></div>
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        {{-- جدول الفروع --}}
        <div id="branchesTable" class="rates-table" style="display:none">
          <table class="table table-bordered table-sm">
            <thead class="thead-light">
              <tr>
                <th>الفرع</th>
                <th style="width:200px">مبلغ بدل الإجازة (ج.م)</th>
              </tr>
            </thead>
            <tbody>
              @foreach($branches as $branch)
              <tr>
                <td>{{ $branch->branch_name }}</td>
                <td>
                  <div class="input-group input-group-sm">
                    <input type="number" class="form-control"
                      name="rates_branch[{{ $branch->id }}]"
                      step="0.01" min="0"
                      value="{{ old('rates_branch.'.$branch->id, $rates->get('branch_'.$branch->id)?->amount ?? '') }}"
                      placeholder="0">
                    <div class="input-group-append"><span class="input-group-text">ج.م</span></div>
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        {{-- جدول الإدارات --}}
        <div id="deptsTable" class="rates-table" style="display:none">
          <table class="table table-bordered table-sm">
            <thead class="thead-light">
              <tr>
                <th>الإدارة</th>
                <th style="width:200px">مبلغ بدل الإجازة (ج.م)</th>
              </tr>
            </thead>
            <tbody>
              @foreach($depts as $dept)
              <tr>
                <td>{{ $dept->dep_name }}</td>
                <td>
                  <div class="input-group input-group-sm">
                    <input type="number" class="form-control"
                      name="rates_dept[{{ $dept->id }}]"
                      step="0.01" min="0"
                      value="{{ old('rates_dept.'.$dept->id, $rates->get('department_'.$dept->id)?->amount ?? '') }}"
                      placeholder="0">
                    <div class="input-group-append"><span class="input-group-text">ج.م</span></div>
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>

    </div>{{-- card-body --}}
    <div class="card-footer">
      <button type="submit" class="btn btn-success">
        <i class="fas fa-save ml-1"></i> حفظ الإعدادات
      </button>
    </div>
  </form>
</div>
</div>
@endsection

@section('script')
<script>
function toggleCompType() {
  var type = parseInt(document.getElementById('compType').value);
  document.getElementById('multiplierWrap').style.display = (type === 1) ? '' : 'none';
  document.getElementById('levelWrap').style.display      = (type === 2) ? '' : 'none';
  document.getElementById('ratesSection').style.display   = (type === 2) ? '' : 'none';
  if (type === 2) showRatesTable();
}

function showRatesTable() {
  var level = document.getElementById('fixedLevel').value;
  document.querySelectorAll('.rates-table').forEach(t => t.style.display = 'none');
  var map = {job:'jobsTable', branch:'branchesTable', department:'deptsTable'};
  if (map[level]) document.getElementById(map[level]).style.display = '';
}

document.addEventListener('DOMContentLoaded', function() {
  toggleCompType();
});
</script>
@endsection
