@extends('admin.layouts.admin')
@section('title') تنبيهات العقود وفترة الاختبار @endsection
@section('start') التقارير @endsection
@section('home') <a href="{{ route('reports.contracts_expiring') }}">تنبيهات العقود/الاختبار</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">

<div class="card mb-3">
  <div class="card-body">
    <form method="GET" action="{{ route('reports.contracts_expiring') }}" class="form-inline">
      <label class="ml-2">عرض المستحق خلال</label>
      <input type="number" name="days" min="1" value="{{ $days }}" class="form-control form-control-sm mx-2" style="width:90px">
      <label class="ml-2">يوم</label>
      <button type="submit" class="btn btn-primary btn-sm mr-2"><i class="fas fa-filter ml-1"></i> تصفية</button>
    </form>
  </div>
</div>

<div class="card mb-3">
  <div class="card-header"><h3 class="card-title"><i class="fas fa-user-clock ml-2 text-warning"></i>موظفون تنتهي فترة اختبارهم خلال {{ $days }} يوم</h3></div>
  <div class="card-body p-0">
    <table class="table table-bordered table-striped mb-0">
      <thead class="thead-dark"><tr><th>#</th><th>الموظف</th><th>كود الموظف</th><th>نهاية فترة الاختبار</th><th>متبقي (يوم)</th></tr></thead>
      <tbody>
        @forelse($probationEmployees as $e)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td><a href="{{ route('employees.show', $e->id) }}">{{ $e->employee_name_A }}</a></td>
          <td>{{ $e->employee_id }}</td>
          <td>{{ $e->probation_end_date }}</td>
          <td>{{ now()->diffInDays($e->probation_end_date, false) }}</td>
        </tr>
        @empty
        <tr><td colspan="5" class="text-center text-muted py-4">لا يوجد موظفون فى فترة اختبار قريبة الانتهاء</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="card">
  <div class="card-header"><h3 class="card-title"><i class="fas fa-file-signature ml-2 text-danger"></i>موظفون تنتهي عقودهم خلال {{ $days }} يوم</h3></div>
  <div class="card-body p-0">
    <table class="table table-bordered table-striped mb-0">
      <thead class="thead-dark"><tr><th>#</th><th>الموظف</th><th>كود الموظف</th><th>نهاية العقد</th><th>متبقي (يوم)</th></tr></thead>
      <tbody>
        @forelse($contractEmployees as $e)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td><a href="{{ route('employees.show', $e->id) }}">{{ $e->employee_name_A }}</a></td>
          <td>{{ $e->employee_id }}</td>
          <td>{{ $e->contract_end_date }}</td>
          <td>{{ now()->diffInDays($e->contract_end_date, false) }}</td>
        </tr>
        @empty
        <tr><td colspan="5" class="text-center text-muted py-4">لا توجد عقود محدد المدة قريبة الانتهاء</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

</div>
@endsection
