@extends('admin.layouts.admin')
@section('title') الحضور والانصراف @endsection
@section('start') شئون الموظفين @endsection
@section('home') <a href="{{ route('attendance.index') }}">الحضور والانصراف</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header d-flex align-items-center flex-wrap" style="gap:.4rem">
            <h3 class="card-title mb-0 ml-auto">
                <i class="fas fa-fingerprint ml-1"></i>
                سجلات الحضور والانصراف
            </h3>
            <div class="d-flex flex-wrap mr-auto" style="gap:.4rem">
                <a class="btn btn-sm btn-success" href="{{ route('attendance.create') }}">
                    <i class="fas fa-plus ml-1"></i> إضافة سجل
                </a>
                <a class="btn btn-sm btn-info" href="{{ route('attendance.bulk_create') }}">
                    <i class="fas fa-list ml-1"></i> إدخال دفعي
                </a>
                <a class="btn btn-sm btn-purple" href="{{ route('attendance.generate_weekly_leaves_form') }}"
                   style="background:#6f42c1;color:#fff;border-color:#6f42c1">
                    <i class="fas fa-calendar-week ml-1"></i> توليد إجازات أسبوعية
                </a>
                <button type="button" class="btn btn-sm btn-danger" id="btnBulkDelete"
                        data-employees='@json($employees->pluck("employee_name_A","id"))'>
                    <i class="fas fa-trash-alt ml-1"></i> حذف جماعي
                </button>
            </div>
        </div>

        {{-- فلاتر البحث --}}
        <div class="card-body border-bottom pb-3">
            <form method="GET" action="{{ route('attendance.index') }}">
                <div class="row align-items-end">
                    <div class="col-md-3 col-sm-6 form-group mb-2">
                        <label class="mb-1 font-weight-bold text-muted" style="font-size:.82rem">
                            <i class="fas fa-user ml-1"></i>الموظف
                        </label>
                        <select name="employee_id" class="form-control select2">
                            <option value="">-- جميع الموظفين --</option>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ request('employee_id')==$emp->id ? 'selected':'' }}>
                                {{ $emp->employee_name_A }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6 form-group mb-2">
                        <label class="mb-1 font-weight-bold text-muted" style="font-size:.82rem">
                            <i class="fas fa-calendar-alt ml-1"></i>من تاريخ
                        </label>
                        <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-2 col-sm-6 form-group mb-2">
                        <label class="mb-1 font-weight-bold text-muted" style="font-size:.82rem">
                            <i class="fas fa-calendar-alt ml-1"></i>إلى تاريخ
                        </label>
                        <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                    </div>
                    <div class="col-md-2 col-sm-6 form-group mb-2">
                        <label class="mb-1 font-weight-bold text-muted" style="font-size:.82rem">
                            <i class="fas fa-filter ml-1"></i>الحالة
                        </label>
                        <select name="status" class="form-control">
                            <option value="">-- جميع الحالات --</option>
                            <option value="1" {{ request('status')==1?'selected':'' }}>حضر</option>
                            <option value="2" {{ request('status')==2?'selected':'' }}>غياب</option>
                            <option value="3" {{ request('status')==3?'selected':'' }}>إجازة</option>
                            <option value="4" {{ request('status')==4?'selected':'' }}>إجازة رسمية</option>
                            <option value="5" {{ request('status')==5?'selected':'' }}>مأمورية</option>
                            <option value="6" {{ request('status')==6?'selected':'' }}>إجازة أسبوعية</option>
                        </select>
                    </div>
                    <div class="col-md-1 col-sm-4 form-group mb-2">
                        <label class="mb-1 font-weight-bold text-muted" style="font-size:.82rem">
                            <i class="fas fa-list-ol ml-1"></i>سجلات/صفحة
                        </label>
                        <select name="per_page" class="form-control">
                            @foreach([10, 20, 50, 100] as $n)
                                <option value="{{ $n }}" {{ request('per_page', 20) == $n ? 'selected' : '' }}>{{ $n }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-8 form-group mb-2">
                        <label class="mb-1 d-block" style="font-size:.82rem;visibility:hidden">.</label>
                        <div class="d-flex" style="gap:.5rem">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-search ml-1"></i> بحث
                            </button>
                            <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary flex-fill">
                                <i class="fas fa-times ml-1"></i> إلغاء
                            </a>
                        </div>
                    </div>
                </div>
                {{-- شارات الفلاتر النشطة --}}
                @if(request()->hasAny(['employee_id','from_date','to_date','status']))
                <div class="mt-1 mb-0">
                    <small class="text-muted ml-2">فلاتر نشطة:</small>
                    @if(request('employee_id'))
                        @php $empName = $employees->find(request('employee_id'))?->employee_name_A; @endphp
                        <span class="badge badge-primary">{{ $empName }}</span>
                    @endif
                    @if(request('from_date'))
                        <span class="badge badge-info">من: {{ request('from_date') }}</span>
                    @endif
                    @if(request('to_date'))
                        <span class="badge badge-info">إلى: {{ request('to_date') }}</span>
                    @endif
                    @if(request('status'))
                        @php $statusNames = [1=>'حضر',2=>'غياب',3=>'إجازة',4=>'إجازة رسمية',5=>'مأمورية',6=>'إجازة أسبوعية']; @endphp
                        <span class="badge badge-warning">{{ $statusNames[request('status')] ?? '' }}</span>
                    @endif
                </div>
                @endif
            </form>

            {{-- أزرار معالجة البصمة --}}
            <div class="d-flex flex-wrap align-items-start mt-2" style="gap:.5rem">
                {{-- زر تفريغ البصمة --}}
                <form method="POST"
                      action="{{ route('attendance.void_fingerprint') }}"
                      onsubmit="return confirmVoidFingerprint(this)">
                    @csrf
                    <input type="hidden" name="employee_id" value="{{ request('employee_id') }}">
                    <input type="hidden" name="from_date"   value="{{ request('from_date') }}">
                    <input type="hidden" name="to_date"     value="{{ request('to_date') }}">
                    <input type="hidden" name="status"      value="{{ request('status') }}">
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="fas fa-eraser ml-1"></i> تفريغ البصمة وتحويلها غياب
                        @if(request()->hasAny(['employee_id','from_date','to_date','status']))
                            <span class="badge badge-light text-danger">حسب الفلتر الحالي</span>
                        @else
                            <span class="badge badge-light text-danger">كل السجلات</span>
                        @endif
                    </button>
                </form>

                {{-- زر إعادة معالجة البصمة --}}
                <form method="POST"
                      action="{{ route('attendance.bulk_reprocess_fingerprint') }}"
                      onsubmit="return confirmBulkReprocess(this)">
                    @csrf
                    <input type="hidden" name="employee_id" value="{{ request('employee_id') }}">
                    <input type="hidden" name="from_date"   value="{{ request('from_date') }}">
                    <input type="hidden" name="to_date"     value="{{ request('to_date') }}">
                    <input type="hidden" name="status"      value="{{ request('status') }}">
                    <button type="submit" class="btn btn-sm btn-warning">
                        <i class="fas fa-sync-alt ml-1"></i> إعادة معالجة البصمة
                        @if(request()->hasAny(['employee_id','from_date','to_date','status']))
                            <span class="badge badge-light text-warning">حسب الفلتر الحالي</span>
                        @else
                            <span class="badge badge-light text-warning">كل السجلات</span>
                        @endif
                    </button>
                </form>
            </div>
            <small class="text-muted d-block mt-1">
                <i class="fas fa-info-circle ml-1"></i>
                <strong>تفريغ:</strong> يحوّل إلى غياب ويعيد البصمات لغير معالَجة. &nbsp;
                <strong>إعادة معالجة:</strong> يُعيد احتساب الحضور من سجلات البصمة المخزنة مع مراعاة الشيفت المخصص لكل يوم.
            </small>
        </div>

        {{-- مودال تأكيد الحذف الجماعي --}}
        <div class="modal fade" id="bulkDeleteModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title w-100 text-center">
                            <i class="fas fa-exclamation-triangle ml-2"></i>
                            تحذير: حذف سجلات الحضور
                        </h5>
                    </div>
                    <div class="modal-body pb-1">
                        <div class="alert alert-danger mb-3">
                            <i class="fas fa-exclamation-circle ml-1"></i>
                            هذا الإجراء <strong>لا يمكن التراجع عنه</strong>. سيتم حذف جميع السجلات المطابقة للفلاتر المحددة نهائياً.
                        </div>
                        <table class="table table-sm table-borderless mb-0" id="bulkDeleteSummary"></table>
                    </div>
                    <div class="modal-footer">
                        <form id="bulkDeleteForm" method="POST" action="{{ route('attendance.bulk_delete') }}">
                            @csrf
                            <input type="hidden" name="employee_id" id="bd_employee_id">
                            <input type="hidden" name="from_date"   id="bd_from_date">
                            <input type="hidden" name="to_date"     id="bd_to_date">
                            <input type="hidden" name="status"      id="bd_status">
                            <button type="button" class="btn btn-secondary ml-2" data-dismiss="modal">
                                <i class="fas fa-times ml-1"></i> إلغاء
                            </button>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash-alt ml-1"></i> تأكيد الحذف
                            </button>
                        </form>
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
                <table class="table table-bordered table-hover table-sm" style="font-size:.78rem;white-space:nowrap">
                    <thead class="thead-dark">
                        <tr style="font-size:.77rem">
                            <th style="width:82px">التاريخ</th>
                            <th style="width:110px">الموظف</th>
                            <th style="width:75px">الشيفت</th>
                            <th style="width:52px">حضور</th>
                            <th style="width:52px">انصراف</th>
                            <th style="width:95px">الحالة</th>
                            <th style="width:52px" title="دقائق التأخير">تأخير</th>
                            <th style="width:52px" title="الانصراف المبكر">مبكر</th>
                            <th style="width:50px" title="أوفرتايم (ساعات)">OT(س)</th>
                            <th style="width:58px" title="خصم التأخير">خ.تأخير</th>
                            <th style="width:55px" title="خصم الانصراف المبكر">خ.مبكر</th>
                            <th style="width:58px" title="قيمة الأوفرتايم">ق.OT</th>
                            <th style="width:52px" title="بدل الإجازة">بدل</th>
                            <th style="width:80px">ملاحظات</th>
                            <th style="width:55px">إجراء</th>
                        </tr>
                    </thead>
                    @php
                        $dayNames = [
                            'Saturday'  => 'السبت',
                            'Sunday'    => 'الأحد',
                            'Monday'    => 'الاثنين',
                            'Tuesday'   => 'الثلاثاء',
                            'Wednesday' => 'الأربعاء',
                            'Thursday'  => 'الخميس',
                            'Friday'    => 'الجمعة',
                        ];
                        $weekendDays = ['Saturday', 'Sunday', 'Friday'];
                    @endphp
                    <tbody>
                        @forelse($data as $rec)
                        @php
                            $dayEn        = $rec->attendance_date->format('l');
                            $dayAr        = $dayNames[$dayEn] ?? $dayEn;
                            $isWeekend    = in_array($dayEn, $weekendDays);
                            $isBeforeHire = $rec->is_before_hire
                                || (
                                    ($rec->employee->emp_start_date ?? null)
                                    && $rec->attendance_date->format('Y-m-d') < $rec->employee->emp_start_date
                                );
                        @endphp
                        <tr class="{{ $isBeforeHire ? 'table-secondary' : ($isWeekend ? 'table-light' : '') }}">
                            <td style="white-space:normal">
                                <span style="font-size:.77rem">{{ $rec->attendance_date->format('Y-m-d') }}</span>
                                <small class="d-block font-weight-bold
                                    {{ $isWeekend ? 'text-danger' : 'text-primary' }}"
                                    style="font-size:.72rem">
                                    {{ $dayAr }}
                                </small>
                                @if($isBeforeHire)
                                    <span class="badge badge-secondary" style="font-size:.65rem">قبل التعيين</span>
                                @endif
                            </td>
                            <td style="max-width:110px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                                title="{{ $rec->employee->employee_name_A ?? '' }}">
                                {{ $rec->employee->employee_name_A ?? '-' }}
                            </td>
                            <td style="white-space:normal">
                                @php $effShift = $rec->effective_shift; @endphp
                                @if($effShift)
                                    <span style="font-size:.75rem">{{ $effShift->type }}</span>
                                    <small class="text-muted d-block" style="font-size:.7rem">{{ $effShift->from_time }}-{{ $effShift->to_time }}</small>
                                    @if($rec->shift_override_id)
                                        <span class="badge badge-warning" style="font-size:.65rem">مخصص</span>
                                    @endif
                                @else - @endif
                            </td>
                            <td>
                                {{ $rec->check_in_time ?? '-' }}
                                @if($rec->check_in_lat && $rec->check_in_lng)
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ $rec->check_in_lat }},{{ $rec->check_in_lng }}"
                                       target="_blank" rel="noopener" title="عرض موقع الحضور على الخريطة">
                                        <i class="fas fa-map-marker-alt text-danger" style="font-size:.8rem"></i>
                                    </a>
                                @endif
                            </td>
                            <td>
                                {{ $rec->check_out_time ?? '-' }}
                                @if($rec->check_out_lat && $rec->check_out_lng)
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ $rec->check_out_lat }},{{ $rec->check_out_lng }}"
                                       target="_blank" rel="noopener" title="عرض موقع الانصراف على الخريطة">
                                        <i class="fas fa-map-marker-alt text-danger" style="font-size:.8rem"></i>
                                    </a>
                                @endif
                            </td>
                            <td style="white-space:normal">
                                {!! $rec->status_label !!}
                                @if($rec->status == 2 && !$isBeforeHire)
                                    @php
                                        $absDays = $rec->absence_deduction_days
                                                   ?? ($settings?->sanctions_value_first_abcence ?? 1);
                                    @endphp
                                    <br>
                                    <span class="badge badge-dark" style="font-size:.68rem"
                                          title="أيام الخصم المطبقة على هذا الغياب">
                                        خصم {{ number_format($absDays, 0) }} يوم
                                    </span>
                                @endif
                                @if($rec->missing_punch)
                                    <br>
                                    @if($rec->missing_punch_resolution)
                                        <span class="badge badge-success" style="font-size:.68rem">{{ $rec->missing_punch_resolution_label }}</span>
                                    @else
                                        <span class="badge badge-danger" style="font-size:.68rem">لم يتخذ إجراء</span>
                                    @endif
                                @endif
                                @if($rec->is_manual_lock)
                                    <br><span class="badge badge-danger" style="font-size:.65rem" title="مثبَّت — محمي من معالجة البصمة"><i class="fas fa-lock"></i> مثبَّت</span>
                                @endif
                            </td>
                            {{-- التأخير --}}
                            <td>
                                @if($isBeforeHire)
                                    <span class="text-muted">—</span>
                                @elseif($rec->late_minutes > 0 || $rec->late_fraction)
                                    <span class="text-danger font-weight-bold">{{ $rec->late_display }}</span>
                                    @if($rec->permission_minutes > 0)
                                        <br><small class="text-success">إذن: {{ $rec->permission_minutes }} د</small>
                                    @endif
                                @else
                                    <span class="text-success">—</span>
                                @endif
                            </td>
                            {{-- الانصراف المبكر --}}
                            <td>
                                @if($isBeforeHire)
                                    <span class="text-muted">—</span>
                                @elseif($rec->early_departure_minutes > 0)
                                    @php $earlyFrac = $rec->early_departure_fraction ?? null; @endphp
                                    <span class="font-weight-bold {{ $earlyFrac >= 3 ? 'text-danger' : 'text-warning' }}">
                                        {{ $rec->early_departure_display }}
                                    </span>
                                    @if($earlyFrac == 4)
                                        <br><small class="text-danger">⚠ عدم إتمام اليوم</small>
                                    @endif
                                    @if($rec->permission_early_minutes > 0)
                                        <br><small class="text-success">إذن: {{ $rec->permission_early_minutes }} د</small>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            {{-- أوفرتايم --}}
                            <td>
                                @if($isBeforeHire)
                                    <span class="text-muted">—</span>
                                @elseif($rec->overtime_hours > 0)
                                    <span class="text-success">{{ $rec->overtime_hours }}</span>
                                @else
                                    0
                                @endif
                            </td>
                            {{-- الخصومات والأوفرتايم المالي --}}
                            <td class="{{ $isBeforeHire ? 'text-muted' : 'text-danger' }}">
                                {{ $isBeforeHire ? '—' : number_format($rec->late_deduction, 2) }}
                            </td>
                            <td class="{{ $isBeforeHire ? 'text-muted' : 'text-warning' }}">
                                {{ $isBeforeHire ? '—' : number_format($rec->early_departure_deduction ?? 0, 2) }}</td>
                            <td class="{{ $isBeforeHire ? 'text-muted' : 'text-success' }}">
                                {{ $isBeforeHire ? '—' : number_format($rec->overtime_amount, 2) }}
                            </td>
                            <td class="text-center" style="padding:3px">
                                @if(!$isBeforeHire && $rec->status == 1)
                                    <form method="POST"
                                          action="{{ route('attendance.toggle_weekly_off', $rec->id) }}"
                                          style="display:inline">
                                        @csrf
                                        <input type="hidden" name="_back_url" value="{{ request()->fullUrl() }}">
                                        @if($rec->is_weekly_off_worked)
                                            <button type="submit"
                                                    class="btn btn-xs btn-success"
                                                    title="بدل إجازة مفعّل: {{ number_format($rec->leave_compensation_amount ?? 0, 2) }} ج.م — اضغط للإلغاء"
                                                    onclick="return confirm('إلغاء بدل الإجازة الأسبوعية؟')">
                                                <i class="fas fa-umbrella-beach"></i>
                                                <span style="font-size:.65rem">{{ number_format($rec->leave_compensation_amount ?? 0, 2) }}</span>
                                            </button>
                                        @else
                                            <button type="submit"
                                                    class="btn btn-xs btn-outline-secondary"
                                                    title="تفعيل بدل الإجازة الأسبوعية لهذا اليوم"
                                                    onclick="return confirm('تفعيل بدل الإجازة الأسبوعية؟')">
                                                <i class="fas fa-umbrella-beach"></i>
                                            </button>
                                        @endif
                                    </form>
                                @elseif(!$isBeforeHire && $rec->is_weekly_off_worked && ($rec->leave_compensation_amount ?? 0) > 0)
                                    <span class="text-success font-weight-bold" style="font-size:.8rem">
                                        {{ number_format($rec->leave_compensation_amount, 2) }}
                                    </span>
                                    <br><small class="badge badge-success" style="font-size:.65rem">يوم راحة</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td style="max-width:80px">
                                @if($rec->notes)
                                    <span class="text-muted"
                                          title="{{ $rec->notes }}"
                                          data-toggle="tooltip"
                                          style="display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:78px;cursor:help;font-size:.73rem">
                                        <i class="fas fa-comment-alt fa-xs ml-1 text-info"></i>{{ $rec->notes }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('attendance.edit', $rec->id) . '?back=' . urlencode(request()->fullUrl()) }}"
                                   class="btn btn-xs btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('attendance.delete', $rec->id) }}"
                                   class="btn btn-xs btn-danger"
                                   onclick="return confirm('هل تريد حذف هذا السجل؟')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="14" class="text-center">لا توجد سجلات</td></tr>
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

    var empName = employeeId ? (employees[employeeId] || '—') : 'جميع الموظفين';
    var rows = [
        ['الموظف',    empName],
        ['من تاريخ',  fromDate || 'غير محدد'],
        ['إلى تاريخ', toDate   || 'غير محدد'],
        ['الحالة',    statusVal ? statusText : 'جميع الحالات'],
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

function confirmBulkReprocess(form) {
    var employees = JSON.parse(document.getElementById('btnBulkDelete').dataset.employees || '{}');
    var empId     = form.querySelector('[name="employee_id"]').value;
    var fromDate  = form.querySelector('[name="from_date"]').value;
    var toDate    = form.querySelector('[name="to_date"]').value;
    var empName   = empId ? (employees[empId] || '—') : 'جميع الموظفين';

    var desc = '';
    if (empName !== 'جميع الموظفين') desc += 'الموظف: ' + empName + '\n';
    if (fromDate) desc += 'من: ' + fromDate + '\n';
    if (toDate)   desc += 'إلى: ' + toDate + '\n';
    if (!desc)    desc  = 'كل السجلات (بدون فلتر)\n';

    return confirm(
        'تأكيد إعادة معالجة البصمة\n\n' +
        desc + '\n' +
        'سيتم إعادة احتساب الحضور من سجلات البصمة المخزنة\nمع مراعاة الشيفت المخصص لكل يوم.\n\n' +
        'هل أنت متأكد؟'
    );
}

function confirmVoidFingerprint(form) {
    var employees  = JSON.parse(document.getElementById('btnBulkDelete').dataset.employees || '{}');
    var empId      = form.querySelector('[name="employee_id"]').value;
    var fromDate   = form.querySelector('[name="from_date"]').value;
    var toDate     = form.querySelector('[name="to_date"]').value;
    var empName    = empId ? (employees[empId] || '—') : 'جميع الموظفين';

    var desc = '';
    if (empName !== 'جميع الموظفين') desc += 'الموظف: ' + empName + '\n';
    if (fromDate) desc += 'من: ' + fromDate + '\n';
    if (toDate)   desc += 'إلى: ' + toDate + '\n';
    if (!desc)    desc  = 'كل السجلات (بدون فلتر)\n';

    return confirm(
        'تأكيد تفريغ البصمة\n\n' +
        desc + '\n' +
        'سيتم تحويل سجلات الحضور المطابقة إلى غياب\nوإعادة البصمات إلى حالة "غير معالَجة".\n\n' +
        'هل أنت متأكد؟'
    );
}
</script>
@endsection
