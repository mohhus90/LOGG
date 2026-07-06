@extends('admin.layouts.sales')
@section('title') فواتير المبيعات @endsection
@section('start') المبيعات @endsection
@section('home') <a href="{{ route('sales_invoices.index') }}">الفواتير</a> @endsection
@section('startpage') عرض الكل @endsection

@section('content')
<div class="col-12">

    {{-- Stat Boxes --}}
    <div class="row mb-3">
        <div class="col-md-3 col-sm-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-primary"><i class="fas fa-file-invoice-dollar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">إجمالي الفواتير</span>
                    <span class="info-box-number">{{ number_format($stats['total_amount'] ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">المحصّل</span>
                    <span class="info-box-number">{{ number_format($stats['paid_amount'] ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">المتبقي</span>
                    <span class="info-box-number">{{ number_format($stats['remaining_amount'] ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-info"><i class="fas fa-hashtag"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">عدد الفواتير</span>
                    <span class="info-box-number">{{ $stats['count'] ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-file-invoice ml-2"></i> فواتير المبيعات</h3>
            <div class="card-tools">
                <a href="{{ route('sales_invoices.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> إضافة فاتورة
                </a>
            </div>
        </div>

        {{-- Filters --}}
        <div class="card-body border-bottom pb-3">
            <form method="GET" action="{{ route('sales_invoices.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group mb-0">
                            <label class="small text-muted">العميل</label>
                            <select name="customer_id" class="form-control form-control-sm select2">
                                <option value="">-- كل العملاء --</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="small text-muted">حالة الفاتورة</label>
                            <select name="status" class="form-control form-control-sm">
                                <option value="">-- الكل --</option>
                                <option value="draft"     {{ request('status') == 'draft'     ? 'selected' : '' }}>مسودة</option>
                                <option value="issued"    {{ request('status') == 'issued'    ? 'selected' : '' }}>مُصدَرة</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغاة</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="small text-muted">حالة السداد</label>
                            <select name="payment_status" class="form-control form-control-sm">
                                <option value="">-- الكل --</option>
                                <option value="unpaid"  {{ request('payment_status') == 'unpaid'  ? 'selected' : '' }}>غير مسدّد</option>
                                <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>مسدّد جزئياً</option>
                                <option value="paid"    {{ request('payment_status') == 'paid'    ? 'selected' : '' }}>مسدّد بالكامل</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="small text-muted">من تاريخ</label>
                            <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="small text-muted">إلى تاريخ</label>
                            <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
                        </div>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm btn-block ml-1">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12">
                        <a href="{{ route('sales_invoices.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-times ml-1"></i> إعادة تعيين الفلاتر
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th style="width:45px">#</th>
                            <th>رقم الفاتورة</th>
                            <th>التاريخ</th>
                            <th>تاريخ الاستحقاق</th>
                            <th>العميل</th>
                            <th>النوع</th>
                            <th>الإجمالي</th>
                            <th>المحصّل</th>
                            <th>المتبقي</th>
                            <th>حالة السداد</th>
                            <th>الحالة</th>
                            <th style="width:180px">إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $invoice)
                        @php
                            $paid      = $invoice->paid_amount ?? 0;
                            $remaining = max(0, $invoice->total - $paid);
                            $payStatus = $remaining <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid');
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><strong>{{ $invoice->invoice_number }}</strong></td>
                            <td>{{ \Carbon\Carbon::parse($invoice->date)->format('Y/m/d') }}</td>
                            <td>
                                @if($invoice->due_date)
                                    <span class="{{ ($payStatus !== 'paid' && \Carbon\Carbon::parse($invoice->due_date)->isPast()) ? 'text-danger font-weight-bold' : '' }}">
                                        {{ \Carbon\Carbon::parse($invoice->due_date)->format('Y/m/d') }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $invoice->customer->name ?? '—' }}</td>
                            <td>
                                @if(($invoice->invoice_type ?? '') === 'cash')
                                    <span class="badge badge-success">نقدي</span>
                                @else
                                    <span class="badge badge-info">آجل</span>
                                @endif
                            </td>
                            <td class="text-left"><strong>{{ number_format($invoice->total, 2) }}</strong></td>
                            <td class="text-left text-success">{{ number_format($paid, 2) }}</td>
                            <td class="text-left {{ $remaining > 0 ? 'text-danger' : '' }}"><strong>{{ number_format($remaining, 2) }}</strong></td>
                            <td>
                                @switch($payStatus)
                                    @case('paid')    <span class="badge badge-success">مسدّد</span> @break
                                    @case('partial') <span class="badge badge-warning">جزئي</span> @break
                                    @case('unpaid')  <span class="badge badge-danger">غير مسدّد</span> @break
                                @endswitch
                            </td>
                            <td>
                                @switch($invoice->status ?? 'issued')
                                    @case('draft')     <span class="badge badge-secondary">مسودة</span> @break
                                    @case('issued')    <span class="badge badge-primary">مُصدَرة</span> @break
                                    @case('cancelled') <span class="badge badge-danger">ملغاة</span> @break
                                    @default           <span class="badge badge-secondary">{{ $invoice->status }}</span>
                                @endswitch
                            </td>
                            <td>
                                <a href="{{ route('sales_invoices.show', $invoice->id) }}" class="btn btn-xs btn-info" title="عرض">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('sales_invoices.edit', $invoice->id) }}" class="btn btn-xs btn-warning" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('sales_invoices.print', $invoice->id) }}" class="btn btn-xs btn-secondary" target="_blank" title="طباعة">
                                    <i class="fas fa-print"></i>
                                </a>
                                @if(($invoice->status ?? '') !== 'cancelled')
                                <form action="{{ route('sales_invoices.cancel', $invoice->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('إلغاء هذه الفاتورة؟ لا يمكن التراجع عن هذا الإجراء.')">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-danger" title="إلغاء">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                </form>
                                @endif
                                <a href="{{ route('sales_invoices.delete', $invoice->id) }}" class="btn btn-xs btn-danger" title="حذف"
                                   onclick="return confirm('حذف هذه الفاتورة نهائياً؟')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                لا توجد فواتير مسجلة
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer">
            {{ $data->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    $('select.select2').select2({ language: 'ar', width: '100%' });
});
</script>
@endsection
