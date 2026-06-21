@extends('admin.layouts.admin')
@section('title') {{ __('admin.vac_title') }} @endsection
@section('start') {{ __('admin.hr_management') }} @endsection
@section('home') <a href="{{ route('vacations.index') }}">{{ __('admin.vac_title') }}</a> @endsection
@section('startpage') {{ __('admin.vac_balances') }} @endsection

@section('css')
<style>
.bal-chip{display:inline-block;padding:2px 10px;border-radius:20px;font-weight:700;font-size:.82em;}
.bal-good{background:#d1fae5;color:#065f46;}
.bal-warn{background:#fef3c7;color:#92400e;}
.bal-zero{background:#fee2e2;color:#991b1b;}
.vac-stat{text-align:center;padding:12px;border-radius:8px;background:#fff;box-shadow:0 2px 8px rgba(0,0,0,.07);}
.vac-stat .num{font-size:1.7em;font-weight:800;color:#1e3a5f;}
.age-warn{font-size:.72em;padding:2px 6px;border-radius:8px;background:#fff3cd;color:#856404;}
</style>
@endsection

@section('content')
<div class="col-12">

@if($stats)
<div class="row mb-3">
  <div class="col-md-3 col-6 mb-2">
    <div class="vac-stat">
      <div class="num text-success">{{ $stats->total_employees ?? 0 }}</div>
      <div class="text-muted small">{{ __('admin.vac_employees_balance') }} {{ $year }}</div>
    </div>
  </div>
  <div class="col-md-3 col-6 mb-2">
    <div class="vac-stat">
      <div class="num">{{ number_format($stats->total_annual_remaining ?? 0, 1) }}</div>
      <div class="text-muted small">{{ __('admin.vac_regular_remain') }}</div>
    </div>
  </div>
  <div class="col-md-3 col-6 mb-2">
    <div class="vac-stat">
      <div class="num" style="color:#856404">{{ number_format($stats->total_annual_used ?? 0, 1) }}</div>
      <div class="text-muted small">{{ __('admin.vac_regular_used') }}</div>
    </div>
  </div>
  <div class="col-md-3 col-6 mb-2">
    <div class="vac-stat">
      <div class="num" style="color:#0d6efd">{{ number_format($stats->total_casual_remaining ?? 0, 1) }}</div>
      <div class="text-muted small">{{ __('admin.vac_casual_remain') }}</div>
    </div>
  </div>
</div>
@endif

<div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap:.5rem">
  <h5 class="mb-0 font-weight-bold" style="color:#1e3a5f">
    <i class="fas fa-umbrella-beach ml-2"></i>{{ __('admin.vac_title') }} — {{ $year }}
    @if($settings)
      <small class="text-muted" style="font-size:.72em">
        ({{ __('admin.vac_regular') }}: {{ $settings->annual_vacation_days ?? 21 }} {{ __('admin.day') }} |
        {{ __('admin.vac_casual') }}: {{ $settings->casual_vacation_days ?? 6 }} {{ __('admin.days') }} |
        {{ __('admin.vac_monthly') }}: {{ $settings->monthly_vacation_balance ?? 1.75 }} {{ __('admin.day') }})
      </small>
    @endif
  </h5>
  <div class="d-flex flex-wrap" style="gap:.4rem">
    <form method="GET" class="form-inline">
      <input type="number" name="year" class="form-control form-control-sm" style="width:82px" value="{{ $year }}">
      <button type="submit" class="btn btn-sm btn-primary mr-1"><i class="fas fa-search"></i></button>
    </form>
    <form method="POST" action="{{ route('vacations.create_bulk') }}" class="d-inline">
      @csrf
      <input type="hidden" name="year" value="{{ $year }}">
      <button type="submit" class="btn btn-sm btn-success"
        onclick="return confirm('{{ __('admin.vac_create_confirm') }} {{ $year }}?')">
        <i class="fas fa-magic ml-1"></i>{{ __('admin.vac_create_balances') }} {{ $year }}
      </button>
    </form>
    <form method="POST" action="{{ route('vacations.monthly_accrual') }}" class="d-inline">
      @csrf
      <input type="hidden" name="year" value="{{ $year }}">
      <button type="submit" class="btn btn-sm btn-info"
        onclick="return confirm('{{ __('admin.vac_accrual_confirm') }}')">
        <i class="fas fa-plus-circle ml-1"></i>{{ __('admin.vac_monthly_accrual') }}
      </button>
    </form>
  </div>
</div>

@if(session('success'))
  <div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    {{ session('success') }}
  </div>
@endif
@if(session('error'))
  <div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    {{ session('error') }}
  </div>
@endif

<div class="card mb-2" style="border-right:4px solid #2d5a9e">
  <div class="card-body py-2">
    <form method="GET" action="{{ route('vacations.index') }}">
      <input type="hidden" name="year" value="{{ $year }}">
      <div class="row align-items-end">
        <div class="col-md-3 col-6 mb-1">
          <input type="text" name="search_name" class="form-control form-control-sm"
            placeholder="{{ __('admin.emp_name_ar') }}" value="{{ request('search_name') }}">
        </div>
        <div class="col-md-2 col-6 mb-1">
          <input type="text" name="search_code" class="form-control form-control-sm"
            placeholder="{{ __('admin.emp_code') }}" value="{{ request('search_code') }}">
        </div>
        <div class="col-md-2 col-6 mb-1">
          <input type="text" name="search_national" class="form-control form-control-sm"
            placeholder="{{ __('admin.emp_national_id') }}" value="{{ request('search_national') }}">
        </div>
        <div class="col-md-2 col-6 mb-1">
          <select name="has_balance" class="form-control form-control-sm">
            <option value="">{{ __('admin.vac_all_employees') }}</option>
            <option value="1" {{ request('has_balance')=='1'?'selected':'' }}>{{ __('admin.vac_has_balance') }}</option>
            <option value="0" {{ request('has_balance')=='0'?'selected':'' }}>{{ __('admin.vac_no_balance') }}</option>
          </select>
        </div>
        <div class="col-md-3 mb-1 d-flex" style="gap:.3rem">
          <button type="submit" class="btn btn-primary btn-sm flex-fill">
            <i class="fas fa-search ml-1"></i>{{ __('admin.search') }}
          </button>
          <a href="{{ route('vacations.index',['year'=>$year]) }}"
             class="btn btn-outline-secondary btn-sm flex-fill">{{ __('admin.clear') }}</a>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body p-0">
  <div class="table-responsive">
  <table class="table table-bordered table-hover mb-0" style="font-size:.86em">
    <thead>
      <tr>
        <th>#</th>
        <th>{{ __('admin.emp_code') }}</th>
        <th>{{ __('admin.emp_name_ar') }}</th>
        <th>{{ __('admin.emp_national_id') }}</th>
        <th>{{ __('admin.vac_age') }}</th>
        <th colspan="3" class="text-center" style="background:#e8f5e9">🏖 {{ __('admin.vac_regular') }}</th>
        <th colspan="3" class="text-center" style="background:#fff8e1">📅 {{ __('admin.vac_casual') }}</th>
        <th>{{ __('admin.vac_monthly') }}</th>
        <th>{{ __('admin.action') }}</th>
      </tr>
      <tr style="background:#f8f9fa;font-size:.78em">
        <th colspan="5"></th>
        <th class="text-center">{{ __('admin.vac_total') }}</th>
        <th class="text-center text-danger">{{ __('admin.vac_used') }}</th>
        <th class="text-center text-success">{{ __('admin.vac_remaining') }}</th>
        <th class="text-center">{{ __('admin.vac_total') }}</th>
        <th class="text-center text-danger">{{ __('admin.vac_used') }}</th>
        <th class="text-center text-success">{{ __('admin.vac_remaining') }}</th>
        <th></th><th></th>
      </tr>
    </thead>
    <tbody>
      @forelse($employees as $emp)
      @php
        $bal  = $emp->vacationBalance->first();
        $age  = $emp->birth_date ? \Carbon\Carbon::parse($emp->birth_date)->age : null;
        $law30 = $age && $age >= 50;
        if (!$law30 && $emp->emp_start_date)
          $law30 = \Carbon\Carbon::parse($emp->emp_start_date)->diffInYears(now()) >= 10;
      @endphp
      <tr>
        <td>{{ $employees->firstItem() + $loop->index }}</td>
        <td><code style="font-size:.82em">{{ $emp->employee_id }}</code></td>
        <td>
          <strong>{{ $emp->employee_name_A }}</strong>
          @if($law30)
            <span class="age-warn mr-1" title="{{ __('admin.vac_law30_title') }}">30 {{ __('admin.day') }}</span>
          @endif
        </td>
        <td><small>{{ $emp->national_id ?? '—' }}</small></td>
        <td class="text-center">
          @if($age)
            <span class="{{ $law30?'font-weight-bold text-warning':'' }}">{{ $age }}</span>
          @else —
          @endif
        </td>
        @if($bal)
          <td class="text-center">{{ $bal->annual_balance }}</td>
          <td class="text-center text-danger">{{ $bal->annual_used }}</td>
          <td class="text-center">
            <span class="bal-chip {{ $bal->annual_remaining > 5 ? 'bal-good' : ($bal->annual_remaining > 0 ? 'bal-warn' : 'bal-zero') }}">
              {{ $bal->annual_remaining }}
            </span>
          </td>
          <td class="text-center">{{ $bal->casual_balance }}</td>
          <td class="text-center text-danger">{{ $bal->casual_used }}</td>
          <td class="text-center">
            <span class="bal-chip {{ $bal->casual_remaining > 0 ? 'bal-good' : 'bal-zero' }}">
              {{ $bal->casual_remaining }}
            </span>
          </td>
          <td class="text-center"><small>{{ $bal->monthly_accrual }}</small></td>
        @else
          <td colspan="7" class="text-center text-muted">
            <i class="fas fa-exclamation-triangle text-warning ml-1"></i>{{ __('admin.vac_no_balance') }}
          </td>
        @endif
        <td>
          <a href="{{ route('vacations.edit', [$emp->id, $year]) }}"
             class="btn btn-xs btn-warning" title="{{ __('admin.edit') }}">
            <i class="fas fa-edit"></i>
          </a>
          @if($bal)
          <button class="btn btn-xs btn-danger" title="{{ __('admin.delete') }}"
            onclick="if(confirm('{{ __('admin.vac_delete_confirm') }}'))document.getElementById('del_{{$emp->id}}_{{$year}}').submit()">
            <i class="fas fa-trash"></i>
          </button>
          <form id="del_{{$emp->id}}_{{$year}}"
            action="{{ route('vacations.delete_balance', [$emp->id, $year]) }}"
            method="POST" style="display:none">
            @csrf @method('DELETE')
          </form>
          @endif
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="14" class="text-center py-4 text-muted">
          <i class="fas fa-search fa-2x mb-2 d-block"></i>
          {{ __('admin.no_data') }}
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
  </div>
  </div>
  <div class="card-footer">
    {{ $employees->appends(request()->except('page'))->links() }}
  </div>
</div>

</div>
@endsection
