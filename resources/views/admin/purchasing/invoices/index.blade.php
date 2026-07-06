@extends('admin.layouts.purchasing')
@section('title') فواتير الشراء @endsection
@section('start') المشتريات @endsection
@section('home') <a href="{{ route('purchase_invoices.index') }}">الفواتير</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">

    <div class="row mb-3">
        <div class="col-md-3">
            <div class="info-box bg-primary">
                <span class="info-box-icon"><i class="fas fa-file-invoice"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">إجمالي الفواتير</span>
                    <span class="info-box-number">{{ number_format($totals['total'] ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-success">
                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">المدفوع</span>
                    <span class="info-box-number">{{ number_format($totals['paid'] ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-danger">
                <span class="info-box-icon"><i class="fas fa-exclamation-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">المتبقي</span>
                    <span class="info-box-number">{{ number_format($totals['remaining'] ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-secondary">
                <span class="info-box-icon"><i class="fas fa-list"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">عدد الفواتير</span>
                    <span class="info-box-number">{{ $totals['count'] ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">
                <i class="fas fa-file-invoice-dollar ml-2"></i>
                فواتير الشراء
                <a href="{{ route('purchase_invoices.create') }}" class="btn btn-sm btn-success mr-2">
                    <i class="fas fa-plus"></i> فاتورة جديدة
                </a>
            </h3>
        </div>

        <div class="card-body pb-0">
            <form method="GET" action="{{ route('purchase_invoices.index') }}" class="form-inline mb-3 flex-wrap">
                <select name="supplier_id" class="form-control ml-2 mb-1">
                    <option value="">-- المورد --</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                    @endforeach
                </select>
                <select name="payment_status" class="form-control ml-2 mb-1">
                    <option value="">-- حالة السداد --</option>
                    <option value="unpaid"  {{ request('payment_status') == 'unpaid'  ? 'selected' : '' }}>غير مسدد</option>
                    <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>مسدد جزئياً</option>
                    <option value="paid"    {{ request('payment_status') == 'paid'    ? 'selected' : '' }}>مسدد بالكامل</option>
                </select>
                <input type="date" name="from" class="form-control ml-2 mb-1" value="{{ request('from') }}">
                <input type="date" name="to" class="form-control ml-2 mb-1" value="{{ request('to') }}">
                <button type="submit" class="btn btn-primary ml-2 mb-1"><i class="fas fa-search"></i> بحث</button>
                <a href="{{ route('purchase_invoices.index') }}" class="btn btn-secondary mb-1">مسح</a>
            </form>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>رقم الفاتورة</th>
                            <th>التاريخ</th>
                            <th>المورد</th>
                            <th>الإجمالي</th>
                            <th>المدفوع</th>
                            <th>المتبقي</th>
                            <th>حالة السداد</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $invoice)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><strong>{{ $invoice->invoice_number }}</strong></td>
                            <td>{{ optional($invoice->date)->format('Y-m-d') }}</td>
                            <td>{{ $invoice->supplier->name ?? '—' }}</td>
                            <td class="text-primary">{{ number_format($invoice->total, 2) }}</td>
                            <td class="text-success">{{ number_format($invoice->paid_amount, 2) }}</td>
                            <td class="{{ $invoice->remaining_amount > 0 ? 'text-danger font-weight-bold' : 'text-muted' }}">{{ number_format($invoice->remaining_amount, 2) }}</td>
                            <td>{!! $invoice->payment_status_label !!}</td>
                            <td>
                                <a href="{{ route('purchase_invoices.show', $invoice->id) }}" class="btn btn-xs btn-info" title="عرض"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('purchase_invoices.edit', $invoice->id) }}" class="btn btn-xs btn-warning" title="تعديل"><i class="fas fa-edit"></i></a>
                                <a href="{{ route('purchase_invoices.print', $invoice->id) }}" class="btn btn-xs btn-secondary" target="_blank" title="طباعة"><i class="fas fa-print"></i></a>
                                <form action="{{ route('purchase_invoices.cancel', $invoice->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('إلغاء هذه الفاتورة؟')">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-dark" title="إلغاء"><i class="fas fa-ban"></i></button>
                                </form>
                                <a href="{{ route('purchase_invoices.delete', $invoice->id) }}" class="btn btn-xs btn-danger" title="حذف"
                                   onclick="return confirm('هل تريد حذف هذه الفاتورة؟')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="text-center text-muted py-4"><i class="fas fa-file-invoice fa-2x mb-2 d-block"></i>لا توجد فواتير شراء</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $data->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
