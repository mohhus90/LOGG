@extends('admin.layouts.admin')
@section('title') ملخص حضور الموظف @endsection
@section('start') شئون الموظفين @endsection
@section('home') <a href="{{ route('attendance.index') }}">الحضور والانصراف</a> @endsection
@section('startpage') ملخص @endsection

@section('css')
<style>
    .stat-card { border-radius: 10px; padding: 15px; color: #fff; text-align: center; }
    .stat-card .num { font-size: 2em; font-weight: 700; }
    .stat-card .lbl { font-size: 0.85em; opacity: 0.9; }
    .att-badge-حضر      { background:#28a745; color:#fff; }
    .att-badge-غياب     { background:#dc3545; color:#fff; }
    .att-badge-إجازة    { background:#ffc107; color:#333; }
    .att-badge-مأمورية  { background:#6c757d; color:#fff; }
</style>
@endsection

@section('content')
<div class="col-12">

    {{-- فلتر الشهر --}}
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-bar ml-2"></i>
                ملخص حضور: <strong>{{ $employee->employee_name_A }}</strong>
                ({{ $employee->employee_id }})
            </h3>
        </div>
        <div class="card-body pb-0">
            <form method="GET" class="form-inline mb-3">
                <label class="ml-2">الشهر:</label>
                <select name="month" class="form-control ml-2">
                    @foreach(['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'] as $i => $m)
                    <option value="{{ $i+1 }}" {{ $month==$i+1?'selected':'' }}>{{ $m }}</option>
                    @endforeach
                </select>
                <label class="mr-2 ml-2">السنة:</label>
                <input type="number" name="year" class="form-control ml-2" style="width:90px" value="{{ $year }}">
                <button type="submit" class="btn btn-primary ml-2">
                    <i class="fas fa-search"></i> عرض
                </button>
                <a href="{{ route('payroll.create') }}" class="btn btn-success mr-2">
                    <i class="fas fa-calculator"></i> احتساب الراتب
                </a>
            </form>
        </div>
    </div>

    {{-- بطاقات الملخص --}}
    <div class="row mb-3">
        <div class="col-md-2">
            <div class="stat-card" style="background:#007bff">
                <div class="num">{{ $summary['present_days'] }}</div>
                <div class="lbl">أيام الحضور</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card" style="background:#dc3545">
                <div class="num">{{ $summary['absent_days'] }}</div>
                <div class="lbl">أيام الغياب</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card" style="background:#ffc107; color:#333">
                <div class="num">{{ $summary['leave_days'] }}</div>
                <div class="lbl">أيام الإجازة</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card" style="background:#fd7e14">
                <div class="num">{{ $summary['total_late_min'] }}</div>
                <div class="lbl">إجمالي التأخير (د)</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card" style="background:#28a745">
                <div class="num">{{ $summary['total_overtime'] }}</div>
                <div class="lbl">إجمالي الأوفرتايم (س)</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card" style="background:#6f42c1">
                <div class="num">{{ number_format($summary['total_overtime_amount'] - $summary['total_late_deduction'], 2) }}</div>
                <div class="lbl">صافي المؤثرات</div>
            </div>
        </div>
    </div>

    {{-- جدول التفاصيل --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">تفاصيل أيام الشهر</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>التاريخ</th>
                            <th>اليوم</th>
                            <th>الشيفت</th>
                            <th>حضور</th>
                            <th>انصراف</th>
                            <th>الحالة</th>
                            <th>تأخير (د)</th>
                            <th>أوفرتايم (س)</th>
                            <th>خصم التأخير</th>
                            <th>قيمة الأوفرتايم</th>
                            <th>ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $rec)
                        <tr>
                            <td>{{ $rec->attendance_date->format('Y-m-d') }}</td>
                            <td>
                                @php
                                    $days = ['الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس','الجمعة','السبت'];
                                    echo $days[$rec->attendance_date->dayOfWeek];
                                @endphp
                            </td>
                            <td>
                                @if($rec->shift)
                                    <small>{{ $rec->shift->from_time }} - {{ $rec->shift->to_time }}</small>
                                @else - @endif
                            </td>
                            <td>{{ $rec->check_in_time ?? '—' }}</td>
                            <td>{{ $rec->check_out_time ?? '—' }}</td>
                            <td>{!! $rec->status_label !!}</td>
                            <td>
                                @if($rec->late_minutes > 0)
                                    <span class="text-danger font-weight-bold">{{ $rec->late_minutes }}</span>
                                @else <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($rec->overtime_hours > 0)
                                    <span class="text-success font-weight-bold">{{ $rec->overtime_hours }}</span>
                                @else <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="{{ $rec->late_deduction > 0 ? 'text-danger' : 'text-muted' }}">
                                {{ $rec->late_deduction > 0 ? number_format($rec->late_deduction, 2) : '—' }}
                            </td>
                            <td class="{{ $rec->overtime_amount > 0 ? 'text-success' : 'text-muted' }}">
                                {{ $rec->overtime_amount > 0 ? number_format($rec->overtime_amount, 2) : '—' }}
                            </td>
                            <td><small>{{ $rec->notes ?? '' }}</small></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-3">
                                لا توجد سجلات حضور لهذا الشهر
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($records->count() > 0)
                    <tfoot class="table-info">
                        <tr>
                            <th colspan="6" class="text-left">المجموع</th>
                            <th class="text-danger">{{ $summary['total_late_min'] }} د</th>
                            <th class="text-success">{{ $summary['total_overtime'] }} س</th>
                            <th class="text-danger">{{ number_format($summary['total_late_deduction'], 2) }}</th>
                            <th class="text-success">{{ number_format($summary['total_overtime_amount'], 2) }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
