@extends('admin.layouts.admin')
@section('title') {{ __('admin.att_title') }} @endsection
@section('start') {{ __('admin.hr_management') }} @endsection
@section('home') <a href="{{ route('attendance.index') }}">{{ __('admin.att_title') }}</a> @endsection
@section('startpage') {{ __('admin.view') }} @endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">
                <i class="fas fa-fingerprint ml-2"></i>
                {{ __('admin.att_records') }}
                <a class="btn btn-sm btn-success mr-2" href="{{ route('attendance.create') }}">
                    <i class="fas fa-plus"></i> {{ __('admin.att_add') }}
                </a>
                <a class="btn btn-sm btn-info mr-1" href="{{ route('attendance.bulk_create') }}">
                    <i class="fas fa-list"></i> {{ __('admin.att_bulk_entry') }}
                </a>
                <a class="btn btn-sm btn-purple mr-1" href="{{ route('attendance.generate_weekly_leaves_form') }}"
                   style="background:#6f42c1;color:#fff;border-color:#6f42c1">
                    <i class="fas fa-calendar-week"></i> {{ __('admin.att_gen_weekly') }}
                </a>
            </h3>
        </div>

        <div class="card-body pb-0">
            <form method="GET" action="{{ route('attendance.index') }}" class="row">
                <div class="col-md-3 form-group">
                    <label>{{ __('admin.att_employee') }}</label>
                    <select name="employee_id" class="form-control select2">
                        <option value="">-- {{ __('admin.all') }} --</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('employee_id')==$emp->id ? 'selected':'' }}>
                            {{ $emp->employee_name_A }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 form-group">
                    <label>{{ __('admin.att_from_date') }}</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2 form-group">
                    <label>{{ __('admin.att_to_date') }}</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-2 form-group">
                    <label>{{ __('admin.att_status') }}</label>
                    <select name="status" class="form-control">
                        <option value="">-- {{ __('admin.all') }} --</option>
                        <option value="1" {{ request('status')==1?'selected':'' }}>{{ __('admin.att_present') }}</option>
                        <option value="2" {{ request('status')==2?'selected':'' }}>{{ __('admin.att_absent') }}</option>
                        <option value="3" {{ request('status')==3?'selected':'' }}>{{ __('admin.att_vacation') }}</option>
                        <option value="4" {{ request('status')==4?'selected':'' }}>{{ __('admin.att_official_vacation') }}</option>
                        <option value="5" {{ request('status')==5?'selected':'' }}>{{ __('admin.att_mission') }}</option>
                        <option value="6" {{ request('status')==6?'selected':'' }}>{{ __('admin.att_weekly_vacation') }}</option>
                    </select>
                </div>
                <div class="col-md-1 form-group">
                    <label>{{ __('admin.att_per_page') }}</label>
                    <select name="per_page" class="form-control">
                        @foreach([10, 20, 50, 100] as $n)
                            <option value="{{ $n }}" {{ request('per_page', 20) == $n ? 'selected' : '' }}>{{ $n }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 form-group d-flex align-items-end">
                    <button type="submit" class="btn btn-primary ml-2">
                        <i class="fas fa-search"></i> {{ __('admin.search') }}
                    </button>
                </div>
                <div class="col-md-1 form-group d-flex align-items-end">
                    <button type="button" class="btn btn-danger" id="btnBulkDelete"
                            data-employees='@json($employees->pluck("employee_name_A","id"))'>
                        <i class="fas fa-trash-alt"></i> {{ __('admin.delete') }}
                    </button>
                </div>
            </form>

            <div class="modal fade" id="bulkDeleteModal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title w-100 text-center">
                                <i class="fas fa-exclamation-triangle ml-2"></i>
                                {{ __('admin.att_delete_warning') }}
                            </h5>
                        </div>
                        <div class="modal-body pb-1">
                            <div class="alert alert-danger mb-3">
                                <i class="fas fa-exclamation-circle ml-1"></i>
                                {{ __('admin.att_irreversible') }}
                            </div>
                            <table class="table table-sm table-borderless mb-0" id="bulkDeleteSummary">
                            </table>
                        </div>
                        <div class="modal-footer">
                            <form id="bulkDeleteForm" method="POST" action="{{ route('attendance.bulk_delete') }}">
                                @csrf
                                <input type="hidden" name="employee_id" id="bd_employee_id">
                                <input type="hidden" name="from_date"   id="bd_from_date">
                                <input type="hidden" name="to_date"     id="bd_to_date">
                                <input type="hidden" name="status"      id="bd_status">
                                <button type="button" class="btn btn-secondary ml-2" data-dismiss="modal">
                                    <i class="fas fa-times ml-1"></i> {{ __('admin.cancel') }}
                                </button>
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash-alt ml-1"></i> {{ __('admin.att_confirm_delete') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success mx-3">{!! session('success') !!}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger mx-3">{{ session('error') }}</div>
        @endif

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th>{{ __('admin.att_date') }}</th>
                            <th>{{ __('admin.att_employee') }}</th>
                            <th>{{ __('admin.att_shift') }}</th>
                            <th>{{ __('admin.att_check_in') }}</th>
                            <th>{{ __('admin.att_check_out') }}</th>
                            <th>{{ __('admin.att_status') }}</th>
                            <th>{{ __('admin.att_late') }}</th>
                            <th>{{ __('admin.att_early_out') }}</th>
                            <th>{{ __('admin.att_overtime') }}</th>
                            <th>{{ __('admin.att_late_deduction') }}</th>
                            <th>{{ __('admin.att_early_deduction') }}</th>
                            <th>{{ __('admin.att_ot_amount') }}</th>
                            <th>{{ __('admin.att_leave_comp') }}</th>
                            <th>{{ __('admin.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $rec)
                        <tr>
                            <td>{{ $rec->attendance_date->format('Y-m-d') }}</td>
                            <td>{{ $rec->employee->employee_name_A ?? '-' }}</td>
                            <td>
                                @if($rec->shift)
                                    {{ $rec->shift->type }}
                                    <small class="text-muted">({{ $rec->shift->from_time }} - {{ $rec->shift->to_time }})</small>
                                @else - @endif
                            </td>
                            <td>{{ $rec->check_in_time ?? '-' }}</td>
                            <td>{{ $rec->check_out_time ?? '-' }}</td>
                            <td>{!! $rec->status_label !!}</td>
                            <td>
                                @if($rec->late_minutes > 0 || $rec->late_fraction)
                                    <span class="text-danger font-weight-bold">{{ $rec->late_display }}</span>
                                    @if($rec->permission_minutes > 0)
                                        <br><small class="text-success">{{ __('admin.att_permission') }}: {{ $rec->permission_minutes }} {{ __('admin.att_min_abbr') }}</small>
                                    @endif
                                @else
                                    <span class="text-success">—</span>
                                @endif
                            </td>
                            <td>
                                @if($rec->early_departure_minutes > 0)
                                    @php $earlyFrac = $rec->early_departure_fraction ?? null; @endphp
                                    <span class="font-weight-bold {{ $earlyFrac >= 3 ? 'text-danger' : 'text-warning' }}">
                                        {{ $rec->early_departure_display }}
                                    </span>
                                    @if($earlyFrac == 4)
                                        <br><small class="text-danger">⚠ {{ __('admin.att_incomplete_day') }}</small>
                                    @endif
                                    @if($rec->permission_early_minutes > 0)
                                        <br><small class="text-success">{{ __('admin.att_permission') }}: {{ $rec->permission_early_minutes }} {{ __('admin.att_min_abbr') }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($rec->overtime_hours > 0)
                                    <span class="text-success">{{ $rec->overtime_hours }}</span>
                                @else 0 @endif
                            </td>
                            <td class="text-danger">{{ number_format($rec->late_deduction, 2) }}</td>
                            <td class="text-warning">{{ number_format($rec->early_departure_deduction ?? 0, 2) }}</td>
                            <td class="text-success">{{ number_format($rec->overtime_amount, 2) }}</td>
                            <td>
                                @if($rec->is_weekly_off_worked && ($rec->leave_compensation_amount ?? 0) > 0)
                                    <span class="text-success font-weight-bold">
                                        {{ number_format($rec->leave_compensation_amount, 2) }}
                                    </span>
                                    <br><small class="badge badge-success" style="font-size:.75em">{{ __('admin.att_rest_day') }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('attendance.edit', $rec->id) }}"
                                   class="btn btn-xs btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('attendance.delete', $rec->id) }}"
                                   class="btn btn-xs btn-danger"
                                   onclick="return confirm('{{ __('admin.att_delete_confirm') }}')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="13" class="text-center">{{ __('admin.no_data') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $data->appends(request()->query())->links('vendor.pagination.attendance') }}
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.getElementById('btnBulkDelete').addEventListener('click', function () {
    var employees = JSON.parse(this.dataset.employees || '{}');

    var employeeId = document.querySelector('select[name="employee_id"]').value;
    var fromDate   = document.querySelector('input[name="from_date"]').value;
    var toDate     = document.querySelector('input[name="to_date"]').value;
    var statusEl   = document.querySelector('select[name="status"]');
    var statusVal  = statusEl.value;
    var statusText = statusEl.options[statusEl.selectedIndex].text;

    document.getElementById('bd_employee_id').value = employeeId;
    document.getElementById('bd_from_date').value   = fromDate;
    document.getElementById('bd_to_date').value     = toDate;
    document.getElementById('bd_status').value      = statusVal;

    var empName = employeeId ? (employees[employeeId] || '—') : '{{ __('admin.att_all_employees') }}';
    var rows = [
        ['{{ __('admin.att_employee') }}', empName],
        ['{{ __('admin.att_from_date') }}', fromDate || '{{ __('admin.att_not_specified') }}'],
        ['{{ __('admin.att_to_date') }}', toDate || '{{ __('admin.att_not_specified') }}'],
        ['{{ __('admin.att_status') }}', statusVal ? statusText : '{{ __('admin.att_all_statuses') }}'],
    ];

    var html = rows.map(function(r) {
        return '<tr>'
             + '<td class="font-weight-bold text-muted" style="width:42%">' + r[0] + '</td>'
             + '<td class="text-dark font-weight-bold">' + r[1] + '</td>'
             + '</tr>';
    }).join('');

    document.getElementById('bulkDeleteSummary').innerHTML = html;
    $('#bulkDeleteModal').modal('show');
});
</script>
@endsection
