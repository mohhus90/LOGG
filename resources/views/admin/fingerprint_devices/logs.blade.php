@extends('admin.layouts.admin')
@section('title') سجلات البصمة — {{ $device->device_name }} @endsection
@section('start') الحضور والانصراف @endsection
@section('home') <a href="{{ route('fingerprint_devices.index') }}">أجهزة البصمة</a> @endsection
@section('startpage') سجلات @endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap" style="gap:8px">
            <h3 class="card-title mb-0">
                <i class="fas fa-list ml-2"></i>
                سجلات البصمة الخام — <strong>{{ $device->device_name }}</strong>
                <small class="text-muted">({{ $device->ip_address }}:{{ $device->port }})</small>
            </h3>
        </div>

        {{-- فلاتر + زر تفريغ --}}
        <div class="card-body pb-0">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    {{ session('error') }}
                </div>
            @endif

            <form method="GET" id="filterForm" class="row align-items-end mb-2">
                <div class="col-md-3">
                    <label class="small">البحث بالاسم</label>
                    <input type="text" name="employee_name" class="form-control"
                        placeholder="اكتب اسم الموظف..."
                        value="{{ request('employee_name') }}">
                </div>
                <div class="col-md-2">
                    <label class="small">من تاريخ</label>
                    <input type="date" name="log_date_from" class="form-control"
                        value="{{ request('log_date_from', today()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-2">
                    <label class="small">إلى تاريخ</label>
                    <input type="date" name="log_date_to" class="form-control"
                        value="{{ request('log_date_to', today()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-2">
                    <label class="small">الحالة</label>
                    <select name="processed" class="form-control">
                        <option value="">كل السجلات</option>
                        <option value="0" {{ request('processed')==='0'?'selected':'' }}>غير معالَجة</option>
                        <option value="1" {{ request('processed')==='1'?'selected':'' }}>معالَجة</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-search"></i> بحث
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('fingerprint_devices.logs', $device->id) }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-times"></i> مسح
                    </a>
                </div>
            </form>

            {{-- زر تفريغ البصمة --}}
            <form method="POST"
                  action="{{ route('fingerprint_devices.void_logs', $device->id) }}"
                  id="voidForm"
                  onsubmit="return confirmVoid(this)">
                @csrf
                {{-- نمرر قيم الفلتر الحالية --}}
                <input type="hidden" name="log_date_from"  value="{{ request('log_date_from') }}">
                <input type="hidden" name="log_date_to"    value="{{ request('log_date_to') }}">
                <input type="hidden" name="processed"      value="{{ request('processed') }}">
                <input type="hidden" name="employee_name"  value="{{ request('employee_name') }}">

                <div class="mb-3">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-eraser ml-1"></i>
                        تفريغ البصمة وتحويلها غياب
                        @if(request('log_date_from') || request('log_date_to') || request('employee_name') || request('processed') !== null)
                            <span class="badge badge-light text-danger">حسب الفلتر الحالي</span>
                        @else
                            <span class="badge badge-light text-danger">كل اليوم</span>
                        @endif
                    </button>
                    <small class="text-muted mr-2">
                        <i class="fas fa-info-circle"></i>
                        يُحوِّل سجلات الحضور المقابلة إلى غياب ويُعيد البصمات لحالة "غير معالَجة"
                    </small>
                </div>
            </form>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Finger ID</th>
                            <th>اسم الموظف</th>
                            <th>وقت البصمة</th>
                            <th>نوع البصمة</th>
                            <th>الحالة</th>
                            <th>تعديل</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        @php $emp = $employees->get($log->finger_id); @endphp
                        <tr class="{{ $log->is_processed ? '' : 'table-warning' }}">
                            <td>{{ $loop->iteration }}</td>
                            <td><code>{{ $log->finger_id }}</code></td>
                            <td>
                                @if($emp)
                                    <span class="text-success">{{ $emp->employee_name_A }}</span>
                                @else
                                    <span class="text-danger"><i class="fas fa-exclamation-triangle"></i> غير معروف</span>
                                @endif
                            </td>
                            <td>{{ $log->punch_time->format('Y-m-d H:i:s') }}</td>
                            <td>
                                @switch($log->punch_type)
                                    @case(1) <span class="badge badge-success">حضور</span> @break
                                    @case(2) <span class="badge badge-danger">انصراف</span> @break
                                    @default <span class="badge badge-secondary">عام</span>
                                @endswitch
                            </td>
                            <td>
                                @if($log->is_processed)
                                    <span class="badge badge-success"><i class="fas fa-check"></i> معالَج</span>
                                @else
                                    <span class="badge badge-warning"><i class="fas fa-clock"></i> انتظار</span>
                                @endif
                            </td>
                            <td>
                                <button type="button"
                                        class="btn btn-xs btn-outline-primary"
                                        onclick="openEditModal(
                                            {{ $log->id }},
                                            '{{ $log->punch_time->format('Y-m-d\TH:i') }}',
                                            {{ $log->punch_type }}
                                        )">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-4">لا توجد سجلات لهذا التاريخ</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $logs->appends(request()->query())->links() }}
        </div>
    </div>
</div>

{{-- Modal تعديل سجل البصمة --}}
<div class="modal fade" id="editLogModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="editLogForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-edit ml-1"></i> تعديل سجل البصمة</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">وقت البصمة <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="punch_time" id="editPunchTime"
                               class="form-control" required>
                        <small class="text-muted">بعد التعديل سيُعاد تصنيف السجل كـ "غير معالَج"</small>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">نوع البصمة <span class="text-danger">*</span></label>
                        <select name="punch_type" id="editPunchType" class="form-control">
                            <option value="0">عام</option>
                            <option value="1">حضور (دخول)</option>
                            <option value="2">انصراف (خروج)</option>
                        </select>
                    </div>

                    {{-- نمرر الفلتر الحالي ليُعاد التوجيه إليه --}}
                    <input type="hidden" name="log_date_from" value="{{ request('log_date_from') }}">
                    <input type="hidden" name="log_date_to"   value="{{ request('log_date_to') }}">
                    <input type="hidden" name="processed"     value="{{ request('processed') }}">
                    <input type="hidden" name="employee_name" value="{{ request('employee_name') }}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save ml-1"></i> حفظ التعديل
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script>
const baseUpdateUrl = "{{ url('admin/dashboard/fingerprint_devices/' . $device->id . '/logs') }}";

function openEditModal(logId, punchTime, punchType) {
    document.getElementById('editPunchTime').value = punchTime;
    document.getElementById('editPunchType').value = punchType;
    document.getElementById('editLogForm').action   = baseUpdateUrl + '/' + logId;
    $('#editLogModal').modal('show');
}

function confirmVoid(form) {
    const from = form.querySelector('[name="log_date_from"]').value;
    const to   = form.querySelector('[name="log_date_to"]').value;
    const name = form.querySelector('[name="employee_name"]').value;

    let filterDesc = '';
    if (from || to) filterDesc += ` من ${from || '...'} إلى ${to || '...'}`;
    if (name)       filterDesc += ` — موظف: "${name}"`;
    if (!filterDesc) filterDesc = ' (كل سجلات اليوم)';

    return confirm(
        'تأكيد تفريغ البصمة\n\n' +
        'سيتم تحويل سجلات الحضور المقابلة للفلتر التالي إلى غياب:\n' +
        filterDesc + '\n\n' +
        'السجلات لن تُحذف — فقط ستُعاد لحالة "غير معالَجة" ويُحوَّل الحضور المقابل إلى غياب.\n\n' +
        'هل أنت متأكد؟'
    );
}
</script>
@endsection
