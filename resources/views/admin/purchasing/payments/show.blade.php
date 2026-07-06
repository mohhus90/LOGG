@extends('admin.layouts.purchasing')
@section('title') تفاصيل الدفعة @endsection
@section('start') المشتريات @endsection
@section('home') <a href="{{ route('purchase_payments.index') }}">المدفوعات</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card card-info card-outline">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-receipt ml-2"></i>
                تفاصيل الدفعة
                {!! $payment->method_label !!}
            </h3>
            <div class="card-tools">
                <a href="{{ route('purchase_payments.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-right ml-1"></i> رجوع
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-light">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0"><i class="fas fa-info-circle ml-2 text-info"></i>معلومات الدفعة</h5>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-bordered mb-0">
                                <tr>
                                    <th class="bg-light" width="40%">رقم الدفعة</th>
                                    <td><strong class="text-primary">#{{ $payment->payment_number ?? $payment->id }}</strong></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">التاريخ</th>
                                    <td>{{ \Carbon\Carbon::parse($payment->date)->format('Y/m/d') }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">المبلغ</th>
                                    <td>
                                        <strong class="text-success" style="font-size:1.2em">
                                            {{ number_format($payment->amount, 2) }} ج.م
                                        </strong>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-light">طريقة الدفع</th>
                                    <td>{!! $payment->method_label !!}</td>
                                </tr>
                                @if($payment->reference_number)
                                <tr>
                                    <th class="bg-light">الرقم المرجعي</th>
                                    <td>{{ $payment->reference_number }}</td>
                                </tr>
                                @endif
                                @if($payment->bank_name)
                                <tr>
                                    <th class="bg-light">البنك</th>
                                    <td>{{ $payment->bank_name }}</td>
                                </tr>
                                @endif
                                @if($payment->cheque_number)
                                <tr>
                                    <th class="bg-light">رقم الشيك</th>
                                    <td>{{ $payment->cheque_number }}</td>
                                </tr>
                                @endif
                                @if($payment->cheque_date)
                                <tr>
                                    <th class="bg-light">تاريخ الشيك</th>
                                    <td>{{ \Carbon\Carbon::parse($payment->cheque_date)->format('Y/m/d') }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card card-light">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0"><i class="fas fa-truck ml-2 text-primary"></i>معلومات المورد والفاتورة</h5>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-bordered mb-0">
                                <tr>
                                    <th class="bg-light" width="40%">المورد</th>
                                    <td>
                                        @if($payment->supplier)
                                            <strong>{{ $payment->supplier->name }}</strong>
                                            @if($payment->supplier->phone)
                                                <br><small class="text-muted"><i class="fas fa-phone ml-1"></i>{{ $payment->supplier->phone }}</small>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-light">الفاتورة</th>
                                    <td>
                                        @if($payment->invoice)
                                            <a href="{{ route('purchase_invoices.show', $payment->invoice_id) }}" class="text-primary">
                                                <i class="fas fa-file-invoice ml-1"></i>
                                                #{{ $payment->invoice->invoice_number ?? $payment->invoice_id }}
                                            </a>
                                            <br>
                                            <small class="text-muted">
                                                إجمالي الفاتورة: {{ number_format($payment->invoice->total ?? 0, 2) }} ج.م
                                            </small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($payment->notes)
                                <tr>
                                    <th class="bg-light">ملاحظات</th>
                                    <td>{{ $payment->notes }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th class="bg-light">تاريخ الإدخال</th>
                                    <td>{{ $payment->created_at->format('Y/m/d H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <a href="{{ route('purchase_payments.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-right ml-1"></i> رجوع للقائمة
            </a>
            <a href="{{ route('purchase_payments.delete', $payment->id) }}" class="btn btn-danger mr-2"
               onclick="return confirm('هل أنت متأكد من حذف هذه الدفعة؟')">
                <i class="fas fa-trash ml-1"></i> حذف
            </a>
        </div>
    </div>
</div>
@endsection
