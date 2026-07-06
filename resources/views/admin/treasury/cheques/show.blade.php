@extends('admin.layouts.treasury')
@section('title') شيك {{ $cheque->cheque_number }} @endsection
@section('start') الخزينة @endsection
@section('home') <a href="{{ route('cheques.index') }}">الشيكات</a> @endsection
@section('startpage') {{ $cheque->cheque_number }} @endsection

@section('content')
<div class="col-lg-8">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-money-check-alt ml-2"></i> شيك {{ $cheque->cheque_number }}
                <span class="badge badge-{{ $cheque->status_color }} mr-2">{{ $cheque->status_label }}</span>
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4"><strong>الاتجاه:</strong> {{ $cheque->direction === 'received' ? 'واردة (من عميل)' : 'صادرة (لمورد)' }}</div>
                <div class="col-md-4"><strong>الطرف:</strong> {{ $cheque->party_name }}</div>
                <div class="col-md-4"><strong>المبلغ:</strong> {{ number_format($cheque->amount, 2) }}</div>
                <div class="col-md-4 mt-2"><strong>البنك:</strong> {{ $cheque->bank_name ?? '-' }}</div>
                <div class="col-md-4 mt-2"><strong>تاريخ الشيك:</strong> {{ \Carbon\Carbon::parse($cheque->cheque_date)->format('Y-m-d') }}</div>
                <div class="col-md-4 mt-2"><strong>تاريخ الاستحقاق:</strong> {{ \Carbon\Carbon::parse($cheque->due_date)->format('Y-m-d') }}</div>
            </div>

            @if($cheque->status === 'under_collection')
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <form action="{{ route('cheques.collect', $cheque->id) }}" method="POST" onsubmit="return confirm('تأكيد تحصيل الشيك؟')">
                        @csrf
                        <div class="form-group">
                            <label>الحساب البنكي {{ $cheque->direction === 'received' ? 'المُحصَّل عليه' : 'المسحوب منه' }}</label>
                            <select name="bank_account_id" class="form-control" required>
                                <option value="">-- اختر الحساب --</option>
                                @foreach($banks as $bank)
                                    <option value="{{ $bank->id }}" {{ $cheque->bank_account_id == $bank->id ? 'selected' : '' }}>{{ $bank->bank_name }} - {{ $bank->account_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button class="btn btn-success btn-sm"><i class="fas fa-check ml-1"></i> تحصيل الشيك</button>
                    </form>
                </div>
                <div class="col-md-6">
                    <form action="{{ route('cheques.bounce', $cheque->id) }}" method="POST" onsubmit="return confirm('تأكيد ارتجاع الشيك؟')">
                        @csrf
                        <div class="form-group">
                            <label>سبب الارتجاع</label>
                            <input type="text" name="reason" class="form-control" required>
                        </div>
                        <button class="btn btn-danger btn-sm"><i class="fas fa-times ml-1"></i> ارتجاع الشيك</button>
                    </form>
                </div>
            </div>
            @endif

            @if($cheque->status === 'bounced')
                <div class="alert alert-danger mt-3">تم ارتجاع الشيك بتاريخ {{ \Carbon\Carbon::parse($cheque->bounced_at)->format('Y-m-d') }} - السبب: {{ $cheque->bounce_reason }}</div>
            @endif
            @if($cheque->status === 'collected')
                <div class="alert alert-success mt-3">تم تحصيل الشيك بتاريخ {{ \Carbon\Carbon::parse($cheque->collected_at)->format('Y-m-d') }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
