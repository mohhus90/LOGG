@extends('admin.layouts.sales')
@section('title') جلسة كاشير #{{ $session->id }} @endsection
@section('start') المبيعات @endsection
@section('home') <a href="{{ route('pos_sessions.index') }}">جلسات الكاشير</a> @endsection
@section('startpage') جلسة #{{ $session->id }} @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-receipt ml-2"></i> تفاصيل الجلسة #{{ $session->id }}</h3>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3"><small class="text-muted d-block">الكاشير</small><strong>{{ $session->register->name ?? '-' }}</strong></div>
                <div class="col-md-3"><small class="text-muted d-block">فُتحت بواسطة</small><strong>{{ $session->openedBy->name ?? '-' }}</strong></div>
                <div class="col-md-3"><small class="text-muted d-block">وقت الفتح</small><strong>{{ optional($session->opened_at)->format('Y-m-d H:i') }}</strong></div>
                <div class="col-md-3"><small class="text-muted d-block">الحالة</small>
                    @if($session->status === 'open')<span class="badge badge-success">مفتوحة</span>
                    @else<span class="badge badge-secondary">مغلقة</span>@endif
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-3"><small class="text-muted d-block">الافتتاحية</small><strong>{{ number_format($session->opening_amount, 2) }}</strong></div>
                <div class="col-md-3"><small class="text-muted d-block">إجمالي المبيعات</small><strong>{{ number_format($session->sales_total, 2) }}</strong></div>
                <div class="col-md-3"><small class="text-muted d-block">المتوقع/الفعلي</small>
                    <strong>{{ $session->expected_closing_amount !== null ? number_format($session->expected_closing_amount, 2) : '-' }} / {{ $session->counted_closing_amount !== null ? number_format($session->counted_closing_amount, 2) : '-' }}</strong>
                </div>
                <div class="col-md-3"><small class="text-muted d-block">الفرق</small>
                    <strong>{{ $session->difference !== null ? number_format($session->difference, 2) : '-' }}</strong>
                </div>
            </div>

            <h5 class="mt-4">فواتير البيع فى هذه الجلسة</h5>
            <table class="table table-bordered table-sm">
                <thead class="thead-light"><tr><th>#</th><th>رقم الفاتورة</th><th>العميل</th><th>الإجمالي</th><th></th></tr></thead>
                <tbody>
                    @forelse($session->invoices as $inv)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $inv->invoice_number }}</td>
                        <td>{{ $inv->customer->name ?? '-' }}</td>
                        <td>{{ number_format($inv->total, 2) }}</td>
                        <td><a href="{{ route('sales_invoices.print', $inv->id) }}" target="_blank" class="btn btn-xs btn-info"><i class="fas fa-print"></i></a></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">لا توجد فواتير</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            @if($session->status === 'open')
                <a href="{{ route('pos.terminal', $session->register_id) }}" class="btn btn-primary btn-sm">متابعة البيع</a>
                <a href="{{ route('pos_sessions.close_form', $session->id) }}" class="btn btn-danger btn-sm">إغلاق الجلسة</a>
            @endif
        </div>
    </div>
</div>
@endsection
