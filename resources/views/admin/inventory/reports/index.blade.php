@extends('admin.layouts.inventory')
@section('title') تقارير المخازن @endsection
@section('start') المخازن @endsection
@section('home') <a href="{{ route('inventory_reports.index') }}">التقارير</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">

    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($stats['total_stock_value'] ?? 0, 2) }}<small style="font-size:13px"> ج.م</small></h3>
                    <p>قيمة المخزون الإجمالية</p>
                </div>
                <div class="icon"><i class="fas fa-coins"></i></div>
                <a href="{{ route('inventory_reports.valuation') }}" class="small-box-footer">
                    التفاصيل <i class="fas fa-arrow-circle-left"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ number_format($stats['low_stock_count'] ?? 0) }}</h3>
                    <p>أصناف تحت حد إعادة الطلب</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                <a href="{{ route('inventory_reports.low_stock') }}" class="small-box-footer">
                    التفاصيل <i class="fas fa-arrow-circle-left"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ number_format($stats['warehouses_count'] ?? 0) }}</h3>
                    <p>عدد المخازن</p>
                </div>
                <div class="icon"><i class="fas fa-warehouse"></i></div>
                <a href="{{ route('warehouses.index') }}" class="small-box-footer">
                    عرض المخازن <i class="fas fa-arrow-circle-left"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($stats['movements_this_month'] ?? 0) }}</h3>
                    <p>حركات هذا الشهر</p>
                </div>
                <div class="icon"><i class="fas fa-exchange-alt"></i></div>
                <a href="{{ route('inventory_reports.movements_summary') }}" class="small-box-footer">
                    التفاصيل <i class="fas fa-arrow-circle-left"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="card card-danger card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-exclamation-circle ml-2"></i> أصناف تحتاج إعادة طلب</h3>
            <div class="card-tools">
                <a href="{{ route('inventory_reports.low_stock') }}" class="btn btn-sm btn-danger">عرض الكل</a>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>كود الصنف</th>
                        <th>اسم الصنف</th>
                        <th>الرصيد الحالي</th>
                        <th>حد إعادة الطلب</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lowStockItems as $item)
                    <tr class="table-danger">
                        <td><code>{{ $item->code ?? '—' }}</code></td>
                        <td><strong>{{ $item->name }}</strong></td>
                        <td>{{ number_format($item->total_stock, 2) }}</td>
                        <td>{{ number_format($item->reorder_level, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted py-3"><i class="fas fa-check-circle text-success ml-1"></i> جميع الأصناف ضمن الحدود الآمنة</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="card card-widget widget-user-2 shadow">
                <div class="card-footer p-0">
                    <a href="{{ route('inventory_reports.valuation') }}" class="btn btn-block btn-outline-success py-3">
                        <i class="fas fa-coins fa-2x d-block mb-2"></i>
                        <strong>تقييم المخزون</strong>
                        <br><small class="text-muted">قيمة كل صنف بكل مخزن</small>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-widget widget-user-2 shadow">
                <div class="card-footer p-0">
                    <a href="{{ route('inventory_reports.low_stock') }}" class="btn btn-block btn-outline-danger py-3">
                        <i class="fas fa-exclamation-circle fa-2x d-block mb-2"></i>
                        <strong>تنبيهات النقص</strong>
                        <br><small class="text-muted">الأصناف تحت حد الطلب</small>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-widget widget-user-2 shadow">
                <div class="card-footer p-0">
                    <a href="{{ route('inventory_reports.movements_summary') }}" class="btn btn-block btn-outline-info py-3">
                        <i class="fas fa-list-alt fa-2x d-block mb-2"></i>
                        <strong>ملخص الحركة</strong>
                        <br><small class="text-muted">إجمالي كل نوع حركة</small>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-widget widget-user-2 shadow">
                <div class="card-footer p-0">
                    <a href="{{ route('stock_movements.index') }}" class="btn btn-block btn-outline-secondary py-3">
                        <i class="fas fa-exchange-alt fa-2x d-block mb-2"></i>
                        <strong>سجل الحركة</strong>
                        <br><small class="text-muted">جميع حركات المخزون</small>
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
