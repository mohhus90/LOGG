@extends('admin.layouts.purchasing')
@section('title') تقرير ملخص المشتريات @endsection
@section('start') المشتريات @endsection
@section('home') <a href="{{ route('purchase_reports.index') }}">التقارير</a> @endsection
@section('startpage') ملخص @endsection

@section('content')
<div class="col-12">

    <div class="card card-outline card-secondary mb-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-calendar-alt ml-2"></i> تصفية بالتاريخ</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('purchase_reports.summary') }}">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>من تاريخ</label>
                            <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>إلى تاريخ</label>
                            <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-group w-100">
                            <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search"></i> عرض التقرير</button>
                        </div>
                    </div>
                </div>
                @if(request()->hasAny(['from','to']))
                    <a href="{{ route('purchase_reports.summary') }}" class="btn btn-sm btn-secondary"><i class="fas fa-times"></i> إلغاء التصفية</a>
                @endif
            </form>
        </div>
    </div>

    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-file-alt ml-2"></i> ملخص فواتير الشراء
                @if(request('from') || request('to'))
                    <small class="text-muted mr-2">
                        {{ request('from') ? 'من: ' . request('from') : '' }}
                        {{ request('to') ? ' إلى: ' . request('to') : '' }}
                    </small>
                @endif
            </h3>
            <div class="card-tools">
                <a href="{{ route('purchase_reports.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-right ml-1"></i> رجوع
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>رقم الفاتورة</th>
                            <th>التاريخ</th>
                            <th>المورد</th>
                            <th>الإجمالي الفرعي</th>
                            <th>الخصم</th>
                            <th>الضريبة</th>
                            <th>الإجمالي</th>
                            <th>المدفوع</th>
                            <th>المتبقي</th>
                            <th>حالة السداد</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $invoice)
                        <tr>
                            <td>
                                <a href="{{ route('purchase_invoices.show', $invoice->id) }}" class="text-primary">
                                    <strong>#{{ $invoice->invoice_number }}</strong>
                                </a>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($invoice->date)->format('Y/m/d') }}</td>
                            <td>{{ $invoice->supplier->name ?? '-' }}</td>
                            <td>{{ number_format($invoice->subtotal ?? 0, 2) }}</td>
                            <td>{{ number_format($invoice->discount_amount ?? 0, 2) }}</td>
                            <td>{{ number_format($invoice->tax_amount ?? 0, 2) }}</td>
                            <td><strong>{{ number_format($invoice->total ?? 0, 2) }}</strong></td>
                            <td class="text-success">{{ number_format($invoice->paid_amount ?? 0, 2) }}</td>
                            <td class="{{ ($invoice->remaining_amount ?? 0) > 0 ? 'text-danger font-weight-bold' : 'text-success' }}">
                                {{ number_format($invoice->remaining_amount ?? 0, 2) }}
                            </td>
                            <td>{!! $invoice->payment_status_label !!}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                لا توجد فواتير في هذه الفترة
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if(isset($totals) && count($data) > 0)
                    <tfoot class="bg-light font-weight-bold">
                        <tr class="table-info">
                            <td colspan="3" class="text-left">الإجمالي</td>
                            <td>{{ number_format($totals['subtotal'] ?? 0, 2) }}</td>
                            <td>{{ number_format($totals['discount'] ?? 0, 2) }}</td>
                            <td>{{ number_format($totals['tax'] ?? 0, 2) }}</td>
                            <td>{{ number_format($totals['total'] ?? 0, 2) }}</td>
                            <td class="text-success">{{ number_format($totals['paid'] ?? 0, 2) }}</td>
                            <td class="{{ ($totals['remaining'] ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($totals['remaining'] ?? 0, 2) }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
        <div class="card-footer">
            @if(method_exists($data, 'links'))
                {{ $data->appends(request()->query())->links() }}
            @endif
        </div>
    </div>
</div>
@endsection
