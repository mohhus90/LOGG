@extends('admin.layouts.admin')
@section('title') العمولات @endsection
@section('start') الرواتب والمؤثرات @endsection
@section('home') <a href="{{ route('commissions.index') }}">العمولات</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">
                <i class="fas fa-percentage ml-2"></i>
                سجل العمولات
                <a href="{{ route('commissions.create') }}" class="btn btn-sm btn-success mr-2">
                    <i class="fas fa-plus"></i> إضافة عمولة
                </a>
            </h3>
        </div>

        <div class="card-body pb-0">
            <form method="GET" action="{{ route('commissions.index') }}" class="form-inline mb-3 flex-wrap gap-2">
                <select name="employee_id" class="form-control ml-2 mb-1">
                    <option value="">-- كل الموظفين --</option>
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ request('employee_id')==$emp->id?'selected':'' }}>
                        {{ $emp->employee_name_A }}
                    </option>
                    @endforeach
                </select>
                <select name="month" class="form-control ml-2 mb-1">
                    <option value="">-- الشهر --</option>
                    @foreach(['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'] as $i=>$m)
                    <option value="{{ $i+1 }}" {{ request('month')==$i+1?'selected':'' }}>{{ $m }}</option>
                    @endforeach
                </select>
                <input type="number" name="year" class="form-control ml-2 mb-1" style="width:90px"
                    placeholder="السنة" value="{{ request('year', now()->year) }}">
                <select name="status" class="form-control ml-2 mb-1">
                    <option value="">-- الحالة --</option>
                    <option value="1" {{ request('status')==1?'selected':'' }}>معتمدة</option>
                    <option value="2" {{ request('status')==2?'selected':'' }}>معلقة</option>
                    <option value="3" {{ request('status')==3?'selected':'' }}>ملغاة</option>
                </select>
                <button type="submit" class="btn btn-primary ml-2 mb-1"><i class="fas fa-search"></i> بحث</button>
                <a href="{{ route('commissions.index') }}" class="btn btn-secondary mb-1">مسح</a>
            </form>
        </div>

        @if(session('success'))
            <div class="alert alert-success mx-3">{{ session('success') }}</div>
        @endif

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>الموظف</th>
                            <th>التاريخ</th>
                            <th>نوع العمولة</th>
                            <th>القيمة</th>
                            <th>الشهر/السنة</th>
                            <th>الحالة</th>
                            <th>ملاحظات</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $com)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $com->employee->employee_name_A ?? '-' }}</td>
                            <td>{{ $com->commission_date }}</td>
                            <td>{{ $com->commission_type ?? '—' }}</td>
                            <td class="text-success font-weight-bold">{{ number_format($com->amount, 2) }}</td>
                            <td>
                                @php
                                    $months = ['','يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
                                @endphp
                                {{ $months[$com->month] ?? $com->month }} {{ $com->year }}
                            </td>
                            <td>{!! $com->status_label !!}</td>
                            <td><small>{{ $com->notes }}</small></td>
                            <td>
                                <a href="{{ route('commissions.edit', $com->id) }}" class="btn btn-xs btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('commissions.delete', $com->id) }}" class="btn btn-xs btn-danger"
                                   onclick="return confirm('حذف هذه العمولة؟')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="text-center">لا توجد عمولات مسجلة</td></tr>
                        @endforelse
                    </tbody>
                    @if($data->count() > 0)
                    <tfoot class="table-success">
                        <tr>
                            <th colspan="4" class="text-left">إجمالي المعتمد</th>
                            <th class="text-success">{{ number_format($data->where('status',1)->sum('amount'), 2) }}</th>
                            <th colspan="4"></th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            {{ $data->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
