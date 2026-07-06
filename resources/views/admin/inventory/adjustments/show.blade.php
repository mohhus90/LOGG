@extends('admin.layouts.inventory')
@section('title') تسوية {{ $adjustment->adjustment_number }} @endsection
@section('start') المخازن @endsection
@section('home') <a href="{{ route('stock_adjustments.index') }}">تسويات المخزون</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">

    <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:8px">
        <div>
            @if($adjustment->status === 'draft')
            <form action="{{ route('stock_adjustments.approve', $adjustment->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('اعتماد هذه التسوية سيحدّث أرصدة المخزون فورًا. متابعة؟')">
                    <i class="fas fa-check ml-1"></i> اعتماد
                </button>
            </form>
            <form action="{{ route('stock_adjustments.reject', $adjustment->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('رفض هذه التسوية؟')">
                    <i class="fas fa-ban ml-1"></i> رفض
                </button>
            </form>
            <a href="{{ route('stock_adjustments.delete', $adjustment->id) }}" class="btn btn-danger btn-sm"
               onclick="return confirm('حذف هذه التسوية؟')">
                <i class="fas fa-trash ml-1"></i> حذف
            </a>
            @endif
        </div>
        <div>{!! $adjustment->status_label !!}</div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-warning">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-balance-scale ml-2"></i> بيانات التسوية</h3></div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th style="width:140px" class="text-muted">رقم التسوية</th><td><strong>{{ $adjustment->adjustment_number }}</strong></td></tr>
                        <tr><th class="text-muted">التاريخ</th><td>{{ \Carbon\Carbon::parse($adjustment->date)->format('Y/m/d') }}</td></tr>
                        <tr><th class="text-muted">المخزن</th><td>{{ $adjustment->warehouse->name ?? '—' }}</td></tr>
                        <tr><th class="text-muted">النوع</th><td>{{ $adjustment->type_label }}</td></tr>
                        <tr><th class="text-muted">أنشئ بواسطة</th><td>{{ $adjustment->createdBy->name ?? '—' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-light h-100">
                <div class="card-body">
                    <h6 class="font-weight-bold mb-2"><i class="fas fa-comment-alt ml-2 text-warning"></i>سبب التسوية</h6>
                    <p class="mb-0">{{ $adjustment->reason ?? '-' }}</p>
                    @if($adjustment->notes)
                        <hr>
                        <h6 class="font-weight-bold mb-2"><i class="fas fa-sticky-note ml-2 text-info"></i>ملاحظات</h6>
                        <p class="mb-0">{{ $adjustment->notes }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-secondary">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-list ml-2"></i> الأصناف</h3></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>الصنف</th>
                            <th class="text-center">الكمية</th>
                            <th class="text-left">تكلفة الوحدة</th>
                            <th>ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($adjustment->items as $i => $row)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td><strong>{{ $row->item->name ?? '—' }}</strong></td>
                            <td class="text-center">{{ number_format($row->quantity, 3) }}</td>
                            <td class="text-left">{{ number_format($row->unit_cost ?? 0, 2) }}</td>
                            <td>{{ $row->notes ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">لا توجد أصناف</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
