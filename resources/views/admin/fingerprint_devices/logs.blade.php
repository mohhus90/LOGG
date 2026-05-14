@extends('admin.layouts.admin')
@section('title') سجلات البصمة — {{ $device->device_name }} @endsection
@section('start') الحضور والانصراف @endsection
@section('home') <a href="{{ route('fingerprint_devices.index') }}">أجهزة البصمة</a> @endsection
@section('startpage') سجلات @endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list ml-2"></i>
                سجلات البصمة الخام — <strong>{{ $device->device_name }}</strong>
                <small class="text-muted">({{ $device->ip_address }}:{{ $device->port }})</small>
            </h3>
        </div>

        {{-- فلاتر --}}
        <div class="card-body pb-0">
            <form method="GET" class="form-inline mb-3">
                <label class="ml-2">التاريخ:</label>
                <input type="date" name="log_date" class="form-control ml-2"
                    value="{{ request('log_date', today()->format('Y-m-d')) }}">
                <select name="processed" class="form-control ml-2">
                    <option value="">-- كل السجلات --</option>
                    <option value="0" {{ request('processed')==='0'?'selected':'' }}>غير معالَجة</option>
                    <option value="1" {{ request('processed')==='1'?'selected':'' }}>معالَجة</option>
                </select>
                <button type="submit" class="btn btn-primary ml-2"><i class="fas fa-search"></i> بحث</button>
                <a href="{{ route('fingerprint_devices.index') }}" class="btn btn-secondary">رجوع</a>
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
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-4">لا توجد سجلات لهذا التاريخ</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $logs->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
