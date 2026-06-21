@extends('admin.layouts.admin')
@section('title') تعديل سجل الحضور @endsection
@section('start') شئون الموظفين @endsection
@section('home') <a href="{{ route('attendance.index') }}">الحضور والانصراف</a> @endsection
@section('startpage') تعديل @endsection

@section('content')
<div class="col-md-9 mx-auto">

    {{-- ─── تنبيه البصمة الناقصة ─── --}}
    @if($attendance->missing_punch && !$attendance->missing_punch_resolution)
    <div class="alert alert-warning alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <h5><i class="fas fa-exclamation-triangle ml-1"></i>
            تحذير: {{ $attendance->missing_punch === 'out' ? 'انصراف مفقود' : 'حضور مفقود' }}
        </h5>
        هذا السجل يحتوي على بصمة ناقصة — يرجى تحديد طريقة المعالجة أدناه أو تعديل الوقت المفقود يدوياً.
    </div>
    @endif

    {{-- ─── بطاقة التعديل الرئيسية ─── --}}
    <div class="card card-warning">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-edit ml-2"></i>
                تعديل سجل الحضور — {{ $attendance->employee->employee_name_A ?? '' }}
                <span class="badge badge-light mr-2">{{ $attendance->attendance_date->format('Y-m-d') }}</span>
                {!! $attendance->status_label !!}
                @if($attendance->is_manual_lock)
                    <span class="badge badge-danger mr-2"><i class="fas fa-lock ml-1"></i>مثبَّت</span>
                @endif
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

                {{-- الشيفت الفعلي --}}
                @php $effectiveShift = $attendance->effective_shift; @endphp
                @if($effectiveShift)
                <div class="alert alert-info mb-3">
                    <i class="fas fa-clock ml-1"></i>
                    <strong>الشيفت المستخدم في الاحتساب:</strong>
                    {{ $effectiveShift->type }} —
                    من <strong>{{ $effectiveShift->from_time }}</strong>
                    إلى <strong>{{ $effectiveShift->to_time }}</strong>
                    @if($attendance->shift_override_id)
                        <span class="badge badge-warning mr-2">شيفت مخصص</span>
                    @endif
                </div>
                @endif

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>الموظف</label>
                        <input type="text" class="form-control bg-light" readonly
                            value="{{ $attendance->employee->employee_name_A ?? '-' }} ({{ $attendance->employee->employee_id ?? '' }})">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>التاريخ</label>
                        <input type="text" class="form-control bg-light" readonly
                            value="{{ $attendance->attendance_date->format('Y-m-d') }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>
                            وقت الحضور
                            @if($attendance->missing_punch === 'in')
                                <span class="badge badge-danger">مفقود</span>
                            @endif
                        </label>
                        <input type="time" name="check_in_time" id="checkInTime" class="form-control
                            {{ $attendance->missing_punch === 'in' ? 'border-danger' : '' }}"
                            value="{{ old('check_in_time', $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') : '') }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>
                            وقت الانصراف
                            @if($attendance->missing_punch === 'out')
                                <span class="badge badge-danger">مفقود</span>
                            @endif
                        </label>
                        <input type="time" name="check_out_time" id="checkOutTime" class="form-control
                            {{ $attendance->missing_punch === 'out' ? 'border-danger' : '' }}"
                            value="{{ old('check_out_time', $attendance->check_out_time ? \Carbon\Carbon::parse($attendance->check_out_time)->format('H:i') : '') }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>الحالة <span class="text-danger">*</span></label>
                        <select name="status" id="statusSelect" class="form-control" required
                                onchange="handleStatusChange(this.value)">
                            <option value="1" {{ $attendance->status==1?'selected':'' }}>حضر</option>
                            <option value="2" {{ $attendance->status==2?'selected':'' }}>غياب</option>
                            <option value="3" {{ $attendance->status==3?'selected':'' }}>إجازة</option>
                            <option value="4" {{ $attendance->status==4?'selected':'' }}>إجازة رسمية</option>
                            <option value="5" {{ $attendance->status==5?'selected':'' }}>مأمورية</option>
                            <option value="6" {{ $attendance->status==6?'selected':'' }}>إجازة أسبوعية</option>
                        </select>
                    </div>
                </div>

                {{-- ─── قسم خصم الغياب ─── --}}
                <div id="absenceDeductionSection" style="display:{{ $attendance->status==2 ? 'block' : 'none' }}">
                    <hr>
                    <h6 class="text-danger"><i class="fas fa-minus-circle ml-1"></i>خصم الغياب</h6>
                    <div class="row">
                        <div class="col-md-5 form-group">
                            <label>عدد أيام الخصم <span class="text-muted">(افتراضي من الضبط العام)</span></label>
                            <div class="input-group">
                                <input type="number"
                                       name="absence_deduction_days"
                                       id="absenceDeductionDays"
                                       class="form-control"
                                       min="0" max="30" step="0.5"
                                       value="{{ old('absence_deduction_days', $attendance->absence_deduction_days ?? ($settings?->sanctions_value_first_abcence ?? 1)) }}">
                                <div class="input-group-append">
                                    <span class="input-group-text">يوم</span>
                                </div>
                            </div>
                            <small class="text-muted">
                                القيمة الافتراضية من الضبط العام:
                                <strong>{{ $settings?->sanctions_value_first_abcence ?? 1 }} يوم</strong>
                                — يمكن تعديلها لهذا السجل فقط.
                            </small>
                        </div>
                        <div class="col-md-4 d-flex align-items-end form-group">
                            @php
                                $rates = null;
                                if ($settings && $attendance->employee?->emp_sal) {
                                    $dayDiv = match((int)($settings->day_rate_divisor_type ?? 1)) {
                                        2 => 30,
                                        3 => $attendance->attendance_date->daysInMonth,
                                        4 => max(1, (float)($settings->day_rate_divisor_custom ?? 26)),
                                        default => 26,
                                    };
                                    $dailyRate = $attendance->employee->emp_sal / max(1, $dayDiv);
                                    $deductDays = $attendance->absence_deduction_days ?? ($settings->sanctions_value_first_abcence ?? 1);
                                    $deductAmount = round($dailyRate * $deductDays, 2);
                                }
                            @endphp
                            @if(isset($deductAmount))
                            <div class="alert alert-danger py-2 mb-0 w-100" style="font-size:.88rem">
                                <i class="fas fa-coins ml-1"></i>
                                قيمة الخصم المتوقعة:
                                <strong>{{ number_format($deductAmount, 2) }} ج.م</strong>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ─── قسم الإجازة الأسبوعية ─── --}}
                <div id="weeklyLeaveSection" style="display:{{ $attendance->status==6 ? 'block' : 'none' }}">
                    <div class="alert" style="background:#f3eeff;border:1px solid #6f42c1;border-radius:6px">
                        <i class="fas fa-calendar-week ml-1" style="color:#6f42c1"></i>
                        <strong>إجازة أسبوعية بحتة</strong> — لم يبصم الموظف في هذا اليوم.
                        لا تأخير ولا خصومات ولا بدل إجازة.
                    </div>
                </div>

                {{-- ─── بدل الإجازة (يوم راحة عمل فيه) ─── --}}
                @if($attendance->status == 1)
                <div id="weeklyOffWorkedSection" style="display:{{ $attendance->is_weekly_off_worked ? 'block' : 'none' }}">
                    <div class="alert alert-success" style="border-radius:6px">
                        <i class="fas fa-umbrella-beach ml-1"></i>
                        <strong>يوم راحة عمل فيه</strong> —
                        يستحق الموظف بدل إجازة.
                        يمكن إلغاؤه بإلغاء التحديد أدناه.
                    </div>
                    <div class="row">
                        <div class="col-md-5 form-group">
                            <label>
                                <input type="checkbox" name="is_weekly_off_worked" value="1"
                                    id="weeklyOffWorkedChk"
                                    {{ $attendance->is_weekly_off_worked ? 'checked' : '' }}>
                                احتساب بدل الإجازة لهذا اليوم
                            </label>
                            <div class="mt-1">
                                <span class="badge badge-success" style="font-size:1em">
                                    بدل الإجازة: {{ number_format($attendance->leave_compensation_amount ?? 0, 2) }} ج.م
                                </span>
                            </div>
                            <small class="text-muted">
                                المبلغ يُحتسب تلقائياً من إعدادات بدل الإجازة عند الحفظ
                            </small>
                        </div>
                    </div>
                </div>
                @endif

                {{-- ─── قسم الإذن ─── --}}
                <div id="permissionSection" style="display:{{ $attendance->status==1 ? 'block' : 'none' }}">
                    <hr>
                    <h6 class="text-info"><i class="fas fa-id-card ml-1"></i>الإذونات (تُخصم من التأخير/الانصراف المبكر)</h6>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>دقائق إذن التأخير</label>
                            <div class="input-group">
                                <input type="number" name="permission_minutes" class="form-control"
                                    min="0" step="1"
                                    value="{{ old('permission_minutes', $attendance->permission_minutes ?? 0) }}">
                                <div class="input-group-append"><span class="input-group-text">د</span></div>
                            </div>
                            <small class="text-muted">
                                يُطرح من دقائق التأخير قبل الاحتساب
                                @if(isset($settings) && $settings->max_permission_minutes_per_day > 0)
                                    — الحد الأقصى: {{ $settings->max_permission_minutes_per_day }} د/يوم
                                @endif
                            </small>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>دقائق إذن الانصراف المبكر</label>
                            <div class="input-group">
                                <input type="number" name="permission_early_minutes" class="form-control"
                                    min="0" step="1"
                                    value="{{ old('permission_early_minutes', $attendance->permission_early_minutes ?? 0) }}">
                                <div class="input-group-append"><span class="input-group-text">د</span></div>
                            </div>
                            <small class="text-muted">يُطرح من دقائق الانصراف المبكر</small>
                        </div>
                    </div>
                </div>

                {{-- ─── قفل السجل من معالجة البصمة ─── --}}
                <div class="form-group mt-3">
                    <div class="custom-control custom-switch">
                        <input type="hidden" name="is_manual_lock" value="0">
                        <input type="checkbox"
                               class="custom-control-input"
                               id="isManualLock"
                               name="is_manual_lock"
                               value="1"
                               {{ $attendance->is_manual_lock ? 'checked' : '' }}>
                        <label class="custom-control-label font-weight-bold" for="isManualLock">
                            <i class="fas fa-lock ml-1 text-danger"></i>
                            تثبيت هذا السجل — لا تتأثر بأي معالجة بصمة لاحقة
                        </label>
                    </div>
                    <small class="text-muted d-block mt-1 mr-4">
                        عند التفعيل: لن تؤثر عليه مزامنة الأجهزة، إعادة المعالجة الجماعية، أو تفريغ البصمة.
                        يظل نافذاً حتى تزيل العلامة يدوياً.
                    </small>
                    @if($attendance->is_manual_lock)
                    <div class="alert alert-warning py-2 mt-2 mb-0" style="font-size:.85rem">
                        <i class="fas fa-lock ml-1"></i>
                        <strong>هذا السجل مثبَّت</strong> — محمي من جميع عمليات معالجة البصمة التلقائية.
                    </div>
                    @endif
                </div>

                <div class="form-group">
                    <label>ملاحظات</label>
                    <input type="text" name="notes" class="form-control"
                        value="{{ old('notes', $attendance->notes) }}">
                </div>

                {{-- ─── القيم المحتسبة الحالية ─── --}}
                <div class="row mt-2">
                    <div class="col-md-3">
                        <div class="info-box bg-warning">
                            <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">تأخير</span>
                                <span class="info-box-number">{{ $attendance->late_display }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-orange">
                            <span class="info-box-icon"><i class="fas fa-sign-out-alt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">انصراف مبكر</span>
                                <span class="info-box-number">{{ $attendance->early_departure_display }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-success">
                            <span class="info-box-icon"><i class="fas fa-plus"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">أوفرتايم</span>
                                <span class="info-box-number">{{ $attendance->overtime_hours }} س</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-danger">
                            <span class="info-box-icon"><i class="fas fa-minus"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">خصم تأخير + مبكر</span>
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
                                <span class="info-box-text">قيمة أوفرتايم</span>
                                <span class="info-box-number">{{ number_format($attendance->overtime_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    @if(($attendance->permission_minutes ?? 0) > 0 || ($attendance->permission_early_minutes ?? 0) > 0)
                    <div class="col-md-3">
                        <div class="info-box bg-teal">
                            <span class="info-box-icon"><i class="fas fa-id-card"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">إذن مُعتمد</span>
                                <span class="info-box-number">
                                    {{ ($attendance->permission_minutes ?? 0) + ($attendance->permission_early_minutes ?? 0) }} د
                                </span>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                <small class="text-muted">* سيتم إعادة احتساب التأخير والأوفرتايم والانصراف المبكر تلقائياً بعد الحفظ</small>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save ml-1"></i> حفظ التعديلات
                </button>
                <a href="{{ $backUrl }}" class="btn btn-secondary mr-2">
                    <i class="fas fa-arrow-right ml-1"></i> رجوع
                </a>
            </div>
        </form>
    </div>

    {{-- ─── بطاقة تغيير الشيفت المخصص ─── --}}
    <div class="card card-info mt-3">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-exchange-alt ml-2"></i>تغيير الشيفت لهذا السجل فقط
            </h3>
        </div>
        <form id="shiftForm" action="{{ route('attendance.update_shift', $attendance->id) }}" method="POST">
            @csrf
            <div class="card-body">
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                <p class="text-muted small">
                    الشيفت الأصلي للموظف: <strong>{{ $attendance->shift->type ?? '—' }}</strong>.
                    يمكنك تعيين شيفت مختلف لهذا اليوم فقط.
                </p>
                <div class="form-group mb-0">
                    <label>الشيفت المخصص لهذا اليوم</label>
                    <select name="shift_override_id" id="shiftOverrideSelect" class="form-control">
                        <option value="">-- استخدام الشيفت الأصلي ({{ $attendance->shift->type ?? 'غير محدد' }}) --</option>
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
            <div class="card-footer d-flex flex-wrap gap-2" style="gap:.5rem">
                {{-- زر 1: تطبيق الشيفت فقط وإعادة الاحتساب (يحتفظ بأوقات البصمة الحالية) --}}
                <button type="submit" class="btn btn-info">
                    <i class="fas fa-calculator ml-1"></i> تطبيق الشيفت وإعادة الاحتساب
                </button>

                {{-- زر 2: تطبيق الشيفت وإعادة معالجة البصمة من السجلات الخام --}}
                <button type="submit"
                        formaction="{{ route('attendance.reprocess_fingerprint', $attendance->id) }}"
                        class="btn btn-warning"
                        onclick="return confirm('سيتم إعادة تحديد أوقات الحضور والانصراف من سجلات البصمة الخام باستخدام الشيفت الجديد.\nهل تريد المتابعة؟')">
                    <i class="fas fa-fingerprint ml-1"></i> تطبيق الشيفت وإعادة معالجة البصمة
                </button>
            </div>
        </form>
    </div>

    {{-- وصف الفرق بين الزرين --}}
    <div class="alert alert-light border mt-n2 mb-3" style="border-radius:0 0 6px 6px;font-size:.85em">
        <i class="fas fa-info-circle text-info ml-1"></i>
        <strong>الفرق بين الزرين:</strong>
        <ul class="mb-0 mt-1">
            <li><strong>إعادة الاحتساب فقط:</strong> يُبقي على أوقات الحضور/الانصراف المُسجَّلة ويُعيد حساب التأخير والأوفرتايم بالشيفت الجديد.</li>
            <li><strong>إعادة معالجة البصمة:</strong> يرجع لسجلات البصمة الخام ويُعيد تحديد أوقات الحضور/الانصراف حسب نافذة الشيفت الجديد (ضروري عند التبديل بين شيفت صباحي وليلي).</li>
        </ul>
    </div>

    {{-- ─── بطاقة البصمات الخام ─── --}}
    <div class="card mt-3" style="border:2px solid #6c757d">
        <div class="card-header" style="background:#f8f9fa">
            <h3 class="card-title">
                <i class="fas fa-fingerprint ml-2 text-secondary"></i>
                <strong>سجلات البصمة الخام</strong>
                <span class="text-muted">— {{ $date }}</span>
                <span class="text-muted"> و </span>
                <span class="text-warning">{{ $nextDate }}</span>
                <span class="badge badge-{{ $fingerLogs->count() ? 'info' : 'secondary' }} mr-2" style="font-size:.9rem">
                    {{ $fingerLogs->count() }} بصمة
                </span>
            </h3>
        </div>
        <div class="card-body p-0">
            @if($fingerLogs->isEmpty())
                <div class="p-4 text-muted text-center">
                    @if(!($attendance->employee->finger_id ?? null))
                        <i class="fas fa-exclamation-circle fa-2x mb-2 d-block"></i>
                        الموظف ليس لديه رقم بصمة مسجّل في ملفه.
                    @else
                        <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
                        لا توجد سجلات بصمة لهذا الموظف
                        (رقم البصمة: <strong>{{ $attendance->employee->finger_id }}</strong>)
                        في يومَي <strong>{{ $date }}</strong> و <strong>{{ $nextDate }}</strong>.
                    @endif
                </div>
            @else
                <table class="table table-sm table-hover table-bordered mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th width="40">#</th>
                            <th>وقت البصمة</th>
                            <th>اليوم</th>
                            <th>الجهاز</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fingerLogs as $i => $log)
                        <tr class="{{ $log->punch_time->format('Y-m-d') === $date ? '' : 'table-warning' }}">
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>
                                <strong class="text-dark">{{ $log->punch_time->format('H:i:s') }}</strong>
                                <small class="text-muted mr-2">{{ $log->punch_time->format('Y-m-d') }}</small>
                            </td>
                            <td>
                                @if($log->punch_time->format('Y-m-d') === $date)
                                    <span class="badge badge-primary">يوم الحضور</span>
                                @else
                                    <span class="badge badge-warning text-dark">اليوم التالي</span>
                                @endif
                            </td>
                            <td>{{ $log->device?->device_name ?? '—' }}</td>
                            <td>
                                @if($log->is_processed)
                                    <span class="badge badge-success">مُعالَجة</span>
                                @else
                                    <span class="badge badge-secondary">لم تُعالَج</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-3 py-2 text-muted" style="font-size:.82rem;background:#f8f9fa">
                    <i class="fas fa-info-circle ml-1"></i>
                    الصفوف الصفراء = اليوم التالي (للشيفت الليلي).
                    &nbsp;|&nbsp; أول بصمة = وقت الحضور &nbsp;|&nbsp; آخر بصمة = وقت الانصراف.
                </div>
            @endif
        </div>
    </div>

    {{-- ─── بطاقة معالجة البصمة الناقصة ─── --}}
    @if($attendance->missing_punch)
    <div class="card card-danger mt-3">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-fingerprint ml-2"></i>
                معالجة البصمة الناقصة
                ({{ $attendance->missing_punch === 'out' ? 'انصراف مفقود' : 'حضور مفقود' }})
            </h3>
        </div>
        <div class="card-body">
            @if($attendance->missing_punch_resolution)
            <div class="alert alert-success">
                <i class="fas fa-check-circle ml-1"></i>
                تم الحل مسبقاً: <strong>{{ $attendance->missing_punch_resolution_label }}</strong>
            </div>
            @endif

            <form action="{{ route('attendance.resolve_missing', $attendance->id) }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-5 form-group">
                        <label>طريقة المعالجة <span class="text-danger">*</span></label>
                        <select name="missing_punch_resolution" id="resolutionSelect" class="form-control" required>
                            <option value="">-- اختر --</option>
                            <option value="1" {{ $attendance->missing_punch_resolution==1?'selected':'' }}>خصم ربع يوم</option>
                            <option value="2" {{ $attendance->missing_punch_resolution==2?'selected':'' }}>خصم نصف يوم</option>
                            <option value="3" {{ $attendance->missing_punch_resolution==3?'selected':'' }}>خصم يوم كامل</option>
                            <option value="4" {{ $attendance->missing_punch_resolution==4?'selected':'' }}>نسيان (بدون خصم)</option>
                            <option value="5" {{ $attendance->missing_punch_resolution==5?'selected':'' }}>إذن (تحديد عدد الساعات)</option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group" id="hoursField" style="display:none;">
                        <label>عدد ساعات الإذن <span class="text-danger">*</span></label>
                        <input type="number" name="missing_punch_hours" class="form-control"
                            min="0.25" max="24" step="0.25"
                            value="{{ $attendance->missing_punch_hours }}">
                    </div>
                    <div class="col-md-4 form-group d-flex align-items-end">
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-check ml-1"></i> تطبيق القرار
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
    var weeklySection         = document.getElementById('weeklyLeaveSection');
    var permissionSection     = document.getElementById('permissionSection');
    var absenceSection        = document.getElementById('absenceDeductionSection');

    weeklySection.style.display         = (val == '6') ? 'block' : 'none';
    permissionSection.style.display     = (val == '1') ? 'block' : 'none';
    absenceSection.style.display        = (val == '2') ? 'block' : 'none';
}

document.getElementById('resolutionSelect')?.addEventListener('change', function () {
    document.getElementById('hoursField').style.display = this.value === '5' ? 'flex' : 'none';
});

if (document.getElementById('resolutionSelect')?.value === '5') {
    document.getElementById('hoursField').style.display = 'flex';
}

// تهيئة أولية
handleStatusChange(document.getElementById('statusSelect').value);
</script>
@endsection
