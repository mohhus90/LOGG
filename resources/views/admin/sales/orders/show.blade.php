@extends('admin.layouts.sales')
@section('title') أمر بيع {{ $order->order_number }} @endsection
@section('start') المبيعات @endsection
@section('home') <a href="{{ route('sales_orders.index') }}">أوامر البيع</a> @endsection
@section('startpage') عرض التفاصيل @endsection

@section('content')
<div class="col-12">

    {{-- Action Bar --}}
    <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:8px">
        <div>
            <a href="{{ route('sales_orders.edit', $order->id) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit ml-1"></i> تعديل
            </a>
            <a href="{{ route('sales_orders.print', $order->id) }}" class="btn btn-secondary btn-sm" target="_blank">
                <i class="fas fa-print ml-1"></i> طباعة
            </a>
            @if(in_array($order->status, ['confirmed', 'processing']))
            <form action="{{ route('sales_invoices.createFromOrder', $order->id) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('إنشاء فاتورة لهذا الأمر؟')">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-file-invoice ml-1"></i> إنشاء فاتورة
                </button>
            </form>
            @endif
            {{-- Change Status --}}
            <div class="btn-group">
                <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown">
                    <i class="fas fa-tag ml-1"></i> تغيير الحالة
                </button>
                <div class="dropdown-menu">
                    @foreach(['confirmed' => 'مؤكد', 'processing' => 'قيد التنفيذ', 'partial' => 'تسليم جزئي', 'delivered' => 'مُسلَّم', 'cancelled' => 'ملغي'] as $val => $label)
                        @if($order->status !== $val)
                        <form action="{{ route('sales_orders.status', $order->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="{{ $val }}">
                            <button type="submit" class="dropdown-item">{{ $label }}</button>
                        </form>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
        <div>
            @switch($order->status)
                @case('draft')       <span class="badge badge-secondary p-2" style="font-size:1rem">مسودة</span> @break
                @case('confirmed')   <span class="badge badge-primary   p-2" style="font-size:1rem">مؤكد</span> @break
                @case('processing')  <span class="badge badge-info      p-2" style="font-size:1rem">قيد التنفيذ</span> @break
                @case('partial')     <span class="badge badge-warning   p-2" style="font-size:1rem">تسليم جزئي</span> @break
                @case('delivered')   <span class="badge badge-success   p-2" style="font-size:1rem">مُسلَّم</span> @break
                @case('cancelled')   <span class="badge badge-danger    p-2" style="font-size:1rem">ملغي</span> @break
            @endswitch
        </div>
    </div>

    {{-- Two-Column Info --}}
    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-user ml-2"></i> بيانات العميل</h3></div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th style="width:130px" class="text-muted">العميل</th><td><strong>{{ $order->customer->name ?? '—' }}</strong></td></tr>
                        <tr><th class="text-muted">الهاتف</th><td>{{ $order->customer->phone ?? '—' }}</td></tr>
                        <tr><th class="text-muted">البريد</th><td>{{ $order->customer->email ?? '—' }}</td></tr>
                        <tr><th class="text-muted">الرقم الضريبي</th><td>{{ $order->customer->tax_number ?? '—' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-outline card-warning">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-shopping-cart ml-2"></i> بيانات أمر البيع</h3></div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th style="width:140px" class="text-muted">رقم الأمر</th><td><strong>{{ $order->order_number }}</strong></td></tr>
                        <tr><th class="text-muted">التاريخ</th><td>{{ \Carbon\Carbon::parse($order->date)->format('Y/m/d') }}</td></tr>
                        <tr><th class="text-muted">تاريخ التسليم</th><td>{{ $order->delivery_date ? \Carbon\Carbon::parse($order->delivery_date)->format('Y/m/d') : '—' }}</td></tr>
                        <tr><th class="text-muted">عنوان التسليم</th><td>{{ $order->delivery_address ?? '—' }}</td></tr>
                        <tr><th class="text-muted">الفرع</th><td>{{ $order->branch->name ?? '—' }}</td></tr>
                        @if($order->quotation_id)
                        <tr><th class="text-muted">مرجع عرض السعر</th><td><a href="{{ route('sales_quotations.show', $order->quotation_id) }}">{{ $order->quotation->quote_number ?? $order->quotation_id }}</a></td></tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Items Table with Delivery Progress --}}
    <div class="card card-outline card-secondary">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-list ml-2"></i> بنود أمر البيع</h3></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>الصنف</th>
                            <th>الوحدة</th>
                            <th class="text-center">الكمية المطلوبة</th>
                            <th class="text-center">الكمية المسلّمة</th>
                            <th class="text-center">المتبقي</th>
                            <th class="text-left">السعر</th>
                            <th class="text-center">خصم%</th>
                            <th class="text-left">الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($order->items as $i => $item)
                        @php
                            $delivered = $item->delivered_qty ?? 0;
                            $remaining = max(0, $item->quantity - $delivered);
                            $pct = $item->quantity > 0 ? round($delivered / $item->quantity * 100) : 0;
                        @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td><strong>{{ $item->item->name ?? $item->item_name ?? '—' }}</strong></td>
                            <td>{{ $item->unit->name ?? '—' }}</td>
                            <td class="text-center">{{ number_format($item->quantity, 3) }}</td>
                            <td class="text-center">
                                {{ number_format($delivered, 3) }}
                                <div class="progress mt-1" style="height:4px">
                                    <div class="progress-bar bg-success" style="width:{{ $pct }}%"></div>
                                </div>
                                <small class="text-muted">{{ $pct }}%</small>
                            </td>
                            <td class="text-center {{ $remaining > 0 ? 'text-warning' : 'text-success' }}">
                                <strong>{{ number_format($remaining, 3) }}</strong>
                            </td>
                            <td class="text-left">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-center">{{ $item->discount_percent ?? 0 }}%</td>
                            <td class="text-left"><strong>{{ number_format($item->total, 2) }}</strong></td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="text-center text-muted py-3">لا توجد بنود</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Linked Invoices --}}
    @if($order->invoices && $order->invoices->count() > 0)
    <div class="card card-outline card-info">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-file-invoice ml-2"></i> الفواتير المرتبطة</h3></div>
        <div class="card-body p-0">
            <table class="table table-bordered table-sm mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>رقم الفاتورة</th>
                        <th>التاريخ</th>
                        <th>الإجمالي</th>
                        <th>المحصّل</th>
                        <th>المتبقي</th>
                        <th>الحالة</th>
                        <th>إجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->invoices as $i => $invoice)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $invoice->invoice_number }}</td>
                        <td>{{ \Carbon\Carbon::parse($invoice->date)->format('Y/m/d') }}</td>
                        <td class="text-left">{{ number_format($invoice->total, 2) }}</td>
                        <td class="text-left text-success">{{ number_format($invoice->paid_amount ?? 0, 2) }}</td>
                        <td class="text-left text-danger">{{ number_format(($invoice->total - ($invoice->paid_amount ?? 0)), 2) }}</td>
                        <td>
                            @if(($invoice->total - ($invoice->paid_amount ?? 0)) <= 0)
                                <span class="badge badge-success">مسدّد</span>
                            @else
                                <span class="badge badge-warning">جزئي</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('sales_invoices.show', $invoice->id) }}" class="btn btn-xs btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Totals + Notes --}}
    <div class="row">
        <div class="col-md-6">
            @if($order->notes)
            <div class="card card-outline card-secondary">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-sticky-note ml-2"></i> ملاحظات</h3></div>
                <div class="card-body"><p class="text-muted mb-0">{{ $order->notes }}</p></div>
            </div>
            @endif
        </div>
        <div class="col-md-6">
            <div class="card card-outline card-primary">
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><td class="text-muted">الإجمالي الفرعي:</td><td class="text-left"><strong>{{ number_format($order->subtotal ?? 0, 2) }}</strong></td></tr>
                        <tr><td class="text-muted">الخصم:</td><td class="text-left text-danger">- {{ number_format($order->discount_amount ?? 0, 2) }}</td></tr>
                        <tr><td class="text-muted">الضريبة ({{ $order->tax_rate ?? 14 }}%):</td><td class="text-left">{{ number_format($order->tax_amount ?? 0, 2) }}</td></tr>
                        <tr class="border-top">
                            <td><strong class="h6">الإجمالي الكلي:</strong></td>
                            <td class="text-left"><strong class="h5 text-primary">{{ number_format($order->total ?? 0, 2) }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
