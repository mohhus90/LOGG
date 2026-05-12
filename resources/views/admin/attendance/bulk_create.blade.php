@extends('admin.layouts.admin')
@section('title') إدخال حضور دفعي @endsection
@section('start') شئون الموظفين @endsection
@section('home') <a href="{{ route('attendance.index') }}">الحضور والانصراف</a> @endsection
@section('startpage') إدخال دفعي @endsection

@section('css')
<style>
    .att-table td, .att-table th { padding: 4px 6px; font-size: 13px; }
    .att-table input[type=time] { width: 110px; }
    .att-table select { width: 110px; }
    tr.already-saved { background: #f0fff4 !important; }
</style>
@endsection

@section('content')
<div class="col-12">
    <div class="card card-success">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list ml-2"></i>إدخال حضور وانصراف دفعي</h3>
        </div>

        {{-- اختيار التاريخ --}}
        <div class="card-body pb-0">
            <form method="GET" action="{{ route('attendance.bulk_create') }}" class="form-inline mb-3">
                <label class="ml-2">تاريخ اليوم:</label>
                <input type="date" name="date" class="form-control ml-2" value="{{ $date }}">
                <button type="submit" class="btn btn-info">
                    <i class="fas fa-calendar-check"></i> تحديث
                </button>
            </form>
        </div>

        <form action="{{ route('attendance.bulk_store') }}" method="POST">
            @csrf
            <input type="hidden" name="attendance_date" value="{{ $date }}">

            <div class="card-body pt-0">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-sm att-table">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>كود الموظف</th>
                                <th>اسم الموظف</th>
                                <th>الشيفت</th>
                                <th>وقت الحضور</th>
                                <th>وقت الانصراف</th>
                                <th>الحالة</th>
                                <th>ملاحظات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($employees as $emp)
                            @php $saved = in_array($emp->id, $existing); @endphp
                            <tr class="{{ $saved ? 'already-saved' : '' }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $emp->employee_id }}</td>
                                <td>{{ $emp->employee_name_A }}</td>
                                <td>
                                    @if($emp->shifts_type)
                                        {{ $emp->shifts_type->type }}
                                        <br><small class="text-muted">
                                            {{ $emp->shifts_type->from_time }} - {{ $emp->shifts_type->to_time }}
                                        </small>
                                    @else -
                                    @endif
                                </td>
                                <td>
                                    <input type="time" name="records[{{ $emp->id }}][check_in]"
                                        class="form-control form-control-sm"
                                        {{ $saved ? 'disabled' : '' }}>
                                </td>
                                <td>
                                    <input type="time" name="records[{{ $emp->id }}][check_out]"
                                        class="form-control form-control-sm"
                                        {{ $saved ? 'disabled' : '' }}>
                                </td>
                                <td>
                                    <select name="records[{{ $emp->id }}][status]"
                                        class="form-control form-control-sm"
                                        {{ $saved ? 'disabled' : '' }}>
                                        <option value="1">حضر</option>
                                        <option value="2">غياب</option>
                                        <option value="3">إجازة</option>
                                        <option value="4">إجازة رسمية</option>
                                        <option value="5">مأمورية</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="records[{{ $emp->id }}][notes]"
                                        class="form-control form-control-sm"
                                        placeholder="ملاحظة"
                                        {{ $saved ? 'disabled' : '' }}>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save ml-1"></i> حفظ الحضور
                </button>
                <a href="{{ route('attendance.index') }}" class="btn btn-secondary mr-2">رجوع</a>
                <small class="text-muted mr-3">
                    <i class="fas fa-info-circle"></i>
                    الصفوف الخضراء مسجلة مسبقاً لهذا اليوم
                </small>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script>
// عند اختيار "غياب" يتم مسح أوقات الحضور/الانصراف
document.querySelectorAll('select[name*="[status]"]').forEach(sel => {
    sel.addEventListener('change', function () {
        const row    = this.closest('tr');
        const inTime  = row.querySelector('input[name*="[check_in]"]');
        const outTime = row.querySelector('input[name*="[check_out]"]');
        if (this.value == '2') {
            if(inTime)  inTime.value  = '';
            if(outTime) outTime.value = '';
        }
    });
});
</script>
@endsection