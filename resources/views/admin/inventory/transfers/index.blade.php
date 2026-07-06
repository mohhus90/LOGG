@extends('admin.layouts.inventory')
@section('title') تحويلات المخازن @endsection
@section('start') المخازن @endsection
@section('home') <a href="{{ route('stock_transfers.index') }}">تحويلات المخازن</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">
                <i class="fas fa-dolly ml-2"></i>
                تحويلات المخازن
                <a href="{{ route('stock_transfers.create') }}" class="btn btn-sm btn-success mr-2">
                    <i class="fas fa-plus"></i> تحويل جديد
                </a>
            </h3>
        </div>

        <div class="card-body pb-0">
            <form method="GET" action="{{ route('stock_transfers.index') }}" class="form-inline mb-3 flex-wrap">
                <select name="status" class="form-control ml-2 mb-1">
                    <option value="">-- الحالة --</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>منفذ</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                </select>
                <button type="submit" class="btn btn-primary ml-2 mb-1"><i class="fas fa-search"></i> بحث</button>
                <a href="{{ route('stock_transfers.index') }}" class="btn btn-secondary mb-1">مسح</a>
            </form>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>رقم التحويل</th>
                            <th>التاريخ</th>
                            <th>من مخزن</th>
                            <th>إلى مخزن</th>
                            <th>الحالة</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $transfer)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><strong>{{ $transfer->transfer_number }}</strong></td>
                            <td>{{ optional($transfer->date)->format('Y-m-d') }}</td>
                            <td>{{ $transfer->fromWarehouse->name ?? '—' }}</td>
                            <td>{{ $transfer->toWarehouse->name ?? '—' }}</td>
                            <td>{!! $transfer->status_label !!}</td>
                            <td>
                                <a href="{{ route('stock_transfers.show', $transfer->id) }}" class="btn btn-xs btn-info" title="عرض"><i class="fas fa-eye"></i></a>
                                @if($transfer->status === 'draft')
                                <a href="{{ route('stock_transfers.delete', $transfer->id) }}" class="btn btn-xs btn-danger" title="حذف"
                                   onclick="return confirm('هل تريد حذف هذا التحويل؟')"><i class="fas fa-trash"></i></a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-dolly fa-2x mb-2 d-block"></i>لا توجد تحويلات مسجلة</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $data->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
