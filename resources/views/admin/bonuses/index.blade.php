@extends('admin.layouts.admin')
@section('title') المكافآت @endsection
@section('start') الرواتب والمؤثرات @endsection
@section('home') <a href="{{ route('bonuses.index') }}">المكافآت</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">
                <i class="fas fa-gift ml-2"></i>
                سجل المكافآت
                <a href="{{ route('bonuses.create') }}" class="btn btn-sm btn-success mr-2">
                    <i class="fas fa-plus"></i> إضافة مكافأة
                </a>
            </h3>
        </div>

        <div class="card-body pb-0">
            <form method="GET" action="{{ route('bonuses.index') }}" class="form-inline mb-3 flex-wrap gap-2">
                <select name="employee_id" class="form-control ml-2 mb-1">
                    <option value="">-- كل الموظفين --</option>
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ request('employee_id')==$emp->id?'selected':'' }}>
                        {{ $emp->employee_name_A }}
                    </option>
                    @endforeach
                </select>
                <select name="bonus_type" class="form-control ml-2 mb-1">
                    <option value="">-- نوع المكافأة --</option>
                    <option value="1" {{ request('bonus_type')==1?'selected':'' }}>مبلغ ثابت</option>
                    <option value="2" {{ request('bonus_type')==2?'selected':'' }}>أيام × مضاعف</option>
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
                <a href="{{ route('bonuses.index') }}" class="btn btn-secondary mb-1">مسح</a>
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
                            <th>النوع</th>
                            <th>القيمة / التفاصيل</th>
                            <th>الشهر/السنة</th>
                            <th>الحالة</th>
                            <th>ملاحظات</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $months = ['','يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
                        @endphp
                        @forelse($data as $bonus)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $bonus->employee->employee_name_A ?? '-' }}</td>
                            <td>{{ $bonus->bonus_date }}</td>
                            <td>
                                @if($bonus->bonus_type == 2)
                                    <span class="badge badge-info">أيام × مضاعف</span>
                                @else
                                    <span class="badge badge-primary">مبلغ ثابت</span>
                                @endif
                            </td>
                            <td class="text-success font-weight-bold">
                                @if($bonus->bonus_type == 2)
                                    {{ number_format($bonus->days, 2) }} يوم
                                    × {{ number_format($bonus->day_multiplier, 2) }}
                                    <small class="text-muted d-block">تُحتسب عند إصدار الكشف</small>
                                @else
                                    {{ number_format($bonus->amount, 2) }} ج.م
                                @endif
                            </td>
                            <td>{{ $months[$bonus->month] ?? $bonus->month }} {{ $bonus->year }}</td>
                            <td>{!! $bonus->status_label !!}</td>
                            <td><small>{{ $bonus->notes }}</small></td>
                            <td>
                                <a href="{{ route('bonuses.edit', $bonus->id) }}" class="btn btn-xs btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('bonuses.delete', $bonus->id) }}" class="btn btn-xs btn-danger"
                                   onclick="return confirm('حذف هذه المكافأة؟')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="text-center">لا توجد مكافآت مسجلة</td></tr>
                        @endforelse
                    </tbody>
                    @if($data->count() > 0)
                    <tfoot class="table-success">
                        <tr>
                            <th colspan="4" class="text-left">إجمالي المعتمد (المبلغ الثابت فقط)</th>
                            <th class="text-success">
                                {{ number_format($data->where('status',1)->where('bonus_type',1)->sum('amount'), 2) }} ج.م
                            </th>
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
