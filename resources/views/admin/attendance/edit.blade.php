@extends('admin.layouts.admin')
@section('title') {{ __('admin.att_edit_title') }} @endsection
@section('start') {{ __('admin.hr_management') }} @endsection
@section('home') <a href="{{ route('attendance.index') }}">{{ __('admin.att_title') }}</a> @endsection
@section('startpage') {{ __('admin.edit') }} @endsection

@section('content')
<div class="col-md-9 mx-auto">

    @if($attendance->missing_punch && !$attendance->missing_punch_resolution)
    <div class="alert alert-warning alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <h5><i class="fas fa-exclamation-triangle ml-1"></i>
            {{ __('admin.att_warning') }}: {{ $attendance->missing_punch === 'out' ? __('admin.att_missing_checkout') : __('admin.att_missing_checkin') }}
        </h5>
        {{ __('admin.att_missing_punch_info') }}
    </div>
    @endif

    <div class="card card-warning">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-edit ml-2"></i>
                {{ __('admin.att_edit_title') }} — {{ $attendance->employee->employee_name_A ?? '' }}
                <span class="badge badge-light mr-2">{{ $attendance->attendance_date->format('Y-m-d') }}</span>
                {!! $attendance->status_label !!}
            </h3>
        </div>
        <form action="{{ route('attendance.update', $attendance->id) }}" method="POST">
            @csrf
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @php $effectiveShift = $attendance->effective_shift; @endphp
                @if($effectiveShift)
                <div class="alert alert-info mb-3">
                    <i class="fas fa-clock ml-1"></i>
                    <strong>{{ __('admin.att_shift_used') }}:</strong>
                    {{ $effectiveShift->type }} —
                    {{ __('admin.att_from_date') }} <strong>{{ $effectiveShift->from_time }}</strong>
                    — <strong>{{ $effectiveShift->to_time }}</strong>
                    @if($attendance->shift_override_id)
                        <span class="badge badge-warning mr-2">{{ __('admin.att_custom_shift') }}</span>
                    @endif
                </div>
                @endif

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>{{ __('admin.att_employee') }}</label>
                        <input type="text" class="form-control bg-light" readonly
                            value="{{ $attendance->employee->employee_name_A ?? '-' }} ({{ $attendance->employee->employee_id ?? '' }})">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{ __('admin.att_date') }}</label>
                        <input type="text" class="form-control bg-light" readonly
                            value="{{ $attendance->attendance_date->format('Y-m-d') }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>
                            {{ __('admin.att_check_in') }}
                            @if($attendance->missing_punch === 'in')
                                <span class="badge badge-danger">{{ __('admin.att_missing') }}</span>
                            @endif
                        </label>
                        <input type="time" name="check_in_time" id="checkInTime" class="form-control
                            {{ $attendance->missing_punch === 'in' ? 'border-danger' : '' }}"
                            value="{{ old('check_in_time', $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') : '') }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>
                            {{ __('admin.att_check_out') }}
                            @if($attendance->missing_punch === 'out')
                                <span class="badge badge-danger">{{ __('admin.att_missing') }}</span>
                            @endif
                        </label>
                        <input type="time" name="check_out_time" id="checkOutTime" class="form-control
                            {{ $attendance->missing_punch === 'out' ? 'border-danger' : '' }}"
                            value="{{ old('check_out_time', $attendance->check_out_time ? \Carbon\Carbon::parse($attendance->check_out_time)->format('H:i') : '') }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>{{ __('admin.att_status') }} <span class="text-danger">*</span></label>
                        <select name="status" id="statusSelect" class="form-control" required
                                onchange="handleStatusChange(this.value)">
                            <option value="1" {{ $attendance->status==1?'selected':'' }}>{{ __('admin.att_present') }}</option>
                            <option value="2" {{ $attendance->status==2?'selected':'' }}>{{ __('admin.att_absent') }}</option>
                            <option value="3" {{ $attendance->status==3?'selected':'' }}>{{ __('admin.att_vacation') }}</option>
                            <option value="4" {{ $attendance->status==4?'selected':'' }}>{{ __('admin.att_official_vacation') }}</option>
                            <option value="5" {{ $attendance->status==5?'selected':'' }}>{{ __('admin.att_mission') }}</option>
                            <option value="6" {{ $attendance->status==6?'selected':'' }}>{{ __('admin.att_weekly_vacation') }}</option>
                        </select>
                    </div>
                </div>

                <div id="weeklyLeaveSection" style="display:{{ $attendance->status==6 ? 'block' : 'none' }}">
                    <div class="alert" style="background:#f3eeff;border:1px solid #6f42c1;border-radius:6px">
                        <i class="fas fa-calendar-week ml-1" style="color:#6f42c1"></i>
                        <strong>{{ __('admin.att_weekly_vacation') }}</strong> — {{ __('admin.att_weekly_leave_info') }}
                    </div>
                </div>

                @if($attendance->status == 1)
                <div id="weeklyOffWorkedSection" style="display:{{ $attendance->is_weekly_off_worked ? 'block' : 'none' }}">
                    <div class="alert alert-success" style="border-radius:6px">
                        <i class="fas fa-umbrella-beach ml-1"></i>
                        <strong>{{ __('admin.att_rest_day') }}</strong> —
                        {{ __('admin.att_rest_day_info') }}
                    </div>
                    <div class="row">
                        <div class="col-md-5 form-group">
                            <label>
                                <input type="checkbox" name="is_weekly_off_worked" value="1"
                                    id="weeklyOffWorkedChk"
                                    {{ $attendance->is_weekly_off_worked ? 'checked' : '' }}>
                                {{ __('admin.att_calc_leave_comp') }}
                            </label>
                            <div class="mt-1">
                                <span class="badge badge-success" style="font-size:1em">
                                    {{ __('admin.att_leave_comp') }}: {{ number_format($attendance->leave_compensation_amount ?? 0, 2) }} {{ __('admin.egp') }}
                                </span>
                            </div>
                            <small class="text-muted">
                                {{ __('admin.att_leave_comp_hint') }}
                            </small>
                        </div>
                    </div>
                </div>
                @endif

                <div id="permissionSection" style="display:{{ $attendance->status==1 ? 'block' : 'none' }}">
                    <hr>
                    <h6 class="text-info"><i class="fas fa-id-card ml-1"></i>{{ __('admin.att_permissions_label') }}</h6>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>{{ __('admin.att_late_perm_min') }}</label>
                            <div class="input-group">
                                <input type="number" name="permission_minutes" class="form-control"
                                    min="0" step="1"
                                    value="{{ old('permission_minutes', $attendance->permission_minutes ?? 0) }}">
                                <div class="input-group-append"><span class="input-group-text">{{ __('admin.att_min_abbr') }}</span></div>
                            </div>
                            <small class="text-muted">
                                {{ __('admin.att_deducted_from_late') }}
                                @if(isset($settings) && $settings->max_permission_minutes_per_day > 0)
                                    — {{ __('admin.att_max_per_day') }}: {{ $settings->max_permission_minutes_per_day }} {{ __('admin.att_min_abbr') }}
                                @endif
                            </small>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>{{ __('admin.att_early_perm_min') }}</label>
                            <div class="input-group">
                                <input type="number" name="permission_early_minutes" class="form-control"
                                    min="0" step="1"
                                    value="{{ old('permission_early_minutes', $attendance->permission_early_minutes ?? 0) }}">
                                <div class="input-group-append"><span class="input-group-text">{{ __('admin.att_min_abbr') }}</span></div>
                            </div>
                            <small class="text-muted">{{ __('admin.att_deducted_from_early') }}</small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>{{ __('admin.notes') }}</label>
                    <input type="text" name="notes" class="form-control"
                        value="{{ old('notes', $attendance->notes) }}">
                </div>

                <div class="row mt-2">
                    <div class="col-md-3">
                        <div class="info-box bg-warning">
                            <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('admin.att_late') }}</span>
                                <span class="info-box-number">{{ $attendance->late_display }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-orange">
                            <span class="info-box-icon"><i class="fas fa-sign-out-alt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('admin.att_early_out') }}</span>
                                <span class="info-box-number">{{ $attendance->early_departure_display }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-success">
                            <span class="info-box-icon"><i class="fas fa-plus"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('admin.att_overtime') }}</span>
                                <span class="info-box-number">{{ $attendance->overtime_hours }} {{ __('admin.att_hr_abbr') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-danger">
                            <span class="info-box-icon"><i class="fas fa-minus"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('admin.att_late_deduction') }} + {{ __('admin.att_early_deduction') }}</span>
                                <span class="info-box-number">
                                    {{ number_format(($attendance->late_deduction ?? 0) + ($attendance->early_departure_deduction ?? 0), 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="info-box bg-info">
                            <span class="info-box-icon"><i class="fas fa-coins"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('admin.att_ot_amount') }}</span>
                                <span class="info-box-number">{{ number_format($attendance->overtime_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    @if(($attendance->permission_minutes ?? 0) > 0 || ($attendance->permission_early_minutes ?? 0) > 0)
                    <div class="col-md-3">
                        <div class="info-box bg-teal">
                            <span class="info-box-icon"><i class="fas fa-id-card"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('admin.att_approved_perm') }}</span>
                                <span class="info-box-number">
                                    {{ ($attendance->permission_minutes ?? 0) + ($attendance->permission_early_minutes ?? 0) }} {{ __('admin.att_min_abbr') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                <small class="text-muted">{{ __('admin.att_recalc_hint') }}</small>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save ml-1"></i> {{ __('admin.att_save_changes') }}
                </button>
                <a href="{{ route('attendance.index') }}" class="btn btn-secondary mr-2">{{ __('admin.back') }}</a>
            </div>
        </form>
    </div>

    <div class="card card-info mt-3">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-exchange-alt ml-2"></i>{{ __('admin.att_change_shift_card') }}
            </h3>
        </div>
        <form action="{{ route('attendance.update_shift', $attendance->id) }}" method="POST">
            @csrf
            <div class="card-body">
                <p class="text-muted small">
                    {{ __('admin.att_original_shift') }}: <strong>{{ $attendance->shift->type ?? '—' }}</strong>.
                    {{ __('admin.att_shift_override_hint') }}
                </p>
                <div class="form-group">
                    <label>{{ __('admin.att_custom_shift_for_day') }}</label>
                    <select name="shift_override_id" class="form-control">
                        <option value="">-- {{ __('admin.att_use_original_shift') }} ({{ $attendance->shift->type ?? __('admin.att_not_specified') }}) --</option>
                        @foreach($shifts_types as $shift)
                            <option value="{{ $shift->id }}"
                                {{ $attendance->shift_override_id == $shift->id ? 'selected' : '' }}>
                                {{ $shift->type }}
                                ({{ $shift->from_time }} → {{ $shift->to_time }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-info">
                    <i class="fas fa-sync ml-1"></i> {{ __('admin.att_apply_shift') }}
                </button>
            </div>
        </form>
    </div>

    @if($attendance->missing_punch)
    <div class="card card-danger mt-3">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-fingerprint ml-2"></i>
                {{ __('admin.att_missing_punch_card') }}
                ({{ $attendance->missing_punch === 'out' ? __('admin.att_missing_checkout') : __('admin.att_missing_checkin') }})
            </h3>
        </div>
        <div class="card-body">
            @if($attendance->missing_punch_resolution)
            <div class="alert alert-success">
                <i class="fas fa-check-circle ml-1"></i>
                {{ __('admin.att_resolved_prev') }}: <strong>{{ $attendance->missing_punch_resolution_label }}</strong>
            </div>
            @endif

            <form action="{{ route('attendance.resolve_missing', $attendance->id) }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-5 form-group">
                        <label>{{ __('admin.att_resolution_method') }} <span class="text-danger">*</span></label>
                        <select name="missing_punch_resolution" id="resolutionSelect" class="form-control" required>
                            <option value="">-- {{ __('admin.emp_marital_choose') }} --</option>
                            <option value="1" {{ $attendance->missing_punch_resolution==1?'selected':'' }}>{{ __('admin.att_quarter_day') }}</option>
                            <option value="2" {{ $attendance->missing_punch_resolution==2?'selected':'' }}>{{ __('admin.att_half_day') }}</option>
                            <option value="3" {{ $attendance->missing_punch_resolution==3?'selected':'' }}>{{ __('admin.att_full_day') }}</option>
                            <option value="4" {{ $attendance->missing_punch_resolution==4?'selected':'' }}>{{ __('admin.att_forgotten') }}</option>
                            <option value="5" {{ $attendance->missing_punch_resolution==5?'selected':'' }}>{{ __('admin.att_perm_hours') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group" id="hoursField" style="display:none;">
                        <label>{{ __('admin.att_perm_hours_label') }} <span class="text-danger">*</span></label>
                        <input type="number" name="missing_punch_hours" class="form-control"
                            min="0.25" max="24" step="0.25"
                            value="{{ $attendance->missing_punch_hours }}">
                    </div>
                    <div class="col-md-4 form-group d-flex align-items-end">
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-check ml-1"></i> {{ __('admin.att_apply_decision') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
@endsection

@section('script')
<script>
function handleStatusChange(val) {
    var weeklySection    = document.getElementById('weeklyLeaveSection');
    var permissionSection = document.getElementById('permissionSection');

    weeklySection.style.display    = (val == '6') ? 'block' : 'none';
    permissionSection.style.display = (val == '1') ? 'block' : 'none';
}

document.getElementById('resolutionSelect')?.addEventListener('change', function () {
    document.getElementById('hoursField').style.display = this.value === '5' ? 'flex' : 'none';
});

if (document.getElementById('resolutionSelect')?.value === '5') {
    document.getElementById('hoursField').style.display = 'flex';
}

handleStatusChange(document.getElementById('statusSelect').value);
</script>
@endsection
