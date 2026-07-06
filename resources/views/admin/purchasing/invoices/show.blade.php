@extends('admin.layouts.purchasing')
@section('title') فاتورة شراء {{ $invoice->invoice_number }} @endsection
@section('start') المشتريات @endsection
@section('home') <a href="{{ route('purchase_invoices.index') }}">الفواتير</a> @endsection
@section('startpage') عرض التفاصيل @endsection

@section('content')
<div class="col-12">

    @php
        $paid      = $invoice->paid_amount ?? 0;
        $remaining = max(0, $invoice->total - $paid);
        $payStatus = $remaining <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid');
    @endphp

    <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:8px">
        <div>
            <a href="{{ route('purchase_invoices.edit', $invoice->id) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit ml-1"></i> تعديل
            </a>
            <a href="{{ route('purchase_invoices.print', $invoice->id) }}" class="btn btn-secondary btn-sm" target="_blank">
                <i class="fas fa-print ml-1"></i> طباعة
            </a>
            @if($remaining > 0 && ($invoice->status ?? '') !== 'cancelled')
            <a href="{{ route('purchase_payments.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus ml-1"></i> إضافة دفعة
            </a>
            @endif
            @if(($invoice->status ?? '') !== 'cancelled')
            <form action="{{ route('purchase_invoices.cancel', $invoice->id) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('إلغاء هذه الفاتورة؟ لا يمكن التراجع.')">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="fas fa-ban ml-1"></i> إلغاء الفاتورة
                </button>
            </form>
            @endif
        </div>
        <div class="d-flex align-items-center" style="gap:8px">
            {!! $invoice->payment_status_label !!}
            {!! $invoice->status_label !!}
        </div>
    </div>

    @if(($invoice->status ?? '') === 'cancelled')
    <div class="alert alert-danger">
        <i class="fas fa-ban ml-2"></i> هذه الفاتورة ملغاة. لا يمكن إجراء أي عمليات عليها.
    </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-truck ml-2"></i> بيانات المورد</h3></div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th style="width:130px" class="text-muted">المورد</th><td><strong>{{ $invoice->supplier->name ?? '—' }}</strong></td></tr>
                        <tr><th class="text-muted">الهاتف</th><td>{{ $invoice->supplier->phone ?? '—' }}</td></tr>
                        <tr><th class="text-muted">البريد</th><td>{{ $invoice->supplier->email ?? '—' }}</td></tr>
                        <tr><th class="text-muted">الرقم الضريبي</th><td>{{ $invoice->supplier->tax_number ?? '—' }}</td></tr>
                        <tr><th class="text-muted">العنوان</th><td>{{ $invoice->supplier->address ?? '—' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-outline card-warning">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-file-invoice ml-2"></i> بيانات الفاتورة</h3></div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th style="width:140px" class="text-muted">رقم الفاتورة</th><td><strong>{{ $invoice->invoice_number }}</strong></td></tr>
                        <tr><th class="text-muted">رقم فاتورة المورد</th><td>{{ $invoice->supplier_invoice_no ?? '—' }}</td></tr>
                        <tr><th class="text-muted">التاريخ</th><td>{{ \Carbon\Carbon::parse($invoice->date)->format('Y/m/d') }}</td></tr>
                        <tr>
                            <th class="text-muted">تاريخ الاستحقاق</th>
                            <td>
                                @if($invoice->due_date)
                                    <span class="{{ ($payStatus !== 'paid' && \Carbon\Carbon::parse($invoice->due_date)->isPast()) ? 'text-danger font-weight-bold' : '' }}">
                                        {{ \Carbon\Carbon::parse($invoice->due_date)->format('Y/m/d') }}
                                        @if($payStatus !== 'paid' && \Carbon\Carbon::parse($invoice->due_date)->isPast())
                                            <i class="fas fa-exclamation-triangle text-danger"></i>
                                        @endif
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">النوع</th>
                            <td>
                                @if(($invoice->invoice_type ?? '') === 'cash')
                                    <span class="badge badge-success">نقدي</span>
                                @else
                                    <span class="badge badge-info">آجل</span>
                                @endif
                            </td>
                        </tr>
                        <tr><th class="text-muted">الفرع</th><td>{{ $invoice->branch->name ?? '—' }}</td></tr>
                        @if($invoice->order_id)
                        <tr><th class="text-muted">أمر الشراء</th><td><a href="{{ route('purchase_orders.show', $invoice->order_id) }}">{{ $invoice->order->order_number ?? $invoice->order_id }}</a></td></tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-secondary">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-list ml-2"></i> بنود الفاتورة</h3></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>الصنف</th>
                            <th>الوصف</th>
                            <th>الوحدة</th>
                            <th class="text-center">الكمية</th>
                            <th class="text-left">السعر</th>
                            <th class="text-center">خصم%</th>
                            <th class="text-left">الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoice->items as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td><strong>{{ $item->item->name ?? '—' }}</strong></td>
                            <td>{{ $item->description ?? '—' }}</td>
                            <td>{{ $item->unit->name ?? '—' }}</td>
                            <td class="text-center">{{ number_format($item->quantity, 3) }}</td>
                            <td class="text-left">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-center">{{ $item->discount_percent ?? 0 }}%</td>
                            <td class="text-left"><strong>{{ number_format($item->total, 2) }}</strong></td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center text-muted py-3">لا توجد بنود</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-money-bill-wave ml-2"></i> سجل الدفعات</h3>
            @if($remaining > 0 && ($invoice->status ?? '') !== 'cancelled')
            <div class="card-tools">
                <a href="{{ route('purchase_payments.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus"></i> إضافة دفعة
                </a>
            </div>
            @endif
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>التاريخ</th>
                        <th>طريقة الدفع</th>
                        <th class="text-left">المبلغ</th>
                        <th>ملاحظات</th>
                        <th>إجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoice->payments as $i => $payment)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($payment->date)->format('Y/m/d') }}</td>
                        <td>{!! $payment->method_label !!}</td>
                        <td class="text-left text-success"><strong>{{ number_format($payment->amount, 2) }}</strong></td>
                        <td>{{ $payment->notes ?? '—' }}</td>
                        <td>
                            <a href="{{ route('purchase_payments.delete', $payment->id) }}" class="btn btn-xs btn-danger"
                               onclick="return confirm('حذف هذه الدفعة؟')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">
                            <i class="fas fa-inbox ml-2"></i> لا توجد دفعات مسجلة
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($invoice->payments && $invoice->payments->count() > 0)
                <tfoot class="bg-light">
                    <tr>
                        <td colspan="3" class="text-left font-weight-bold">إجمالي المدفوع:</td>
                        <td class="text-left text-success font-weight-bold">{{ number_format($paid, 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            @if($invoice->notes)
            <div class="card card-outline card-secondary">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-sticky-note ml-2"></i> ملاحظات</h3></div>
                <div class="card-body"><p class="text-muted mb-0">{{ $invoice->notes }}</p></div>
            </div>
            @endif
        </div>
        <div class="col-md-6">
            <div class="card card-outline card-primary">
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><td class="text-muted">الإجمالي الفرعي:</td><td class="text-left"><strong>{{ number_format($invoice->subtotal ?? 0, 2) }}</strong></td></tr>
                        <tr><td class="text-muted">الخصم:</td><td class="text-left text-danger">- {{ number_format($invoice->discount_amount ?? 0, 2) }}</td></tr>
                        <tr><td class="text-muted">الضريبة ({{ $invoice->tax_rate ?? 14 }}%):</td><td class="text-left">{{ number_format($invoice->tax_amount ?? 0, 2) }}</td></tr>
                        <tr class="border-top">
                            <td><strong>الإجمالي الكلي:</strong></td>
                            <td class="text-left"><strong class="h5">{{ number_format($invoice->total ?? 0, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-success"><strong>المدفوع:</strong></td>
                            <td class="text-left text-success"><strong class="h5">{{ number_format($paid, 2) }}</strong></td>
                        </tr>
                        <tr class="border-top">
                            <td class="{{ $remaining > 0 ? 'text-danger' : 'text-success' }}"><strong>المتبقي:</strong></td>
                            <td class="text-left {{ $remaining > 0 ? 'text-danger' : 'text-success' }}">
                                <strong class="h5">{{ number_format($remaining, 2) }}</strong>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
