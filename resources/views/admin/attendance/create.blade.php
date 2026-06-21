@extends('admin.layouts.admin')
@section('title') {{ __('admin.att_title') }} @endsection
@section('start') {{ __('admin.att_title') }} @endsection
@section('home') <a href="{{ route('attendance.index') }}">{{ __('admin.att_title') }}</a> @endsection
@section('startpage') {{ __('admin.add') }} @endsection

@section('content')
<div class="col-md-8 mx-auto">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-check ml-2"></i>{{ __('admin.att_individual_title') }}</h3>
        </div>

        @if(session('error'))
            <div class="alert alert-danger mx-3 mt-2">{{ session('error') }}</div>
        @endif

        <form action="{{ route('attendance.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-8 form-group">
                        <label>{{ __('admin.att_employee') }} <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-control select2" required id="employeeSelect">
                            <option value="">
                                -- {{ __('admin.att_select_employee') }}
                                @if($employees->count())
                                    ({{ $employees->count() }} {{ __('admin.att_emp_available') }})
                                @else
                                    — {{ __('admin.att_no_employees') }}
                                @endif
                                --
                            </option>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}"
                                data-shift-name="{{ optional($emp->shifts_type)->type }}"
                                data-shift-from="{{ optional($emp->shifts_type)->from_time }}"
                                data-shift-to="{{ optional($emp->shifts_type)->to_time }}"
                                {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->employee_name_A }}
                                ({{ $emp->employee_id }})
                                @if($emp->finger_id) — Finger #{{ $emp->finger_id }} @endif
                            </option>
                            @endforeach
                        </select>
                        @if($employees->isEmpty())
                            <div class="text-danger mt-1">
                                <i class="fas fa-exclamation-triangle"></i>
                                {{ __('admin.att_no_employees') }}.
                                <a href="{{ route('employees.create') }}">{{ __('admin.add') }}</a>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-4 form-group">
                        <label>{{ __('admin.att_date') }} <span class="text-danger">*</span></label>
                        <input type="date" name="attendance_date" class="form-control" required
                            value="{{ old('attendance_date', today()->format('Y-m-d')) }}">
                    </div>
                </div>

                <div id="shiftInfo" class="alert alert-info d-none mb-3">
                    <i class="fas fa-clock ml-1"></i>
                    <strong>{{ __('admin.att_shift') }}:</strong> <span id="shiftName"></span>
                    &nbsp;|&nbsp;<strong>{{ __('admin.att_from_date') }}:</strong> <span id="shiftFrom"></span>
                    &nbsp;—&nbsp;<strong><span id="shiftTo"></span></strong>
                </div>

                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>{{ __('admin.att_check_in') }}</label>
                        <input type="time" name="check_in_time" class="form-control" id="checkIn"
                            value="{{ old('check_in_time') }}" onchange="calcPreview()">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>{{ __('admin.att_check_out') }}</label>
                        <input type="time" name="check_out_time" class="form-control" id="checkOut"
                            value="{{ old('check_out_time') }}" onchange="calcPreview()">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>{{ __('admin.att_status') }} <span class="text-danger">*</span></label>
                        <select name="status" class="form-control" required id="statusSel">
                            <option value="1" {{ old('status',1)==1?'selected':'' }}>✅ {{ __('admin.att_present') }}</option>
                            <option value="2" {{ old('status')==2?'selected':'' }}>❌ {{ __('admin.att_absent') }}</option>
                            <option value="3" {{ old('status')==3?'selected':'' }}>🏖 {{ __('admin.att_vacation') }}</option>
                            <option value="4" {{ old('status')==4?'selected':'' }}>📅 {{ __('admin.att_official_vacation') }}</option>
                            <option value="5" {{ old('status')==5?'selected':'' }}>🏢 {{ __('admin.att_mission') }}</option>
                        </select>
                    </div>
                </div>

                <div class="row" id="previewRow" style="display:none">
                    <div class="col-md-6">
                        <div class="callout callout-danger py-2 px-3">
                            <small>{{ __('admin.att_expected_late') }}</small>
                            <h5 class="mb-0"><span id="prevLate">0</span> {{ __('admin.att_min_abbr') }}</h5>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="callout callout-success py-2 px-3">
                            <small>{{ __('admin.att_expected_ot') }}</small>
                            <h5 class="mb-0"><span id="prevOT">0</span> {{ __('admin.att_hr_abbr') }}</h5>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>{{ __('admin.notes') }}</label>
                    <input type="text" name="notes" class="form-control"
                        value="{{ old('notes') }}" placeholder="{{ __('admin.optional') }}">
                </div>
            </div>

            <div class="card-footer d-flex justify-content-between align-items-center">
                <div>
                    <button type="submit" class="btn btn-primary" {{ $employees->isEmpty() ? 'disabled' : '' }}>
                        <i class="fas fa-save ml-1"></i> {{ __('admin.save') }}
                    </button>
                    <a href="{{ route('attendance.index') }}" class="btn btn-secondary mr-2">{{ __('admin.back') }}</a>
                </div>
                <div class="btn-group">
                    <a href="{{ route('attendance.bulk_create') }}" class="btn btn-sm btn-outline-info">
                        <i class="fas fa-list"></i> {{ __('admin.att_bulk_entry') }}
                    </a>
                    <a href="{{ route('attendance.excel_import_form') }}" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-file-excel"></i> Excel
                    </a>
                    <a href="{{ route('fingerprint_devices.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-fingerprint"></i> {{ __('admin.fingerprint') }}
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script>
let sf = '', st = '';

document.getElementById('employeeSelect').addEventListener('change', function () {
    const o = this.options[this.selectedIndex];
    sf = o.dataset.shiftFrom || '';
    st = o.dataset.shiftTo   || '';
    const sn = o.dataset.shiftName || '';
    if (sn) {
        document.getElementById('shiftInfo').classList.remove('d-none');
        document.getElementById('shiftName').textContent = sn;
        document.getElementById('shiftFrom').textContent = sf;
        document.getElementById('shiftTo').textContent   = st;
    } else {
        document.getElementById('shiftInfo').classList.add('d-none');
    }
    calcPreview();
});

document.getElementById('statusSel').addEventListener('change', function () {
    const absent = this.value === '2';
    ['checkIn','checkOut'].forEach(id => {
        const el = document.getElementById(id);
        el.disabled = absent;
        if (absent) el.value = '';
    });
    if (absent) document.getElementById('previewRow').style.display = 'none';
});

function toMin(t) { if (!t) return null; const [h,m] = t.split(':').map(Number); return h*60+m; }

function calcPreview() {
    const ci = document.getElementById('checkIn').value;
    const co = document.getElementById('checkOut').value;
    if (!sf || !ci) { document.getElementById('previewRow').style.display = 'none'; return; }

    const late = Math.max(0, toMin(ci) - toMin(sf));
    const ot   = co ? Math.max(0, (toMin(co) - toMin(st)) / 60) : 0;

    document.getElementById('prevLate').textContent = late;
    document.getElementById('prevOT').textContent   = ot.toFixed(2);
    document.getElementById('previewRow').style.display = 'flex';
}
</script>
@endsection
