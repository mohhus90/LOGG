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
            </h3>
        </div>

        {{-- فلاتر البحث --}}
        <div class="card-body pb-0">
            <form method="GET" action="{{ route('attendance.index') }}" class="row">
                <div class="col-md-3 form-group">
                    <label>الموظف</label>
                    <select name="employee_id" class="form-control">
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
                    </select>
                </div>
                <div class="col-md-3 form-group d-flex align-items-end">
                    <button type="submit" class="btn btn-primary ml-2">
                        <i class="fas fa-search"></i> بحث
                    </button>
                    <a href="{{ route('attendance.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> مسح
                    </a>
                </div>
            </form>
        </div>

        @if(session('success'))
            <div class="alert alert-success mx-3">{{ session('success') }}</div>
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
                            <th>تأخير (د)</th>
                            <th>أوفرتايم (س)</th>
                            <th>خصم التأخير</th>
                            <th>قيمة الأوفرتايم</th>
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
                            <td>
                                @if($rec->late_minutes > 0)
                                    <span class="text-danger">{{ $rec->late_minutes }}</span>
                                @else
                                    <span class="text-success">0</span>
                                @endif
                            </td>
                            <td>
                                @if($rec->overtime_hours > 0)
                                    <span class="text-success">{{ $rec->overtime_hours }}</span>
                                @else 0 @endif
                            </td>
                            <td class="text-danger">{{ number_format($rec->late_deduction, 2) }}</td>
                            <td class="text-success">{{ number_format($rec->overtime_amount, 2) }}</td>
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
                        <tr><td colspan="11" class="text-center">لا توجد سجلات</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $data->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection