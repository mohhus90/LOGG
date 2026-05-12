{{-- resources/views/admin/payroll/index.blade.php --}}
@extends('admin.layouts.admin')
@section('title') مسير الرواتب @endsection
@section('start') الرواتب @endsection
@section('home') <a href="{{ route('payroll.index') }}">مسير الرواتب</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">
                <i class="fas fa-money-check-alt ml-2"></i>
                مسير الرواتب الشهري
                <a href="{{ route('payroll.create') }}" class="btn btn-sm btn-success mr-2">
                    <i class="fas fa-calculator"></i> احتساب رواتب
                </a>
            </h3>
        </div>

        <div class="card-body pb-0">
            <form method="GET" action="{{ route('payroll.index') }}" class="form-inline mb-3">
                <label class="ml-2">الشهر:</label>
                <select name="month" class="form-control ml-2">
                    @foreach(['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'] as $i=>$m)
                    <option value="{{ $i+1 }}" {{ request('month',$month)==$i+1?'selected':'' }}>{{ $m }}</option>
                    @endforeach
                </select>
                <label class="mr-2 ml-2">السنة:</label>
                <input type="number" name="year" class="form-control ml-2" style="width:90px"
                    value="{{ request('year', $year) }}">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> عرض
                </button>
            </form>
        </div>

        @if(session('success'))
            <div class="alert alert-success mx-3">{{ session('success') }}</div>
        @endif

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th>الموظف</th>
                            <th>الفترة</th>
                            <th>أيام الحضور</th>
                            <th>أيام الغياب</th>
                            <th>الراتب الأساسي</th>
                            <th>الراتب المستحق</th>
                            <th>أوفرتايم</th>
                            <th>عمولات</th>
                            <th>إجمالي الخصومات</th>
                            <th>الراتب الصافي</th>
                            <th>الحالة</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalNet = 0;
                            $totalGross = 0;
                        @endphp
                        @forelse($data as $p)
                        @php
                            $totalNet   += $p->net_salary;
                            $totalGross += $p->gross_salary;
                            $totalDeductions = $p->late_deductions + $p->absence_deductions + $p->deductions_amount + $p->advance_installment + $p->insurance_deduction;
                        @endphp
                        <tr>
                            <td>{{ $p->employee->employee_name_A ?? '-' }}</td>
                            <td><small>{{ $p->period_from }} - {{ $p->period_to }}</small></td>
                            <td class="text-center">{{ $p->work_days }}</td>
                            <td class="text-center text-danger">{{ $p->absence_days }}</td>
                            <td>{{ number_format($p->basic_salary, 2) }}</td>
                            <td>{{ number_format($p->earned_salary, 2) }}</td>
                            <td class="text-success">{{ number_format($p->overtime_amount, 2) }}</td>
                            <td class="text-success">{{ number_format($p->commissions_amount, 2) }}</td>
                            <td class="text-danger">{{ number_format($totalDeductions, 2) }}</td>
                            <td><strong class="text-primary">{{ number_format($p->net_salary, 2) }}</strong></td>
                            <td>{!! $p->status_label !!}</td>
                            <td>
                                <a href="{{ route('payroll.show', $p->id) }}"
                                   class="btn btn-xs btn-info" title="التفاصيل">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($p->status == 1)
                                <a href="{{ route('payroll.approve', $p->id) }}"
                                   class="btn btn-xs btn-success"
                                   onclick="return confirm('اعتماد هذا المسير؟')" title="اعتماد">
                                    <i class="fas fa-check"></i>
                                </a>
                                <a href="{{ route('payroll.delete', $p->id) }}"
                                   class="btn btn-xs btn-danger"
                                   onclick="return confirm('حذف هذا المسير؟')" title="حذف">
                                    <i class="fas fa-trash"></i>
                                </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="12" class="text-center">لا توجد بيانات لهذا الشهر. قم باحتساب الرواتب أولاً.</td></tr>
                        @endforelse
                    </tbody>
                    @if($data->count() > 0)
                    <tfoot class="table-warning">
                        <tr>
                            <th colspan="9" class="text-left">الإجمالي</th>
                            <th colspan="3" class="text-primary">{{ number_format($totalNet, 2) }}</th>
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