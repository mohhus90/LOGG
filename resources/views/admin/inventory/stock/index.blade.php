@extends('admin.layouts.inventory')
@section('title') أرصدة المخزون @endsection
@section('start') المخازن @endsection
@section('home') <a href="{{ route('stock_levels.index') }}">أرصدة المخزون</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">
                <i class="fas fa-cubes ml-2"></i>
                أرصدة المخزون الحالية
            </h3>
        </div>

        <div class="card-body pb-0">
            <form method="GET" action="{{ route('stock_levels.index') }}" class="form-inline mb-3 flex-wrap">
                <input type="text" name="search" class="form-control ml-2 mb-1"
                    placeholder="بحث باسم الصنف..." value="{{ request('search') }}" style="min-width:240px">
                <select name="warehouse_id" class="form-control ml-2 mb-1">
                    <option value="">-- كل المخازن --</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                    @endforeach
                </select>
                <div class="custom-control custom-checkbox ml-2 mb-1">
                    <input type="checkbox" class="custom-control-input" id="low_stock" name="low_stock" value="1" {{ request('low_stock') ? 'checked' : '' }}>
                    <label class="custom-control-label" for="low_stock">نقص المخزون فقط</label>
                </div>
                <button type="submit" class="btn btn-primary ml-2 mb-1"><i class="fas fa-search"></i> بحث</button>
                <a href="{{ route('stock_levels.index') }}" class="btn btn-secondary mb-1">مسح</a>
            </form>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>كود الصنف</th>
                            <th>اسم الصنف</th>
                            <th>حد إعادة الطلب</th>
                            <th>الرصيد الحالي</th>
                            <th>الحالة</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $item)
                        <tr class="{{ $item->total_stock <= $item->reorder_level ? 'table-danger' : '' }}">
                            <td>{{ $loop->iteration }}</td>
                            <td><code>{{ $item->code ?? '—' }}</code></td>
                            <td>
                                <a href="{{ route('stock_levels.show', $item->id) }}"><strong>{{ $item->name }}</strong></a>
                            </td>
                            <td>{{ number_format($item->reorder_level, 2) }}</td>
                            <td><strong>{{ number_format($item->total_stock, 2) }}</strong></td>
                            <td>
                                @if($item->total_stock <= $item->reorder_level)
                                    <span class="badge badge-danger"><i class="fas fa-exclamation-circle ml-1"></i>نقص</span>
                                @else
                                    <span class="badge badge-success">متوفر</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('stock_levels.show', $item->id) }}" class="btn btn-xs btn-info" title="التفاصيل">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-cubes fa-2x mb-2 d-block"></i>
                                لا توجد بيانات
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $data->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
