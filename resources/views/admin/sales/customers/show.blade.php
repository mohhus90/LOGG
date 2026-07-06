@extends('admin.layouts.sales')
@section('title') بيانات العميل @endsection
@section('start') المبيعات @endsection
@section('home') <a href="{{ route('sales_customers.index') }}">العملاء</a> @endsection
@section('startpage') عرض @endsection

@section('css')
<style>
    .info-box-number { font-size: 1.4rem; }
    .stat-card { border-radius: 8px; }
    .detail-row {
        display: flex;
        justify-content: space-between;
        padding: 7px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .detail-row:last-child { border-bottom: none; }
    .detail-label { color: #6c757d; font-weight: 500; }
    .detail-value { font-weight: 600; color: #343a40; }
</style>
@endsection

@section('content')
<div class="col-12">

    {{-- إجراءات سريعة --}}
    <div class="mb-3 d-flex flex-wrap">
        <a href="{{ route('sales_customers.edit', $customer->id) }}" class="btn btn-warning ml-2 mb-1">
            <i class="fas fa-edit ml-1"></i> تعديل البيانات
        </a>
        <a href="{{ route('sales_invoices.create', ['customer_id' => $customer->id]) }}" class="btn btn-success ml-2 mb-1">
            <i class="fas fa-file-invoice ml-1"></i> إضافة فاتورة
        </a>
        <a href="{{ route('sales_payments.create', ['customer_id' => $customer->id]) }}" class="btn btn-primary ml-2 mb-1">
            <i class="fas fa-hand-holding-usd ml-1"></i> تسجيل دفعة
        </a>
        <a href="{{ route('sales_customers.index') }}" class="btn btn-secondary mb-1">
            <i class="fas fa-arrow-right ml-1"></i> رجوع
        </a>
    </div>

    {{-- بطاقات الإحصاء --}}
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="info-box stat-card bg-info">
                <span class="info-box-icon"><i class="fas fa-file-invoice-dollar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">إجمالي الفواتير</span>
                    <span class="info-box-number">{{ number_format($totalInvoiced ?? 0, 2) }} <small>ج.م</small></span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box stat-card bg-success">
                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">إجمالي المدفوعات</span>
                    <span class="info-box-number">{{ number_format($totalPaid ?? 0, 2) }} <small>ج.م</small></span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box stat-card {{ ($totalDebt ?? 0) > 0 ? 'bg-danger' : 'bg-secondary' }}">
                <span class="info-box-icon"><i class="fas fa-exclamation-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">صافي الديون</span>
                    <span class="info-box-number">{{ number_format($totalDebt ?? 0, 2) }} <small>ج.م</small></span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- بيانات العميل --}}
        <div class="col-md-5">
            <div class="card card-outline card-primary">
                <div class="card-header py-2">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-user ml-1"></i> بيانات العميل
                        @if($customer->type == 'company')
                            <span class="badge badge-primary mr-2"><i class="fas fa-building"></i> شركة</span>
                        @else
                            <span class="badge badge-secondary mr-2"><i class="fas fa-user"></i> فرد</span>
                        @endif
                    </h6>
                </div>
                <div class="card-body py-2">
                    <div class="detail-row">
                        <span class="detail-label">الكود</span>
                        <span class="detail-value"><code>{{ $customer->code ?? '—' }}</code></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">الاسم</span>
                        <span class="detail-value">{{ $customer->name }}</span>
                    </div>
                    @if($customer->name_en)
                    <div class="detail-row">
                        <span class="detail-label">الاسم بالإنجليزية</span>
                        <span class="detail-value">{{ $customer->name_en }}</span>
                    </div>
                    @endif
                    <div class="detail-row">
                        <span class="detail-label">الهاتف</span>
                        <span class="detail-value">{{ $customer->phone ?? '—' }}</span>
                    </div>
                    @if($customer->phone2)
                    <div class="detail-row">
                        <span class="detail-label">هاتف بديل</span>
                        <span class="detail-value">{{ $customer->phone2 }}</span>
                    </div>
                    @endif
                    @if($customer->email)
                    <div class="detail-row">
                        <span class="detail-label">البريد الإلكتروني</span>
                        <span class="detail-value">{{ $customer->email }}</span>
                    </div>
                    @endif
                    @if($customer->address)
                    <div class="detail-row">
                        <span class="detail-label">العنوان</span>
                        <span class="detail-value">{{ $customer->address }}{{ $customer->city ? ' — ' . $customer->city : '' }}{{ $customer->governorate ? ' — ' . $customer->governorate : '' }}</span>
                    </div>
                    @endif
                    @if($customer->tax_number)
                    <div class="detail-row">
                        <span class="detail-label">الرقم الضريبي</span>
                        <span class="detail-value">{{ $customer->tax_number }}</span>
                    </div>
                    @endif
                    @if($customer->commercial_register)
                    <div class="detail-row">
                        <span class="detail-label">السجل التجاري</span>
                        <span class="detail-value">{{ $customer->commercial_register }}</span>
                    </div>
                    @endif
                    <div class="detail-row">
                        <span class="detail-label">حد الائتمان</span>
                        <span class="detail-value text-success">{{ number_format($customer->credit_limit ?? 0, 2) }} ج.م</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">شروط الدفع</span>
                        <span class="detail-value">{{ $customer->payment_terms ?? 0 }} يوم</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">الرصيد الافتتاحي</span>
                        <span class="detail-value">{{ number_format($customer->opening_balance ?? 0, 2) }} ج.م</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">الحالة</span>
                        <span class="detail-value">
                            @if($customer->is_active)
                                <span class="badge badge-success">مفعّل</span>
                            @else
                                <span class="badge badge-secondary">معطّل</span>
                            @endif
                        </span>
                    </div>
                    @if($customer->notes)
                    <div class="mt-2 p-2 bg-light rounded">
                        <small class="text-muted"><i class="fas fa-sticky-note ml-1"></i> {{ $customer->notes }}</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- الجداول --}}
        <div class="col-md-7">

            {{-- آخر فواتير --}}
            <div class="card card-outline card-success mb-3">
                <div class="card-header py-2">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-file-invoice ml-1"></i> آخر 10 فواتير
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>رقم الفاتورة</th>
                                    <th>التاريخ</th>
                                    <th>الإجمالي</th>
                                    <th>المدفوع</th>
                                    <th>المتبقي</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customer->invoices as $inv)
                                <tr>
                                    <td>
                                        <a href="{{ route('sales_invoices.show', $inv->id) }}">
                                            {{ $inv->invoice_number ?? '#' . $inv->id }}
                                        </a>
                                    </td>
                                    <td>{{ optional($inv->date)->format('Y-m-d') ?? $inv->created_at->format('Y-m-d') }}</td>
                                    <td class="text-primary">{{ number_format($inv->total ?? 0, 2) }}</td>
                                    <td class="text-success">{{ number_format($inv->paid_amount ?? 0, 2) }}</td>
                                    <td class="{{ ($inv->remaining_amount ?? 0) > 0 ? 'text-danger font-weight-bold' : 'text-muted' }}">
                                        {{ number_format($inv->remaining_amount ?? 0, 2) }}
                                    </td>
                                    <td>{!! $inv->payment_status_label !!}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-2">لا توجد فواتير</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- آخر دفعات --}}
            <div class="card card-outline card-primary">
                <div class="card-header py-2">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-hand-holding-usd ml-1"></i> آخر 10 دفعات
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>التاريخ</th>
                                    <th>المبلغ</th>
                                    <th>طريقة الدفع</th>
                                    <th>ملاحظات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customer->payments as $pay)
                                <tr>
                                    <td>{{ optional($pay->date)->format('Y-m-d') ?? $pay->created_at->format('Y-m-d') }}</td>
                                    <td class="text-success font-weight-bold">{{ number_format($pay->amount ?? 0, 2) }}</td>
                                    <td>{!! $pay->method_label !!}</td>
                                    <td><small>{{ $pay->notes ?? '—' }}</small></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-2">لا توجد دفعات مسجلة</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
