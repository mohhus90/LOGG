@extends('admin.layouts.inventory')
@section('title') رصيد الصنف — {{ $item->name }} @endsection
@section('start') المخازن @endsection
@section('home') <a href="{{ route('stock_levels.index') }}">أرصدة المخزون</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">

    <div class="mb-3">
        <a href="{{ route('stock_levels.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-right ml-1"></i> رجوع
        </a>
    </div>

    <div class="row">
        <div class="col-md-5">
            <div class="card card-outline card-primary">
                <div class="card-header py-2">
                    <h6 class="card-title mb-0"><i class="fas fa-box ml-1"></i> بيانات الصنف</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th class="text-muted" style="width:140px">الاسم</th><td><strong>{{ $item->name }}</strong></td></tr>
                        <tr><th class="text-muted">الكود</th><td>{{ $item->code ?? '—' }}</td></tr>
                        <tr><th class="text-muted">المجموعة</th><td>{{ $item->category->name ?? '—' }}</td></tr>
                        <tr><th class="text-muted">الوحدة</th><td>{{ $item->unit->name ?? '—' }}</td></tr>
                        <tr><th class="text-muted">حد إعادة الطلب</th><td>{{ number_format($item->reorder_level, 2) }}</td></tr>
                        <tr><th class="text-muted">إجمالي الرصيد</th><td><strong class="{{ $balances->sum('quantity') <= $item->reorder_level ? 'text-danger' : 'text-success' }}">{{ number_format($balances->sum('quantity'), 2) }}</strong></td></tr>
                    </table>
                </div>
            </div>

            <div class="card card-outline card-secondary">
                <div class="card-header py-2">
                    <h6 class="card-title mb-0"><i class="fas fa-warehouse ml-1"></i> الرصيد حسب المخزن</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="thead-light">
                            <tr><th>المخزن</th><th class="text-left">الكمية</th></tr>
                        </thead>
                        <tbody>
                            @forelse($balances as $bal)
                            <tr>
                                <td>{{ $bal->warehouse->name ?? '—' }}</td>
                                <td class="text-left">{{ number_format($bal->quantity, 2) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-center text-muted py-2">لا يوجد رصيد مسجل</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card card-outline card-info">
                <div class="card-header py-2">
                    <h6 class="card-title mb-0"><i class="fas fa-history ml-1"></i> آخر 30 حركة</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>التاريخ</th>
                                    <th>المخزن</th>
                                    <th>نوع الحركة</th>
                                    <th class="text-left">الكمية</th>
                                    <th class="text-left">الرصيد بعدها</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($movements as $mov)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($mov->date)->format('Y/m/d') }}</td>
                                    <td>{{ $mov->warehouse->name ?? '—' }}</td>
                                    <td>{!! $mov->type_label !!}</td>
                                    <td class="text-left">{{ number_format($mov->signed_quantity, 2) }}</td>
                                    <td class="text-left">{{ number_format($mov->balance_after, 2) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center text-muted py-2">لا توجد حركات مسجلة</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
