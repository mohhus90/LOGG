@extends('admin.layouts.admin')
@section('title') تفاصيل الراتب @endsection
@section('start') الرواتب @endsection
@section('home') <a href="{{ route('payroll.index') }}">مسير الرواتب</a> @endsection
@section('startpage') تفاصيل @endsection

@section('css')
<style>
    .payslip { max-width: 700px; margin: auto; }
    .payslip-section { border: 1px solid #dee2e6; border-radius: 6px; padding: 15px; margin-bottom: 15px; }
    .payslip-section h6 { color: #007bff; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 10px; }
    .payslip-row { display: flex; justify-content: space-between; padding: 3px 0; }
    .payslip-row .label { color: #555; }
    .payslip-row .value { font-weight: 600; }
    .value.plus  { color: #28a745; }
    .value.minus { color: #dc3545; }
    .net-row { background: #f8f9fa; padding: 10px; border-radius: 6px; font-size: 1.2em; }
</style>
@endsection

@section('content')
<div class="col-12">
    <div class="card payslip">
        <div class="card-header text-center bg-primary text-white">
            <h4 class="mb-0">
                <i class="fas fa-file-invoice-dollar ml-2"></i>
                قسيمة الراتب — {{ $payroll->month_name }} {{ $payroll->year }}
            </h4>
        </div>
        <div class="card-body">

            {{-- بيانات الموظف --}}
            <div class="payslip-section">
                <h6><i class="fas fa-user ml-1"></i> بيانات الموظف</h6>
                <div class="row">
                    <div class="col-6">
                        <div class="payslip-row">
                            <span class="label">الاسم:</span>
                            <span class="value">{{ $payroll->employee->employee_name_A ?? '-' }}</span>
                        </div>
                        <div class="payslip-row">
                            <span class="label">كود الموظف:</span>
                            <span class="value">{{ $payroll->employee->employee_id ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="payslip-row">
                            <span class="label">الفترة:</span>
                            <span class="value">{{ $payroll->period_from }} إلى {{ $payroll->period_to }}</span>
                        </div>
                        <div class="payslip-row">
                            <span class="label">الحالة:</span>
                            <span>{!! $payroll->status_label !!}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- أيام الحضور --}}
            <div class="payslip-section">
                <h6><i class="fas fa-calendar-check ml-1"></i> ملخص أيام الفترة</h6>
                <div class="row text-center">
                    <div class="col-3">
                        <div class="p-2 bg-light rounded">
                            <div class="h4 text-primary mb-0">{{ $payroll->total_days }}</div>
                            <small>إجمالي الأيام</small>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 bg-light rounded">
                            <div class="h4 text-success mb-0">{{ $payroll->work_days }}</div>
                            <small>أيام الحضور</small>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 bg-light rounded">
                            <div class="h4 text-danger mb-0">{{ $payroll->absence_days }}</div>
                            <small>أيام الغياب</small>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 bg-light rounded">
                            <div class="h4 text-warning mb-0">{{ $payroll->leave_days }}</div>
                            <small>أيام الإجازة</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- المستحقات --}}
            <div class="payslip-section">
                <h6><i class="fas fa-plus-circle ml-1 text-success"></i> المستحقات</h6>
                <div class="payslip-row">
                    <span class="label">الراتب الأساسي الكامل</span>
                    <span class="value">{{ number_format($payroll->basic_salary, 2) }}</span>
                </div>
                <div class="payslip-row">
                    <span class="label">
                        الراتب المستحق
                        <small class="text-muted">({{ number_format($payroll->daily_rate, 2) }} × {{ $payroll->work_days + $payroll->leave_days }} يوم)</small>
                    </span>
                    <span class="value plus">{{ number_format($payroll->earned_salary, 2) }}</span>
                </div>
                @if($payroll->fixed_allowances > 0)
                <div class="payslip-row">
                    <span class="label">الإضافات الثابتة</span>
                    <span class="value plus">{{ number_format($payroll->fixed_allowances, 2) }}</span>
                </div>
                @endif
                @if($payroll->overtime_amount > 0)
                <div class="payslip-row">
                    <span class="label">إجمالي الأوفرتايم</span>
                    <span class="value plus">{{ number_format($payroll->overtime_amount, 2) }}</span>
                </div>
                @endif
                @if($payroll->commissions_amount > 0)
                <div class="payslip-row">
                    <span class="label">العمولات</span>
                    <span class="value plus">{{ number_format($payroll->commissions_amount, 2) }}</span>
                </div>
                @endif
                <div class="payslip-row border-top pt-2 mt-1">
                    <span class="label"><strong>الإجمالي قبل الخصومات</strong></span>
                    <span class="value"><strong>{{ number_format($payroll->gross_salary, 2) }}</strong></span>
                </div>
            </div>

            {{-- الخصومات --}}
            <div class="payslip-section">
                <h6><i class="fas fa-minus-circle ml-1 text-danger"></i> الخصومات</h6>
                @if($payroll->late_deductions > 0)
                <div class="payslip-row">
                    <span class="label">خصم التأخيرات</span>
                    <span class="value minus">− {{ number_format($payroll->late_deductions, 2) }}</span>
                </div>
                @endif
                @if($payroll->absence_deductions > 0)
                <div class="payslip-row">
                    <span class="label">خصم الغياب ({{ $payroll->absence_days }} يوم)</span>
                    <span class="value minus">− {{ number_format($payroll->absence_deductions, 2) }}</span>
                </div>
                @endif
                @if($payroll->deductions_amount > 0)
                <div class="payslip-row">
                    <span class="label">خصومات أخرى</span>
                    <span class="value minus">− {{ number_format($payroll->deductions_amount, 2) }}</span>
                </div>
                @endif
                @if($payroll->advance_installment > 0)
                <div class="payslip-row">
                    <span class="label">قسط السلفة</span>
                    <span class="value minus">− {{ number_format($payroll->advance_installment, 2) }}</span>
                </div>
                @endif
                @if($payroll->insurance_deduction > 0)
                <div class="payslip-row">
                    <span class="label">خصم التأمينات</span>
                    <span class="value minus">− {{ number_format($payroll->insurance_deduction, 2) }}</span>
                </div>
                @endif
                @php
                    $totalDeductions = $payroll->late_deductions + $payroll->absence_deductions
                        + $payroll->deductions_amount + $payroll->advance_installment + $payroll->insurance_deduction;
                @endphp
                <div class="payslip-row border-top pt-2 mt-1">
                    <span class="label"><strong>إجمالي الخصومات</strong></span>
                    <span class="value minus"><strong>− {{ number_format($totalDeductions, 2) }}</strong></span>
                </div>
            </div>

            {{-- الصافي --}}
            <div class="net-row d-flex justify-content-between align-items-center">
                <span class="h5 mb-0">💰 صافي الراتب</span>
                <span class="h4 mb-0 text-primary">{{ number_format($payroll->net_salary, 2) }}</span>
            </div>

        </div>
        <div class="card-footer d-flex justify-content-between">
            <div>
                @if($payroll->status == 1)
                <a href="{{ route('payroll.approve', $payroll->id) }}"
                   class="btn btn-success"
                   onclick="return confirm('اعتماد هذا المسير؟')">
                    <i class="fas fa-check ml-1"></i> اعتماد المسير
                </a>
                @endif
                <button class="btn btn-secondary mr-2" onclick="window.print()">
                    <i class="fas fa-print ml-1"></i> طباعة
                </button>
            </div>
            <a href="{{ route('payroll.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-right ml-1"></i> رجوع
            </a>
        </div>
    </div>
</div>
@endsection