@extends('admin.layouts.inventory')
@section('title') تحويل {{ $transfer->transfer_number }} @endsection
@section('start') المخازن @endsection
@section('home') <a href="{{ route('stock_transfers.index') }}">تحويلات المخازن</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">

    <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:8px">
        <div>
            @if($transfer->status === 'draft')
            <form action="{{ route('stock_transfers.complete', $transfer->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('تنفيذ هذا التحويل سيحدّث أرصدة المخزون فورًا. متابعة؟')">
                    <i class="fas fa-check ml-1"></i> تنفيذ التحويل
                </button>
            </form>
            <form action="{{ route('stock_transfers.cancel', $transfer->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('إلغاء هذا التحويل؟')">
                    <i class="fas fa-ban ml-1"></i> إلغاء
                </button>
            </form>
            <a href="{{ route('stock_transfers.delete', $transfer->id) }}" class="btn btn-danger btn-sm"
               onclick="return confirm('حذف هذا التحويل؟')">
                <i class="fas fa-trash ml-1"></i> حذف
            </a>
            @endif
        </div>
        <div>{!! $transfer->status_label !!}</div>
    </div>

    <div class="card card-outline card-warning">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-dolly ml-2"></i> بيانات التحويل</h3></div>
        <div class="card-body">
            <table class="table table-sm table-borderless mb-0">
                <tr><th style="width:140px" class="text-muted">رقم التحويل</th><td><strong>{{ $transfer->transfer_number }}</strong></td></tr>
                <tr><th class="text-muted">التاريخ</th><td>{{ \Carbon\Carbon::parse($transfer->date)->format('Y/m/d') }}</td></tr>
                <tr><th class="text-muted">من مخزن</th><td>{{ $transfer->fromWarehouse->name ?? '—' }}</td></tr>
                <tr><th class="text-muted">إلى مخزن</th><td>{{ $transfer->toWarehouse->name ?? '—' }}</td></tr>
                <tr><th class="text-muted">أنشئ بواسطة</th><td>{{ $transfer->createdBy->name ?? '—' }}</td></tr>
                @if($transfer->notes)
                <tr><th class="text-muted">ملاحظات</th><td>{{ $transfer->notes }}</td></tr>
                @endif
            </table>
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
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transfer->items as $i => $row)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td><strong>{{ $row->item->name ?? '—' }}</strong></td>
                            <td class="text-center">{{ number_format($row->quantity, 3) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">لا توجد أصناف</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
