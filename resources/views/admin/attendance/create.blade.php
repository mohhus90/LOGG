@extends('admin.layouts.admin')
@section('title') تسجيل حضور @endsection
@section('start') الحضور والانصراف @endsection
@section('home') <a href="{{ route('attendance.index') }}">الحضور والانصراف</a> @endsection
@section('startpage') إضافة @endsection

@section('content')
<div class="col-md-8 mx-auto">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-check ml-2"></i>تسجيل حضور وانصراف فردي</h3>
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
                        <label>الموظف <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-control" required id="employeeSelect">
                            <option value="">
                                -- اختر الموظف
                                @if($employees->count())
                                    ({{ $employees->count() }} موظف متاح)
                                @else
                                    — لا يوجد موظفون
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
                                @if($emp->finger_id) — بصمة #{{ $emp->finger_id }} @endif
                            </option>
                            @endforeach
                        </select>
                        @if($employees->isEmpty())
                            <div class="text-danger mt-1">
                                <i class="fas fa-exclamation-triangle"></i>
                                لا يوجد موظفون في النظام.
                                <a href="{{ route('employees.create') }}">إضافة موظف</a>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-4 form-group">
                        <label>التاريخ <span class="text-danger">*</span></label>
                        <input type="date" name="attendance_date" class="form-control" required
                            value="{{ old('attendance_date', today()->format('Y-m-d')) }}">
                    </div>
                </div>

                {{-- معلومات الشيفت تظهر بعد اختيار الموظف --}}
                <div id="shiftInfo" class="alert alert-info d-none mb-3">
                    <i class="fas fa-clock ml-1"></i>
                    <strong>الشيفت:</strong> <span id="shiftName"></span>
                    &nbsp;|&nbsp;<strong>من:</strong> <span id="shiftFrom"></span>
                    &nbsp;إلى&nbsp;<strong><span id="shiftTo"></span></strong>
                </div>

                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>وقت الحضور</label>
                        <input type="time" name="check_in_time" class="form-control" id="checkIn"
                            value="{{ old('check_in_time') }}" onchange="calcPreview()">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>وقت الانصراف</label>
                        <input type="time" name="check_out_time" class="form-control" id="checkOut"
                            value="{{ old('check_out_time') }}" onchange="calcPreview()">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>الحالة <span class="text-danger">*</span></label>
                        <select name="status" class="form-control" required id="statusSel">
                            <option value="1" {{ old('status',1)==1?'selected':'' }}>✅ حضر</option>
                            <option value="2" {{ old('status')==2?'selected':'' }}>❌ غياب</option>
                            <option value="3" {{ old('status')==3?'selected':'' }}>🏖 إجازة</option>
                            <option value="4" {{ old('status')==4?'selected':'' }}>📅 إجازة رسمية</option>
                            <option value="5" {{ old('status')==5?'selected':'' }}>🏢 مأمورية</option>
                        </select>
                    </div>
                </div>

                {{-- عرض احتساب مبدئي --}}
                <div class="row" id="previewRow" style="display:none">
                    <div class="col-md-6">
                        <div class="callout callout-danger py-2 px-3">
                            <small>تأخير متوقع</small>
                            <h5 class="mb-0"><span id="prevLate">0</span> دقيقة</h5>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="callout callout-success py-2 px-3">
                            <small>أوفرتايم متوقع</small>
                            <h5 class="mb-0"><span id="prevOT">0</span> ساعة</h5>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>ملاحظات</label>
                    <input type="text" name="notes" class="form-control"
                        value="{{ old('notes') }}" placeholder="اختياري">
                </div>
            </div>

            <div class="card-footer d-flex justify-content-between align-items-center">
                <div>
                    <button type="submit" class="btn btn-primary" {{ $employees->isEmpty() ? 'disabled' : '' }}>
                        <i class="fas fa-save ml-1"></i> حفظ
                    </button>
                    <a href="{{ route('attendance.index') }}" class="btn btn-secondary mr-2">رجوع</a>
                </div>
                <div class="btn-group">
                    <a href="{{ route('attendance.bulk_create') }}" class="btn btn-sm btn-outline-info">
                        <i class="fas fa-list"></i> دفعي
                    </a>
                    <a href="{{ route('attendance.excel_import_form') }}" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-file-excel"></i> Excel
                    </a>
                    <a href="{{ route('fingerprint_devices.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-fingerprint"></i> بصمة
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
