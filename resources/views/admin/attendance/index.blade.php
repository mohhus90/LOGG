@extends('admin.layouts.admin')
@section('title') الحضور والانصراف @endsection
@section('start') شئون الموظفين @endsection
@section('home') <a href="{{ route('attendance.index') }}">الحضور والانصراف</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">
                <i class="fas fa-fingerprint ml-2"></i>
                سجلات الحضور والانصراف
                <a class="btn btn-sm btn-success mr-2" href="{{ route('attendance.create') }}">
                    <i class="fas fa-plus"></i> إضافة سجل
                </a>
                <a class="btn btn-sm btn-info mr-1" href="{{ route('attendance.bulk_create') }}">
                    <i class="fas fa-list"></i> إدخال دفعي
                </a>
                <a class="btn btn-sm btn-purple mr-1" href="{{ route('attendance.generate_weekly_leaves_form') }}"
                   style="background:#6f42c1;color:#fff;border-color:#6f42c1">
                    <i class="fas fa-calendar-week"></i> توليد إجازات أسبوعية
                </a>
            </h3>
        </div>

        {{-- فلاتر البحث --}}
        <div class="card-body pb-0">
            <form method="GET" action="{{ route('attendance.index') }}" class="row">
                <div class="col-md-3 form-group">
                    <label>الموظف</label>
                    <select name="employee_id" class="form-control select2">
                        <option value="">-- الكل --</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('employee_id')==$emp->id ? 'selected':'' }}>
                            {{ $emp->employee_name_A }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 form-group">
                    <label>من تاريخ</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2 form-group">
                    <label>إلى تاريخ</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-2 form-group">
                    <label>الحالة</label>
                    <select name="status" class="form-control">
                        <option value="">-- الكل --</option>
                        <option value="1" {{ request('status')==1?'selected':'' }}>حضر</option>
                        <option value="2" {{ request('status')==2?'selected':'' }}>غياب</option>
                        <option value="3" {{ request('status')==3?'selected':'' }}>إجازة</option>
                        <option value="4" {{ request('status')==4?'selected':'' }}>إجازة رسمية</option>
                        <option value="5" {{ request('status')==5?'selected':'' }}>مأمورية</option>
                        <option value="6" {{ request('status')==6?'selected':'' }}>إجازة أسبوعية</option>
                    </select>
                </div>
                <div class="col-md-1 form-group">
                    <label>سجلات / صفحة</label>
                    <select name="per_page" class="form-control">
                        @foreach([10, 20, 50, 100] as $n)
                            <option value="{{ $n }}" {{ request('per_page', 20) == $n ? 'selected' : '' }}>{{ $n }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 form-group d-flex align-items-end">
                    <button type="submit" class="btn btn-primary ml-2">
                        <i class="fas fa-search"></i> بحث
                    </button>
                </div>
                <div class="col-md-1 form-group d-flex align-items-end">
                    <button type="button" class="btn btn-danger" id="btnBulkDelete"
                            data-employees='@json($employees->pluck("employee_name_A","id"))'>
                        <i class="fas fa-trash-alt"></i> مسح
                    </button>
                </div>
            </form>

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
                            <th>التاريخ</th>
                            <th>الموظف</th>
                            <th>الشيفت</th>
                            <th>حضور</th>
                            <th>انصراف</th>
                            <th>الحالة</th>
                            <th>تأخير</th>
                            <th>انصراف مبكر</th>
                            <th>أوفرتايم (س)</th>
                            <th>خصم تأخير</th>
                            <th>خصم مبكر</th>
                            <th>قيمة أوفرتايم</th>
                            <th>بدل إجازة</th>
                            <th>إجراء</th>
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
                            {{-- التأخير: بالدقائق أو بجزء اليوم حسب طريقة الاحتساب --}}
                            <td>
                                @if($rec->late_minutes > 0 || $rec->late_fraction)
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
                                @if($rec->early_departure_minutes > 0)
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
                                    <br><small class="badge badge-success" style="font-size:.75em">يوم راحة</small>
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
                                   onclick="return confirm('هل تريد حذف هذا السجل؟')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="13" class="text-center">لا توجد سجلات</td></tr>
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
</script>
@endsection
