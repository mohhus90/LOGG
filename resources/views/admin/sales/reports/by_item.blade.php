@extends('admin.layouts.sales')
@section('title') تقرير المبيعات بالصنف @endsection
@section('start') المبيعات @endsection
@section('home') <a href="{{ route('sales_reports.index') }}">التقارير</a> @endsection
@section('startpage') بالصنف @endsection

@section('content')
<div class="col-12">

    {{-- Date Range Filter --}}
    <div class="card card-outline card-secondary mb-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter ml-2"></i> تصفية بالتاريخ</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('sales_reports.item') }}">
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
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> عرض التقرير
                            </button>
                        </div>
                    </div>
                </div>
                @if(request()->hasAny(['from','to']))
                    <a href="{{ route('sales_reports.item') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-times"></i> إلغاء التصفية
                    </a>
                @endif
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card card-info card-outline">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-boxes ml-2"></i> تقرير المبيعات بالصنف
                @if(request('from') || request('to'))
                    <small class="text-muted mr-2">
                        {{ request('from') ? 'من: ' . request('from') : '' }}
                        {{ request('to') ? ' إلى: ' . request('to') : '' }}
                    </small>
                @endif
            </h3>
            <div class="card-tools">
                <a href="{{ route('sales_reports.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-right ml-1"></i> رجوع
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>كود الصنف</th>
                            <th>اسم الصنف</th>
                            <th>إجمالي الكمية المباعة</th>
                            <th>إجمالي المبلغ</th>
                            <th>عدد الفواتير</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <span class="badge badge-secondary">{{ $row->item_code ?? $row->code ?? '-' }}</span>
                            </td>
                            <td><strong>{{ $row->item_name ?? $row->name ?? '-' }}</strong></td>
                            <td>
                                <strong>{{ number_format($row->total_qty ?? $row->qty ?? 0, 2) }}</strong>
                                {{ $row->unit ?? '' }}
                            </td>
                            <td><strong>{{ number_format($row->total_amount ?? $row->total ?? 0, 2) }} ج.م</strong></td>
                            <td>
                                <span class="badge badge-info">{{ number_format($row->invoices_count ?? 0) }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                لا توجد بيانات
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if(count($data) > 0)
                    <tfoot class="bg-light font-weight-bold">
                        <tr class="table-info">
                            <td colspan="3" class="text-left">الإجمالي</td>
                            <td>{{ number_format(collect($data->items())->sum(fn($r) => $r->total_qty ?? $r->qty ?? 0), 2) }}</td>
                            <td>{{ number_format(collect($data->items())->sum(fn($r) => $r->total_amount ?? $r->total ?? 0), 2) }} ج.م</td>
                            <td>{{ collect($data->items())->sum(fn($r) => $r->invoices_count ?? 0) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $data->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
