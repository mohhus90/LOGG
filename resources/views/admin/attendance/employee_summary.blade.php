@extends('admin.layouts.admin')
@section('title') {{ __('admin.att_summary_title') }} @endsection
@section('start') {{ __('admin.hr_management') }} @endsection
@section('home') <a href="{{ route('attendance.index') }}">{{ __('admin.att_title') }}</a> @endsection
@section('startpage') {{ __('admin.att_summary_title') }} @endsection

@section('css')
<style>
    .stat-card { border-radius: 10px; padding: 15px; color: #fff; text-align: center; }
    .stat-card .num { font-size: 2em; font-weight: 700; }
    .stat-card .lbl { font-size: 0.85em; opacity: 0.9; }
</style>
@endsection

@section('content')
<div class="col-12">

    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-bar ml-2"></i>
                {{ __('admin.att_summary_title') }}: <strong>{{ $employee->employee_name_A }}</strong>
                ({{ $employee->employee_id }})
            </h3>
        </div>
        <div class="card-body pb-0">
            <form method="GET" class="form-inline mb-3">
                <label class="ml-2">{{ __('admin.att_month') }}:</label>
                <select name="month" class="form-control ml-2">
                    @foreach([__('admin.month_1'),__('admin.month_2'),__('admin.month_3'),__('admin.month_4'),__('admin.month_5'),__('admin.month_6'),__('admin.month_7'),__('admin.month_8'),__('admin.month_9'),__('admin.month_10'),__('admin.month_11'),__('admin.month_12')] as $i => $m)
                    <option value="{{ $i+1 }}" {{ $month==$i+1?'selected':'' }}>{{ $m }}</option>
                    @endforeach
                </select>
                <label class="mr-2 ml-2">{{ __('admin.att_year') }}:</label>
                <input type="number" name="year" class="form-control ml-2" style="width:90px" value="{{ $year }}">
                <button type="submit" class="btn btn-primary ml-2">
                    <i class="fas fa-search"></i> {{ __('admin.view') }}
                </button>
                <a href="{{ route('payroll.create') }}" class="btn btn-success mr-2">
                    <i class="fas fa-calculator"></i> {{ __('admin.payroll_calc') }}
                </a>
            </form>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-2">
            <div class="stat-card" style="background:#007bff">
                <div class="num">{{ $summary['present_days'] }}</div>
                <div class="lbl">{{ __('admin.att_present_days') }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card" style="background:#dc3545">
                <div class="num">{{ $summary['absent_days'] }}</div>
                <div class="lbl">{{ __('admin.att_absent_days') }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card" style="background:#ffc107; color:#333">
                <div class="num">{{ $summary['leave_days'] }}</div>
                <div class="lbl">{{ __('admin.att_leave_days') }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card" style="background:#fd7e14">
                <div class="num">{{ $summary['total_late_min'] }}</div>
                <div class="lbl">{{ __('admin.att_total_late') }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card" style="background:#28a745">
                <div class="num">{{ $summary['total_overtime'] }}</div>
                <div class="lbl">{{ __('admin.att_total_ot') }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card" style="background:#6f42c1">
                <div class="num">{{ number_format($summary['total_overtime_amount'] - $summary['total_late_deduction'], 2) }}</div>
                <div class="lbl">{{ __('admin.att_net_effects') }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ __('admin.att_month_details') }}</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>{{ __('admin.att_date') }}</th>
                            <th>{{ __('admin.att_day') }}</th>
                            <th>{{ __('admin.att_shift') }}</th>
                            <th>{{ __('admin.att_check_in') }}</th>
                            <th>{{ __('admin.att_check_out') }}</th>
                            <th>{{ __('admin.att_status') }}</th>
                            <th>{{ __('admin.att_late_min') }}</th>
                            <th>{{ __('admin.att_ot_hrs') }}</th>
                            <th>{{ __('admin.att_late_deduction') }}</th>
                            <th>{{ __('admin.att_ot_amount') }}</th>
                            <th>{{ __('admin.notes') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $rec)
                        <tr>
                            <td>{{ $rec->attendance_date->format('Y-m-d') }}</td>
                            <td>
                                @php
                                    $days = [__('admin.emp_sunday'),__('admin.emp_monday'),__('admin.emp_tuesday'),__('admin.emp_wednesday'),__('admin.emp_thursday'),__('admin.emp_friday'),__('admin.emp_saturday')];
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
                                {{ __('admin.no_data') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($records->count() > 0)
                    <tfoot class="table-info">
                        <tr>
                            <th colspan="6" class="text-left">{{ __('admin.total') }}</th>
                            <th class="text-danger">{{ $summary['total_late_min'] }} {{ __('admin.att_min_abbr') }}</th>
                            <th class="text-success">{{ $summary['total_overtime'] }} {{ __('admin.att_hr_abbr') }}</th>
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
