@extends('admin.layouts.inventory')
@section('title') تقييم المخزون @endsection
@section('start') المخازن @endsection
@section('home') <a href="{{ route('inventory_reports.index') }}">التقارير</a> @endsection
@section('startpage') تقييم @endsection

@section('content')
<div class="col-12">

    <div class="row mb-3">
        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($totalValue, 2) }} <small style="font-size:14px">ج.م</small></h3>
                    <p>إجمالي قيمة المخزون</p>
                </div>
                <div class="icon"><i class="fas fa-coins"></i></div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-secondary mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('inventory_reports.valuation') }}" class="form-inline">
                <select name="warehouse_id" class="form-control ml-2">
                    <option value="">-- كل المخازن --</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> عرض</button>
            </form>
        </div>
    </div>

    <div class="card card-success card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-coins ml-2"></i> تقييم المخزون بالتكلفة</h3>
            <div class="card-tools">
                <a href="{{ route('inventory_reports.index') }}" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-right ml-1"></i> رجوع</a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>كود الصنف</th>
                            <th>اسم الصنف</th>
                            <th>المخزن</th>
                            <th class="text-center">الكمية</th>
                            <th class="text-left">تكلفة الوحدة</th>
                            <th class="text-left">القيمة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $row)
                        <tr>
                            <td><code>{{ $row->code ?? '—' }}</code></td>
                            <td><strong>{{ $row->name }}</strong></td>
                            <td>{{ $row->warehouse_name }}</td>
                            <td class="text-center">{{ number_format($row->quantity, 2) }}</td>
                            <td class="text-left">{{ number_format($row->cost_price, 2) }}</td>
                            <td class="text-left"><strong>{{ number_format($row->value, 2) }}</strong></td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">لا توجد بيانات</td></tr>
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
