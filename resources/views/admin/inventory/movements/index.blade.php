@extends('admin.layouts.inventory')
@section('title') حركة الأصناف @endsection
@section('start') المخازن @endsection
@section('home') <a href="{{ route('stock_movements.index') }}">حركة الأصناف</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">
                <i class="fas fa-exchange-alt ml-2"></i>
                سجل حركة الأصناف
            </h3>
        </div>

        <div class="card-body pb-0">
            <form method="GET" action="{{ route('stock_movements.index') }}" class="form-inline mb-3 flex-wrap">
                <select name="warehouse_id" class="form-control ml-2 mb-1">
                    <option value="">-- كل المخازن --</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                    @endforeach
                </select>
                <select name="item_id" class="form-control select2 ml-2 mb-1" style="min-width:220px">
                    <option value="">-- كل الأصناف --</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}" {{ request('item_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                    @endforeach
                </select>
                <select name="movement_type" class="form-control ml-2 mb-1">
                    <option value="">-- كل الأنواع --</option>
                    <option value="purchase_in" {{ request('movement_type') == 'purchase_in' ? 'selected' : '' }}>وارد شراء</option>
                    <option value="purchase_return_out" {{ request('movement_type') == 'purchase_return_out' ? 'selected' : '' }}>مرتجع شراء</option>
                    <option value="sales_out" {{ request('movement_type') == 'sales_out' ? 'selected' : '' }}>صادر بيع</option>
                    <option value="sales_return_in" {{ request('movement_type') == 'sales_return_in' ? 'selected' : '' }}>مرتجع بيع</option>
                    <option value="adjustment_in" {{ request('movement_type') == 'adjustment_in' ? 'selected' : '' }}>تسوية زيادة</option>
                    <option value="adjustment_out" {{ request('movement_type') == 'adjustment_out' ? 'selected' : '' }}>تسوية نقص</option>
                    <option value="transfer_in" {{ request('movement_type') == 'transfer_in' ? 'selected' : '' }}>تحويل وارد</option>
                    <option value="transfer_out" {{ request('movement_type') == 'transfer_out' ? 'selected' : '' }}>تحويل صادر</option>
                </select>
                <input type="date" name="from" class="form-control ml-2 mb-1" value="{{ request('from') }}">
                <input type="date" name="to" class="form-control ml-2 mb-1" value="{{ request('to') }}">
                <button type="submit" class="btn btn-primary ml-2 mb-1"><i class="fas fa-search"></i> بحث</button>
                <a href="{{ route('stock_movements.index') }}" class="btn btn-secondary mb-1">مسح</a>
            </form>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>التاريخ</th>
                            <th>الصنف</th>
                            <th>المخزن</th>
                            <th>نوع الحركة</th>
                            <th class="text-left">الكمية</th>
                            <th class="text-left">الرصيد بعدها</th>
                            <th>ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $mov)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ \Carbon\Carbon::parse($mov->date)->format('Y/m/d') }}</td>
                            <td>{{ $mov->item->name ?? '—' }}</td>
                            <td>{{ $mov->warehouse->name ?? '—' }}</td>
                            <td>{!! $mov->type_label !!}</td>
                            <td class="text-left">{{ number_format($mov->signed_quantity, 2) }}</td>
                            <td class="text-left">{{ number_format($mov->balance_after, 2) }}</td>
                            <td><small class="text-muted">{{ $mov->notes ?? '—' }}</small></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-exchange-alt fa-2x mb-2 d-block"></i>
                                لا توجد حركات مسجلة
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
