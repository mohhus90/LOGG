@extends('admin.layouts.treasury')
@section('title') لوحة الخزينة @endsection
@section('start') الخزينة @endsection
@section('home') <a href="{{ route('treasury_reports.index') }}">لوحة الخزينة</a> @endsection
@section('startpage') الرئيسية @endsection

@section('content')
<div class="col-12">
    <div class="row">
        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner"><h3>{{ number_format($totalCash, 2) }}</h3><p>إجمالي النقدية بالخزائن</p></div>
                <div class="icon"><i class="fas fa-cash-register"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner"><h3>{{ number_format($totalBank, 2) }}</h3><p>إجمالي أرصدة البنوك</p></div>
                <div class="icon"><i class="fas fa-university"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-warning">
                <div class="inner"><h3>{{ $chequesDue->count() }}</h3><p>شيكات مستحقة قريبًا</p></div>
                <div class="icon"><i class="fas fa-money-check-alt"></i></div>
                <a href="{{ route('treasury_reports.cheques_due') }}" class="small-box-footer">عرض التفاصيل <i class="fas fa-arrow-left"></i></a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-success">
                <div class="card-header"><h3 class="card-title">أرصدة الخزائن النقدية</h3></div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0">
                        <thead><tr><th>الخزنة</th><th class="text-left">الرصيد</th></tr></thead>
                        <tbody>
                            @forelse($cashBoxes as $box)
                            <tr><td>{{ $box->name }}</td><td class="text-left">{{ number_format($box->current_balance, 2) }}</td></tr>
                            @empty
                            <tr><td colspan="2" class="text-center text-muted py-3">لا توجد خزائن</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-outline card-info">
                <div class="card-header"><h3 class="card-title">أرصدة الحسابات البنكية</h3></div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0">
                        <thead><tr><th>البنك</th><th class="text-left">الرصيد</th></tr></thead>
                        <tbody>
                            @forelse($banks as $bank)
                            <tr><td>{{ $bank->bank_name }} - {{ $bank->account_name }}</td><td class="text-left">{{ number_format($bank->current_balance, 2) }}</td></tr>
                            @empty
                            <tr><td colspan="2" class="text-center text-muted py-3">لا توجد حسابات بنكية</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
