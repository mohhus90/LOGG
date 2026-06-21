@extends('admin.layouts.admin')
@section('title') {{ __('admin.emp_title') }} @endsection
@section('start') {{ __('admin.hr_management') }} @endsection
@section('home') <a href="{{ route('employees.index') }}">{{ __('admin.emp_title') }}</a> @endsection
@section('startpage') {{ __('admin.view') }} @endsection
@section('css')
<style>
.search-panel{background:#fff;border-radius:10px;padding:16px;box-shadow:0 2px 12px rgba(0,0,0,.07);margin-bottom:1rem;border-right:4px solid #2d5a9e;}
.stat-mini{text-align:center;padding:12px;border-radius:8px;background:#fff;box-shadow:0 2px 8px rgba(0,0,0,.06);}
.stat-mini .num{font-size:1.6em;font-weight:800;color:#1e3a5f;}
.sort-link{color:inherit;text-decoration:none;}
.sort-link:hover{color:#c9a227;}
</style>
@endsection
@section('content')
<div class="col-12">

<div class="row mb-3">
  <div class="col-md-3 col-6 mb-2"><div class="stat-mini"><div class="num">{{ $totalAll }}</div><div class="text-muted small">{{ __('admin.emp_total') }}</div></div></div>
  <div class="col-md-3 col-6 mb-2"><div class="stat-mini"><div class="num" style="color:#198754">{{ $totalActive }}</div><div class="text-muted small">{{ __('admin.emp_active') }}</div></div></div>
  <div class="col-md-3 col-6 mb-2"><div class="stat-mini"><div class="num" style="color:#dc3545">{{ $totalAll - $totalActive }}</div><div class="text-muted small">{{ __('admin.emp_inactive') }}</div></div></div>
  <div class="col-md-3 col-6 mb-2"><div class="stat-mini"><div class="num" style="color:#2d5a9e">{{ number_format($totalSalary,0) }}</div><div class="text-muted small">{{ __('admin.emp_total_salary') }}</div></div></div>
</div>

<div class="search-panel">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="mb-0 font-weight-bold" style="color:#1e3a5f">
      <i class="fas fa-search ml-1"></i>{{ __('admin.emp_advanced_search') }}
    </h6>
    <div>
      <button type="button" class="btn btn-sm btn-outline-secondary" id="toggleAdv">
        <i class="fas fa-sliders-h ml-1"></i>{{ __('admin.emp_extra_filters') }}
      </button>
      <a href="{{ route('employees.index') }}" class="btn btn-sm btn-outline-danger mr-1">
        <i class="fas fa-times"></i>
      </a>
    </div>
  </div>
  <form method="GET" action="{{ route('employees.index') }}">
    <div class="row">
      <div class="col-md-2 col-6 mb-2"><input type="text" name="search_name" class="form-control form-control-sm" placeholder="{{ __('admin.name') }}" value="{{ request('search_name') }}"></div>
      <div class="col-md-2 col-6 mb-2"><input type="text" name="search_code" class="form-control form-control-sm" placeholder="{{ __('admin.code') }}" value="{{ request('search_code') }}"></div>
      <div class="col-md-2 col-6 mb-2"><input type="text" name="search_national" class="form-control form-control-sm" placeholder="{{ __('admin.emp_national_id') }}" value="{{ request('search_national') }}"></div>
      <div class="col-md-2 col-6 mb-2"><input type="text" name="search_phone" class="form-control form-control-sm" placeholder="{{ __('admin.phone') }}" value="{{ request('search_phone') }}"></div>
      <div class="col-md-2 col-6 mb-2">
        <select name="search_func_status" class="form-control form-control-sm">
          <option value="">{{ __('admin.emp_all_status') }}</option>
          <option value="1" {{ request('search_func_status')=='1'?'selected':'' }}>{{ __('admin.emp_working') }}</option>
          <option value="2" {{ request('search_func_status')=='2'?'selected':'' }}>{{ __('admin.emp_not_working') }}</option>
        </select>
      </div>
      <div class="col-md-2 mb-2"><button type="submit" class="btn btn-primary btn-sm btn-block"><i class="fas fa-search ml-1"></i>{{ __('admin.search') }}</button></div>
    </div>

    <div id="advFilters" style="display:none">
      <hr class="mt-1 mb-2">
      <div class="row">
        {{-- فلتر العميل --}}
        <div class="col-md-2 col-6 mb-2">
          <label class="small" style="color:#b8860b;font-weight:600;">العميل (Outsource)</label>
          <select name="client_id" class="form-control form-control-sm" style="border-color:#b8860b">
            <option value="">الكل</option>
            <option value="0" {{ request('client_id')==='0'?'selected':'' }}>— موظفو الشركة فقط —</option>
            @foreach($clients as $cl)
              <option value="{{ $cl->id }}" {{ request('client_id')==$cl->id?'selected':'' }}>{{ $cl->client_name }}</option>
            @endforeach
          </select>
        </div>
        {{-- فلتر HRID --}}
        <div class="col-md-2 col-6 mb-2">
          <label class="small">HRID (كود العميل)</label>
          <input type="text" name="search_hrid" class="form-control form-control-sm"
                 placeholder="KR-15" value="{{ request('search_hrid') }}">
        </div>
        <div class="col-md-2 col-6 mb-2">
          <label class="small">{{ __('admin.emp_branch') }}</label>
          <select name="search_branch" class="form-control form-control-sm">
            <option value="">{{ __('admin.all') }}</option>
            @foreach($branches as $br)<option value="{{ $br->id }}" {{ request('search_branch')==$br->id?'selected':'' }}>{{ $br->branch_name }}</option>@endforeach
          </select>
        </div>
        <div class="col-md-2 col-6 mb-2">
          <label class="small">{{ __('admin.emp_dept') }}</label>
          <select name="search_dept" class="form-control form-control-sm">
            <option value="">{{ __('admin.all') }}</option>
            @foreach($departments as $d)<option value="{{ $d->id }}" {{ request('search_dept')==$d->id?'selected':'' }}>{{ $d->dep_name }}</option>@endforeach
          </select>
        </div>
        <div class="col-md-2 col-6 mb-2">
          <label class="small">{{ __('admin.emp_job') }}</label>
          <select name="search_job" class="form-control form-control-sm">
            <option value="">{{ __('admin.all') }}</option>
            @foreach($jobs_categories as $j)<option value="{{ $j->id }}" {{ request('search_job')==$j->id?'selected':'' }}>{{ $j->job_name }}</option>@endforeach
          </select>
        </div>
        <div class="col-md-2 col-6 mb-2">
          <label class="small">{{ __('admin.emp_shift') }}</label>
          <select name="search_shift" class="form-control form-control-sm">
            <option value="">{{ __('admin.all') }}</option>
            @foreach($shifts as $sh)<option value="{{ $sh->id }}" {{ request('search_shift')==$sh->id?'selected':'' }}>{{ $sh->type }}</option>@endforeach
          </select>
        </div>
        <div class="col-md-2 col-6 mb-2">
          <label class="small">{{ __('admin.emp_gender') }}</label>
          <select name="search_gender" class="form-control form-control-sm">
            <option value="">{{ __('admin.all') }}</option>
            <option value="1" {{ request('search_gender')=='1'?'selected':'' }}>{{ __('admin.male') }}</option>
            <option value="2" {{ request('search_gender')=='2'?'selected':'' }}>{{ __('admin.female') }}</option>
          </select>
        </div>
        <div class="col-md-2 col-6 mb-2">
          <label class="small">{{ __('admin.emp_insurance_status') }}</label>
          <select name="search_insurance" class="form-control form-control-sm">
            <option value="">{{ __('admin.all') }}</option>
            <option value="1" {{ request('search_insurance')=='1'?'selected':'' }}>{{ __('admin.emp_insured') }}</option>
            <option value="2" {{ request('search_insurance')=='2'?'selected':'' }}>{{ __('admin.emp_not_insured') }}</option>
            <option value="3" {{ request('search_insurance')=='3'?'selected':'' }}>{{ __('admin.emp_training') }}</option>
            <option value="4" {{ request('search_insurance')=='4'?'selected':'' }}>{{ __('admin.emp_service_ended') }}</option>
          </select>
        </div>
      </div>
      <div class="row">
        <div class="col-md-2 col-6 mb-2">
          <label class="small">{{ __('admin.fingerprint_devices') }}</label>
          <select name="search_has_finger" class="form-control form-control-sm">
            <option value="">{{ __('admin.all') }}</option>
            <option value="1" {{ request('search_has_finger')=='1'?'selected':'' }}>{{ __('admin.emp_has_finger') }}</option>
            <option value="2" {{ request('search_has_finger')=='2'?'selected':'' }}>{{ __('admin.emp_no_finger') }}</option>
          </select>
        </div>
        <div class="col-md-2 col-6 mb-2">
          <label class="small">Finger ID</label>
          <input type="number" name="search_finger" class="form-control form-control-sm" placeholder="{{ __('admin.emp_finger_id') }}" value="{{ request('search_finger') }}">
        </div>
        <div class="col-md-3 mb-2">
          <label class="small">{{ __('admin.emp_salary_from') }} — {{ __('admin.emp_salary_to') }}</label>
          <div class="input-group input-group-sm">
            <input type="number" name="sal_from" class="form-control" placeholder="{{ __('admin.from') }}" value="{{ request('sal_from') }}" step="100">
            <div class="input-group-prepend input-group-append"><span class="input-group-text px-1">—</span></div>
            <input type="number" name="sal_to" class="form-control" placeholder="{{ __('admin.to') }}" value="{{ request('sal_to') }}" step="100">
          </div>
        </div>
        <div class="col-md-3 mb-2">
          <label class="small">{{ __('admin.emp_hire_date') }}</label>
          <div class="input-group input-group-sm">
            <input type="date" name="hire_from" class="form-control" value="{{ request('hire_from') }}">
            <div class="input-group-prepend input-group-append"><span class="input-group-text px-1">—</span></div>
            <input type="date" name="hire_to" class="form-control" value="{{ request('hire_to') }}">
          </div>
        </div>
        <div class="col-md-2 mb-2">
          <label class="small">{{ __('admin.per_page') }}</label>
          <select name="per_page" class="form-control form-control-sm">
            @foreach([10,20,50,100] as $pp)<option value="{{ $pp }}" {{ request('per_page',20)==$pp?'selected':'' }}>{{ $pp }}</option>@endforeach
          </select>
        </div>
      </div>
    </div>
    <input type="hidden" name="sort_by" value="{{ request('sort_by','employee_name_A') }}">
    <input type="hidden" name="sort_dir" value="{{ request('sort_dir','asc') }}">
  </form>
</div>

<div class="card">
  <div class="card-header">
    <h3 class="card-title card_title_center">
      <i class="fas fa-users ml-2"></i>{{ __('admin.emp_list') }}
      <span class="badge badge-light mr-1">{{ $data->total() }}</span>
      <a href="{{ route('employees.create') }}" class="btn btn-sm btn-success mr-2"><i class="fas fa-plus"></i> {{ __('admin.add') }}</a>
      <a href="{{ route('employees.uploadexcel') }}" class="btn btn-sm btn-info mr-1"><i class="fas fa-file-excel"></i> Excel</a>
    </h3>
  </div>

  @if(session('success'))
    <div class="alert alert-success mx-3 mt-2 alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      {{ session('success') }}
    </div>
  @endif

  <div class="card-body p-0">
  <div class="table-responsive">
  <table class="table table-bordered table-hover mb-0" style="font-size:.87em">
    <thead>
      <tr>
        <th>#</th>
        <th><a href="{{ request()->fullUrlWithQuery(['sort_by'=>'employee_id','sort_dir'=>request('sort_dir','asc')==='asc'?'desc':'asc']) }}" class="sort-link">{{ __('admin.code') }}</a></th>
        <th><a href="{{ request()->fullUrlWithQuery(['sort_by'=>'employee_name_A','sort_dir'=>request('sort_dir','asc')==='asc'?'desc':'asc']) }}" class="sort-link">{{ __('admin.name') }}</a></th>
        <th>{{ __('admin.emp_national_id') }}</th>
        <th>Finger ID</th>
        <th>{{ __('admin.emp_dept_job') }}</th>
        <th>{{ __('admin.emp_branch') }}</th>
        <th><a href="{{ request()->fullUrlWithQuery(['sort_by'=>'emp_sal','sort_dir'=>request('sort_dir','asc')==='asc'?'desc':'asc']) }}" class="sort-link">{{ __('admin.emp_sal_label') }}</a></th>
        <th><a href="{{ request()->fullUrlWithQuery(['sort_by'=>'emp_start_date','sort_dir'=>request('sort_dir','asc')==='asc'?'desc':'asc']) }}" class="sort-link">{{ __('admin.emp_hire_date') }}</a></th>
        <th>{{ __('admin.status') }}</th>
        <th>{{ __('admin.action') }}</th>
      </tr>
    </thead>
    <tbody>
      @forelse($data as $emp)
      <tr>
        <td>{{ $data->firstItem() + $loop->index }}</td>
        <td><code style="font-size:.85em">{{ $emp->employee_id }}</code></td>
        <td>
          <div class="d-flex align-items-center gap-2">
            <div class="emp-avatar-sm me-2">
              @if($emp->emp_photo && file_exists(public_path('assets/admin/uploads/' . $emp->emp_photo)))
                <img src="{{ asset('assets/admin/uploads/' . $emp->emp_photo) }}"
                     alt="{{ $emp->employee_name_A }}"
                     class="rounded-circle"
                     style="width:38px;height:38px;object-fit:cover;border:2px solid #dee2e6;">
              @else
                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center"
                     style="width:38px;height:38px;font-size:.85em;font-weight:bold;flex-shrink:0;">
                  {{ mb_substr($emp->employee_name_A ?? '?', 0, 1) }}
                </div>
              @endif
            </div>
            <div>
              <div class="font-weight-bold">{{ $emp->employee_name_A }}</div>
              @if($emp->employee_name_E)<small class="text-muted">{{ $emp->employee_name_E }}</small>@endif
            </div>
          </div>
        </td>
        <td><small>{{ $emp->national_id ?? '—' }}</small></td>
        <td class="text-center">
          @if($emp->is_has_finger == 1)<code>{{ $emp->finger_id }}</code>
          @else<span class="text-muted">—</span>@endif
        </td>
        <td>
          <small class="d-block">{{ $emp->department->dep_name ?? '—' }}</small>
          <small class="text-muted">{{ $emp->jobs_categories->job_name ?? '—' }}</small>
        </td>
        <td>
          @if($emp->client_id)
            <span class="badge" style="background:#b8860b;color:#fff;font-size:.78em">
              {{ $emp->client->client_name ?? '—' }}
            </span>
            @if($emp->hrid)<br><small class="text-muted">{{ $emp->hrid }}</small>@endif
          @else
            <small>{{ $emp->branches->branch_name ?? '—' }}</small>
          @endif
        </td>
        <td class="text-center">
          @if($emp->emp_sal)<strong>{{ number_format($emp->emp_sal,0) }}</strong><small class="text-muted d-block">{{ __('admin.egp') }}</small>
          @else —@endif
        </td>
        <td>
          <small>{{ $emp->emp_start_date ? \Carbon\Carbon::parse($emp->emp_start_date)->format('Y-m-d') : '—' }}</small>
          @if($emp->emp_start_date)<br><small class="text-muted">{{ \Carbon\Carbon::parse($emp->emp_start_date)->diffInYears(now()) }} {{ __('admin.years') }}</small>@endif
        </td>
        <td>
          @switch($emp->functional_status)
            @case(1)<span class="badge badge-success">{{ __('admin.emp_working') }}</span>@break
            @case(2)<span class="badge badge-danger">{{ __('admin.emp_not_working') }}</span>@break
            @default<span class="badge badge-secondary">—</span>
          @endswitch
          @if($emp->emp_gender==1)<span class="badge badge-light ml-1">{{ __('admin.male') }}</span>
          @elseif($emp->emp_gender==2)<span class="badge badge-light ml-1">{{ __('admin.female') }}</span>@endif
        </td>
        <td>
          <a href="{{ route('employees.show',$emp->id) }}" class="btn btn-xs btn-info" title="{{ __('admin.view') }}"><i class="fas fa-eye"></i></a>
          <a href="{{ route('employees.edit',$emp->id) }}" class="btn btn-xs btn-warning" title="{{ __('admin.edit') }}"><i class="fas fa-edit"></i></a>
          <a href="{{ route('employees.delete',$emp->id) }}" class="btn btn-xs btn-danger" title="{{ __('admin.delete') }}" onclick="return confirm('{{ __('admin.emp_delete_confirm') }}')"><i class="fas fa-trash"></i></a>
        </td>
      </tr>
      @empty
      <tr><td colspan="11" class="text-center py-4 text-muted">
        <i class="fas fa-search fa-2x mb-2 d-block"></i>{{ __('admin.emp_no_results') }}
        <br><a href="{{ route('employees.index') }}" class="btn btn-sm btn-outline-secondary mt-2">{{ __('admin.emp_clear_filters') }}</a>
      </td></tr>
      @endforelse
    </tbody>
  </table>
  </div>
  </div>
  <div class="card-footer d-flex justify-content-between align-items-center">
    <small class="text-muted">{{ __('admin.view') }} {{ $data->firstItem() }}–{{ $data->lastItem() }} {{ __('admin.from') }} {{ $data->total() }}</small>
    {{ $data->appends(request()->except('page'))->links() }}
  </div>
</div>
</div>
@endsection
@section('script')
<script>
document.getElementById('toggleAdv').addEventListener('click', function() {
  var f = document.getElementById('advFilters');
  f.style.display = f.style.display === 'none' ? '' : 'none';
});
(function() {
  var adv = ['search_branch','search_dept','search_job','search_shift','search_gender',
    'search_insurance','search_has_finger','search_finger','sal_from','sal_to','hire_from','hire_to','per_page'];
  var params = new URLSearchParams(window.location.search);
  for(var i=0;i<adv.length;i++) {
    if(params.get(adv[i])) { document.getElementById('advFilters').style.display=''; break; }
  }
})();
</script>
@endsection
