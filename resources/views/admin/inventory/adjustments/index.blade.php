@extends('admin.layouts.inventory')
@section('title') تسويات المخزون @endsection
@section('start') المخازن @endsection
@section('home') <a href="{{ route('stock_adjustments.index') }}">تسويات المخزون</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">
                <i class="fas fa-balance-scale ml-2"></i>
                تسويات المخزون
                <a href="{{ route('stock_adjustments.create') }}" class="btn btn-sm btn-success mr-2">
                    <i class="fas fa-plus"></i> تسوية جديدة
                </a>
            </h3>
        </div>

        <div class="card-body pb-0">
            <form method="GET" action="{{ route('stock_adjustments.index') }}" class="form-inline mb-3 flex-wrap">
                <select name="warehouse_id" class="form-control ml-2 mb-1">
                    <option value="">-- كل المخازن --</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="form-control ml-2 mb-1">
                    <option value="">-- الحالة --</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>معتمد</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                </select>
                <button type="submit" class="btn btn-primary ml-2 mb-1"><i class="fas fa-search"></i> بحث</button>
                <a href="{{ route('stock_adjustments.index') }}" class="btn btn-secondary mb-1">مسح</a>
            </form>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>رقم التسوية</th>
                            <th>التاريخ</th>
                            <th>المخزن</th>
                            <th>النوع</th>
                            <th>الحالة</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $adj)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><strong>{{ $adj->adjustment_number }}</strong></td>
                            <td>{{ optional($adj->date)->format('Y-m-d') }}</td>
                            <td>{{ $adj->warehouse->name ?? '—' }}</td>
                            <td>
                                @if($adj->type === 'increase')
                                    <span class="badge badge-success">زيادة</span>
                                @else
                                    <span class="badge badge-danger">نقص</span>
                                @endif
                            </td>
                            <td>{!! $adj->status_label !!}</td>
                            <td>
                                <a href="{{ route('stock_adjustments.show', $adj->id) }}" class="btn btn-xs btn-info" title="عرض"><i class="fas fa-eye"></i></a>
                                @if($adj->status === 'draft')
                                <a href="{{ route('stock_adjustments.delete', $adj->id) }}" class="btn btn-xs btn-danger" title="حذف"
                                   onclick="return confirm('هل تريد حذف هذه التسوية؟')"><i class="fas fa-trash"></i></a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-balance-scale fa-2x mb-2 d-block"></i>لا توجد تسويات مسجلة</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $data->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
