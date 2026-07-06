@extends('admin.layouts.inventory')
@section('title') تنبيهات نقص المخزون @endsection
@section('start') المخازن @endsection
@section('home') <a href="{{ route('inventory_reports.index') }}">التقارير</a> @endsection
@section('startpage') نقص المخزون @endsection

@section('content')
<div class="col-12">

    <div class="card card-danger card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-exclamation-circle ml-2"></i> الأصناف تحت حد إعادة الطلب</h3>
            <div class="card-tools">
                <a href="{{ route('inventory_reports.index') }}" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-right ml-1"></i> رجوع</a>
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
                            <th>الرصيد الحالي</th>
                            <th>حد إعادة الطلب</th>
                            <th>العجز</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $row)
                        <tr class="table-danger">
                            <td>{{ $loop->iteration }}</td>
                            <td><code>{{ $row->code ?? '—' }}</code></td>
                            <td>
                                <a href="{{ route('stock_levels.show', $row->id) }}"><strong>{{ $row->name }}</strong></a>
                            </td>
                            <td>{{ number_format($row->total_stock, 2) }}</td>
                            <td>{{ number_format($row->reorder_level, 2) }}</td>
                            <td class="text-danger font-weight-bold">{{ number_format(max(0, $row->reorder_level - $row->total_stock), 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted"><i class="fas fa-check-circle fa-3x text-success mb-3 d-block"></i>جميع الأصناف ضمن الحدود الآمنة</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $data->links() }}
        </div>
    </div>
</div>
@endsection
