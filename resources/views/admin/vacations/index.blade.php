{{-- FILE: resources/views/admin/vacations/index.blade.php --}}
@extends('admin.layouts.admin')
@section('title') أرصدة الإجازات @endsection
@section('start') شئون الموظفين @endsection
@section('home') <a href="{{ route('vacations.index') }}">الإجازات</a> @endsection
@section('startpage') الأرصدة السنوية @endsection

@section('content')
<div class="col-12">
<div class="card">
  <div class="card-header">
    <h3 class="card-title">
      <i class="fas fa-umbrella-beach ml-2 text-success"></i>
      أرصدة الإجازات السنوية
    </h3>
    <div class="card-tools d-flex align-items-center">
      {{-- اختيار السنة --}}
      <form method="GET" class="form-inline ml-3">
        <label class="ml-1">السنة:</label>
        <input type="number" name="year" class="form-control form-control-sm ml-1"
          style="width:85px" value="{{ $year }}">
        <button type="submit" class="btn btn-sm btn-primary ml-1">
          <i class="fas fa-search"></i>
        </button>
      </form>

      {{-- إنشاء رصيد دفعي --}}
      <form method="POST" action="{{ route('vacations.create_bulk') }}" class="form-inline">
        @csrf
        <input type="hidden" name="year" value="{{ $year }}">
        <button type="submit" class="btn btn-sm btn-success ml-1"
          onclick="return confirm('إنشاء رصيد {{ $year }} لجميع الموظفين؟')">
          <i class="fas fa-magic ml-1"></i>إنشاء أرصدة {{ $year }}
        </button>
      </form>

      {{-- استحقاق شهري --}}
      <form method="POST" action="{{ route('vacations.monthly_accrual') }}" class="form-inline">
        @csrf
        <input type="hidden" name="year" value="{{ $year }}">
        <button type="submit" class="btn btn-sm btn-info"
          onclick="return confirm('إضافة الاستحقاق الشهري لجميع الموظفين؟')">
          <i class="fas fa-plus-circle ml-1"></i>استحقاق شهري
        </button>
      </form>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success mx-3 mt-2 alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      {{ session('success') }}
    </div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger mx-3 mt-2">{{ session('error') }}</div>
  @endif

  <div class="card-body p-0">
  <div class="table-responsive">
  <table class="table table-bordered table-hover mb-0">
    <thead class="thead-dark">
      <tr>
        <th>#</th>
        <th>الموظف</th>
        <th colspan="3" class="text-center" style="background:#1e7e34">
          🏖 إجازة اعتيادية (قانون: 21 يوم)
        </th>
        <th colspan="3" class="text-center" style="background:#856404">
          📅 إجازة عارضة (قانون: 6 أيام)
        </th>
        <th>إجراء</th>
      </tr>
      <tr class="table-dark">
        <th colspan="2"></th>
        <th>الرصيد الكلي</th>
        <th>المستخدم</th>
        <th>المتبقي</th>
        <th>الرصيد الكلي</th>
        <th>المستخدم</th>
        <th>المتبقي</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      @foreach($employees as $emp)
      @php $bal = $balances->get($emp->id); @endphp
      <tr class="{{ !$bal ? 'table-warning' : '' }}">
        <td>{{ $loop->iteration }}</td>
        <td>
          <strong>{{ $emp->employee_name_A }}</strong>
          <br><small class="text-muted">{{ $emp->employee_id }}</small>
        </td>
        @if($bal)
          <td class="text-center">{{ $bal->annual_balance }}</td>
          <td class="text-center text-danger">{{ $bal->annual_used }}</td>
          <td class="text-center">
            <span class="{{ $bal->annual_remaining <= 0 ? 'text-danger font-weight-bold' : 'text-success font-weight-bold' }}">
              {{ $bal->annual_remaining }}
            </span>
          </td>
          <td class="text-center">{{ $bal->casual_balance }}</td>
          <td class="text-center text-danger">{{ $bal->casual_used }}</td>
          <td class="text-center">
            <span class="{{ $bal->casual_remaining <= 0 ? 'text-danger font-weight-bold' : 'text-success font-weight-bold' }}">
              {{ $bal->casual_remaining }}
            </span>
          </td>
        @else
          <td colspan="6" class="text-center text-muted">
            <i class="fas fa-exclamation-triangle text-warning ml-1"></i>
            لا يوجد رصيد لسنة {{ $year }}
          </td>
        @endif
        <td>
          <a href="{{ route('vacations.edit', [$emp->id, $year]) }}"
             class="btn btn-xs btn-warning">
            <i class="fas fa-edit"></i>
          </a>
        </td>
      </tr>
      @endforeach
    </tbody>
    <tfoot class="table-light font-weight-bold">
      <tr>
        <td colspan="2" class="text-left">الإجمالي</td>
        <td class="text-center">{{ $balances->sum('annual_balance') }}</td>
        <td class="text-center text-danger">{{ $balances->sum('annual_used') }}</td>
        <td class="text-center text-success">{{ $balances->sum('annual_remaining') }}</td>
        <td class="text-center">{{ $balances->sum('casual_balance') }}</td>
        <td class="text-center text-danger">{{ $balances->sum('casual_used') }}</td>
        <td class="text-center text-success">{{ $balances->sum('casual_remaining') }}</td>
        <td></td>
      </tr>
    </tfoot>
  </table>
  </div>
  </div>
</div>
</div>
@endsection
