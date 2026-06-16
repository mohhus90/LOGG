@extends('admin.layouts.admin')
@section('title') {{ __('admin.vac_edit_balance') }} @endsection
@section('start') {{ __('admin.hr_management') }} @endsection
@section('home') <a href="{{ route('vacations.index') }}">{{ __('admin.vac_title') }}</a> @endsection
@section('startpage') {{ __('admin.edit') }} @endsection

@section('content')
<div class="col-md-8 mx-auto">

  <div class="card mb-3" style="border-right:5px solid var(--accent,#c9a227)">
    <div class="card-body py-3">
      <div class="row align-items-center">
        <div class="col-md-8">
          <h5 class="mb-0 font-weight-bold">{{ $employee->employee_name_A }}</h5>
          <div class="mt-1">
            <span class="badge badge-secondary ml-1">{{ $employee->employee_id }}</span>
            @if($employee->national_id)
              <span class="badge badge-light ml-1">
                <i class="fas fa-id-card ml-1"></i>{{ $employee->national_id }}
              </span>
            @endif
            @if($employee->birth_date)
              @php $age = \Carbon\Carbon::parse($employee->birth_date)->age; @endphp
              <span class="badge badge-{{ $age >= 50 ? 'warning' : 'info' }} ml-1">
                {{ $age }} {{ __('admin.years') }}
                @if($age >= 50) — {{ __('admin.vac_law30_entitlement') }} @endif
              </span>
            @endif
          </div>
          @if($employee->emp_start_date)
            @php $exp = \Carbon\Carbon::parse($employee->emp_start_date)->diffInYears(now()); @endphp
            <small class="text-muted">
              <i class="fas fa-briefcase ml-1"></i>{{ __('admin.vac_experience') }}: {{ $exp }} {{ __('admin.years') }}
              @if($exp >= 10)
                <span class="badge badge-warning mr-1">{{ __('admin.vac_law30_title') }}</span>
              @endif
            </small>
          @endif
        </div>
        <div class="col-md-4 text-left">
          <div class="text-muted" style="font-size:.85em">{{ __('admin.year') }}: <strong>{{ $year }}</strong></div>
          @if($settings)
            <div class="text-muted" style="font-size:.8em">
              {{ __('admin.vac_company_setting') }}: {{ $settings->annual_vacation_days ?? 21 }} {{ __('admin.vac_law_annual') }} |
              {{ $settings->casual_vacation_days ?? 6 }} {{ __('admin.vac_law_casual') }}
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  <div class="card card-warning">
    <div class="card-header">
      <h3 class="card-title">
        <i class="fas fa-edit ml-2"></i>{{ __('admin.vac_edit_balance') }} — {{ $year }}
      </h3>
    </div>

    <form id="updateForm" action="{{ route('vacations.update', [$employee->id, $year]) }}" method="POST">
      @csrf

      <div class="card-body">
        @if($errors->any())
          <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
          </div>
        @endif

        <h6 class="section-title">🏖 {{ __('admin.vac_regular') }}</h6>
        <div class="alert alert-info py-2 mb-3" style="font-size:.85em">
          <i class="fas fa-balance-scale ml-1"></i>
          {{ __('admin.vac_egyptian_law') }}
          {{ __('admin.vac_total_used') }}
        </div>
        <div class="row">
          <div class="col-md-4 form-group">
            <label>{{ __('admin.vac_total_annual') }} <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="number" name="annual_balance" class="form-control"
                step="0.5" min="0" id="annualBalance"
                value="{{ old('annual_balance', $balance->annual_balance ?? 21) }}"
                onchange="recalc('annual')">
              <div class="input-group-append"><span class="input-group-text">{{ __('admin.day') }}</span></div>
            </div>
            <small class="text-muted">{{ __('admin.vac_law30_hint') }}</small>
          </div>
          <div class="col-md-4 form-group">
            <label>{{ __('admin.vac_used') }}</label>
            <div class="input-group">
              <input type="text" class="form-control bg-light" id="annualUsedDisplay"
                readonly value="{{ number_format($balance->annual_used ?? 0, 1) }}">
              <div class="input-group-append"><span class="input-group-text text-danger">{{ __('admin.vac_used') }}</span></div>
            </div>
            <small class="text-muted">{{ __('admin.vac_auto_calc') }}</small>
          </div>
          <div class="col-md-4 form-group">
            <label>{{ __('admin.vac_remaining') }} <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="number" name="annual_remaining" class="form-control"
                step="0.5" min="0" id="annualRemaining"
                value="{{ old('annual_remaining', $balance->annual_remaining ?? 21) }}"
                onchange="recalc('annual')">
              <div class="input-group-append"><span class="input-group-text text-success">{{ __('admin.vac_remaining') }}</span></div>
            </div>
          </div>
        </div>

        <h6 class="section-title mt-2">📅 {{ __('admin.vac_casual') }}</h6>
        <div class="row">
          <div class="col-md-4 form-group">
            <label>{{ __('admin.vac_total_annual') }} <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="number" name="casual_balance" class="form-control"
                step="0.5" min="0" id="casualBalance"
                value="{{ old('casual_balance', $balance->casual_balance ?? 6) }}"
                onchange="recalc('casual')">
              <div class="input-group-append"><span class="input-group-text">{{ __('admin.day') }}</span></div>
            </div>
          </div>
          <div class="col-md-4 form-group">
            <label>{{ __('admin.vac_used') }}</label>
            <div class="input-group">
              <input type="text" class="form-control bg-light" id="casualUsedDisplay"
                readonly value="{{ number_format($balance->casual_used ?? 0, 1) }}">
              <div class="input-group-append"><span class="input-group-text text-danger">{{ __('admin.vac_used') }}</span></div>
            </div>
          </div>
          <div class="col-md-4 form-group">
            <label>{{ __('admin.vac_remaining') }} <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="number" name="casual_remaining" class="form-control"
                step="0.5" min="0" id="casualRemaining"
                value="{{ old('casual_remaining', $balance->casual_remaining ?? 6) }}"
                onchange="recalc('casual')">
              <div class="input-group-append"><span class="input-group-text text-success">{{ __('admin.vac_remaining') }}</span></div>
            </div>
          </div>
        </div>

        <h6 class="section-title mt-2">📆 {{ __('admin.vac_monthly_accrual') }}</h6>
        <div class="row">
          <div class="col-md-4 form-group">
            <label>{{ __('admin.vac_monthly_accrual') }}</label>
            <div class="input-group">
              <input type="number" name="monthly_accrual" class="form-control"
                step="0.01" min="0"
                value="{{ old('monthly_accrual', $balance->monthly_accrual ?? 1.75) }}">
              <div class="input-group-append"><span class="input-group-text">{{ __('admin.day') }}/{{ __('admin.vac_monthly') }}</span></div>
            </div>
            <small class="text-muted">
              21÷12 = 1.75 | 30÷12 = 2.5 |
              <strong>0 = {{ __('admin.vac_stop_accrual') }}</strong>
            </small>
          </div>

          <div class="col-md-8 form-group">
            <label class="d-block">{{ __('admin.vac_quick_apply') }}</label>
            <button type="button" class="btn btn-sm btn-outline-primary ml-1"
              onclick="applyLaw(15, 1.75)">
              <i class="fas fa-gavel ml-1"></i>{{ __('admin.vac_law_regular') }}
            </button>
            <button type="button" class="btn btn-sm btn-outline-warning ml-1"
              onclick="applyLaw(24, 2.5)">
              <i class="fas fa-gavel ml-1"></i>{{ __('admin.vac_law_senior') }}
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger ml-1"
              onclick="applyLaw(0, 0)">
              <i class="fas fa-ban ml-1"></i>{{ __('admin.vac_stop_accrual') }}
            </button>
          </div>
        </div>

      </div>
    </form>

    <div class="card-footer d-flex justify-content-between">
      <div>
        <button type="submit" form="updateForm" class="btn btn-warning">
          <i class="fas fa-save ml-1"></i>{{ __('admin.att_save_changes') }}
        </button>
        <a href="{{ route('vacations.index', ['year'=>$year]) }}"
           class="btn btn-secondary mr-2">{{ __('admin.back') }}</a>
      </div>
      @if($balance->exists)
      <form method="POST"
        action="{{ route('vacations.delete_balance', [$employee->id, $year]) }}"
        id="deleteBalForm">
        @csrf @method('DELETE')
        <button type="button" class="btn btn-outline-danger"
          onclick="if(confirm('{{ __('admin.vac_delete_confirm') }} {{ $year }}?'))document.getElementById('deleteBalForm').submit()">
          <i class="fas fa-trash ml-1"></i>{{ __('admin.delete') }}
        </button>
      </form>
      @endif
    </div>
  </div>
</div>
@endsection

@section('script')
<script>
function recalc(type) {
  var bal  = parseFloat(document.getElementById(type + 'Balance').value)   || 0;
  var rem  = parseFloat(document.getElementById(type + 'Remaining').value) || 0;
  var used = Math.max(0, bal - rem);
  document.getElementById(type + 'UsedDisplay').value = used.toFixed(1);
}

function applyLaw(days, monthly) {
  var annualBal = document.getElementById('annualBalance');
  var annualRem = document.getElementById('annualRemaining');
  var monthlyInput = document.querySelector('input[name="monthly_accrual"]');

  if (annualBal) annualBal.value = days;
  if (annualRem) annualRem.value = days;
  if (monthlyInput) monthlyInput.value = monthly;
  recalc('annual');
}

recalc('annual');
recalc('casual');
</script>
@endsection
