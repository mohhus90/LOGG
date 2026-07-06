@extends('admin.layouts.treasury')
@section('title') سندات القبض @endsection
@section('start') الخزينة @endsection
@section('home') <a href="{{ route('treasury_receipts.index') }}">سندات القبض</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-hand-holding-usd ml-2"></i> سندات القبض</h3>
            <div class="card-tools">
                <a href="{{ route('treasury_receipts.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> سند قبض جديد</a>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr><th>رقم السند</th><th>التاريخ</th><th>الطرف</th><th>طريقة السداد</th><th>المبلغ</th><th>الحالة</th><th>إجراء</th></tr>
                </thead>
                <tbody>
                    @forelse($data as $voucher)
                    <tr>
                        <td>{{ $voucher->voucher_number }}</td>
                        <td>{{ \Carbon\Carbon::parse($voucher->date)->format('Y-m-d') }}</td>
                        <td>{{ $voucher->party_name }}</td>
                        <td>{{ $voucher->payment_method }}</td>
                        <td>{{ number_format($voucher->amount, 2) }}</td>
                        <td><span class="badge badge-success">{{ $voucher->status_label }}</span></td>
                        <td><a href="{{ route('treasury_receipts.show', $voucher->id) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a></td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">لا توجد سندات قبض</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $data->links() }}</div>
    </div>
</div>
@endsection
