@extends('admin.layouts.accounting')
@section('title') لوحة المحاسبة @endsection
@section('start') المحاسبة @endsection
@section('home') <a href="{{ route('accounting_reports.index') }}">لوحة المحاسبة</a> @endsection
@section('startpage') الرئيسية @endsection

@section('content')
<div class="col-12">
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner"><h3>{{ $accountsCount }}</h3><p>عدد الحسابات في الدليل</p></div>
                <div class="icon"><i class="fas fa-sitemap"></i></div>
                <a href="{{ route('chart_of_accounts.index') }}" class="small-box-footer">دليل الحسابات <i class="fas fa-arrow-left"></i></a>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <a href="{{ route('accounting_reports.trial_balance') }}" class="card card-outline card-primary text-center p-4 d-block">
                <i class="fas fa-balance-scale fa-2x text-primary mb-2"></i>
                <div>ميزان المراجعة</div>
            </a>
        </div>
        <div class="col-md-3 col-sm-6">
            <a href="{{ route('accounting_reports.income_statement') }}" class="card card-outline card-success text-center p-4 d-block">
                <i class="fas fa-file-invoice-dollar fa-2x text-success mb-2"></i>
                <div>قائمة الدخل</div>
            </a>
        </div>
        <div class="col-md-3 col-sm-6">
            <a href="{{ route('accounting_reports.balance_sheet') }}" class="card card-outline card-warning text-center p-4 d-block">
                <i class="fas fa-university fa-2x text-warning mb-2"></i>
                <div>الميزانية العمومية</div>
            </a>
        </div>
        <div class="col-md-3 col-sm-6">
            <a href="{{ route('accounting_reports.ledger') }}" class="card card-outline card-secondary text-center p-4 d-block">
                <i class="fas fa-list-alt fa-2x text-secondary mb-2"></i>
                <div>كشف حساب</div>
            </a>
        </div>
    </div>
</div>
@endsection
