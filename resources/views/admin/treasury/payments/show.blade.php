@extends('admin.layouts.treasury')
@section('title') سند {{ $voucher->voucher_number }} @endsection
@section('start') الخزينة @endsection
@section('home') <a href="{{ route('treasury_payments.index') }}">سندات الصرف</a> @endsection
@section('startpage') {{ $voucher->voucher_number }} @endsection

@section('content')
<div class="col-lg-8">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-money-check ml-2"></i> سند صرف {{ $voucher->voucher_number }}</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4"><strong>التاريخ:</strong> {{ \Carbon\Carbon::parse($voucher->date)->format('Y-m-d') }}</div>
                <div class="col-md-4"><strong>المبلغ:</strong> {{ number_format($voucher->amount, 2) }}</div>
                <div class="col-md-4"><strong>طريقة السداد:</strong> {{ $voucher->payment_method }}</div>
                <div class="col-md-4 mt-2"><strong>المصروف إلى:</strong> {{ $voucher->party_name }}</div>
                <div class="col-md-4 mt-2"><strong>الخزنة/البنك:</strong> {{ $voucher->cashBox->name ?? $voucher->bankAccount->bank_name ?? '-' }}</div>
                <div class="col-md-4 mt-2"><strong>الحالة:</strong> {{ $voucher->status_label }}</div>
            </div>
            @if($voucher->cheque)
            <hr>
            <p><strong>بيانات الشيك:</strong> رقم {{ $voucher->cheque->cheque_number }} -
                استحقاق {{ \Carbon\Carbon::parse($voucher->cheque->due_date)->format('Y-m-d') }} -
                <span class="badge badge-{{ $voucher->cheque->status_color }}">{{ $voucher->cheque->status_label }}</span>
                <a href="{{ route('cheques.show', $voucher->cheque->id) }}" class="btn btn-xs btn-info">عرض الشيك</a>
            </p>
            @endif
            @if($voucher->notes)<p><strong>ملاحظات:</strong> {{ $voucher->notes }}</p>@endif
        </div>
    </div>
</div>
@endsection
