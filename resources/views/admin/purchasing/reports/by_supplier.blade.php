@extends('admin.layouts.purchasing')
@section('title') تقرير المشتريات بالمورد @endsection
@section('start') المشتريات @endsection
@section('home') <a href="{{ route('purchase_reports.index') }}">التقارير</a> @endsection
@section('startpage') بالمورد @endsection

@section('content')
<div class="col-12">

    <div class="card card-outline card-secondary mb-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-search ml-2"></i> بحث عن مورد</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('purchase_reports.supplier') }}">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <input type="text" name="search" class="form-control"
                                   value="{{ request('search') }}"
                                   placeholder="ابحث باسم المورد أو الكود...">
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-start">
                        <button type="submit" class="btn btn-primary ml-2"><i class="fas fa-search"></i> بحث</button>
                        @if(request('search'))
                            <a href="{{ route('purchase_reports.supplier') }}" class="btn btn-secondary"><i class="fas fa-times"></i> مسح</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-success card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-truck ml-2"></i> تقرير المشتريات بالمورد</h3>
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
                            <th>#</th>
                            <th>المورد</th>
                            <th>إجمالي الفواتير</th>
                            <th>المدفوع</th>
                            <th>المتبقي (مستحق)</th>
                            <th>عدد الفواتير</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <strong>{{ $row->name ?? '-' }}</strong>
                                @if($row->code ?? null)
                                    <br><small class="text-muted">{{ $row->code }}</small>
                                @endif
                            </td>
                            <td><strong>{{ number_format($row->total_invoiced ?? 0, 2) }} ج.م</strong></td>
                            <td class="text-success">{{ number_format($row->total_paid ?? 0, 2) }} ج.م</td>
                            <td class="{{ ($row->total_remaining ?? 0) > 0 ? 'text-danger font-weight-bold' : 'text-success' }}">
                                {{ number_format($row->total_remaining ?? 0, 2) }} ج.م
                                @if(($row->total_remaining ?? 0) > 0)
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-info badge-lg">
                                    {{ number_format($row->invoice_count ?? 0) }}
                                </span>
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
