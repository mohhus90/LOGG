@if(isset($finance_cln_periods) && !empty($finance_cln_periods) && count($finance_cln_periods))
<div class="row">
  @foreach ($finance_cln_periods as $info)
  <div class="col-md-4 mb-3">
    <div class="card card-outline card-primary h-100">
      <div class="card-header py-2 text-center">
        <h6 class="card-title mb-0 font-weight-bold">
          {{ $info->Month->monthe_name }}
          <small class="text-muted d-block">{{ $info->Month->monthe_name_en }}</small>
        </h6>
      </div>
      <div class="card-body py-2 px-3">
        <table class="table table-sm table-borderless mb-0">
          <tr>
            <td class="text-muted pr-0" style="width:40%">{{ __('admin.from') }}</td>
            <td class="font-weight-bold">{{ \Carbon\Carbon::parse($info->start_date)->format('Y-m-d') }}</td>
          </tr>
          <tr>
            <td class="text-muted pr-0">{{ __('admin.to') }}</td>
            <td class="font-weight-bold">{{ \Carbon\Carbon::parse($info->end_date)->format('Y-m-d') }}</td>
          </tr>
          <tr>
            <td class="text-muted pr-0">{{ __('admin.days') }}</td>
            <td>
              <span class="badge badge-info">{{ $info->number_of_days }} {{ __('admin.day') }}</span>
            </td>
          </tr>
        </table>
      </div>
      <div class="card-footer py-2 text-center">
        <a href="{{ route('finance_cln_period.edit', $info->id) }}" class="btn btn-sm btn-success">
          <i class="fas fa-edit"></i> {{ __('admin.edit') }}
        </a>
      </div>
    </div>
  </div>
  @endforeach
</div>
@else
<div class="text-center py-4">
  <i class="fas fa-calendar-times fa-3x text-muted mb-2"></i>
  <h5 class="text-muted">{{ __('admin.no_data') }}</h5>
</div>
@endif
