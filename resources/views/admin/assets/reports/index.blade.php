@extends('admin.layouts.assets')
@section('title') لوحة الأصول @endsection
@section('start') الأصول الثابتة @endsection
@section('home') <a href="{{ route('asset_reports.index') }}">لوحة الأصول</a> @endsection
@section('startpage') الرئيسية @endsection

@section('content')
<div class="col-12">
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-secondary">
                <div class="inner"><h3>{{ $totals['count'] }}</h3><p>عدد الأصول المسجلة</p></div>
                <div class="icon"><i class="fas fa-building"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner"><h3>{{ number_format($totals['total_cost'], 0) }}</h3><p>إجمالي تكلفة الأصول</p></div>
                <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner"><h3>{{ number_format($totals['total_accum'], 0) }}</h3><p>إجمالي مجمع الإهلاك</p></div>
                <div class="icon"><i class="fas fa-chart-line"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner"><h3>{{ number_format($totals['total_book'], 0) }}</h3><p>إجمالي القيمة الدفترية</p></div>
                <div class="icon"><i class="fas fa-balance-scale"></i></div>
                <a href="{{ route('asset_reports.register') }}" class="small-box-footer">سجل الأصول التفصيلي <i class="fas fa-arrow-left"></i></a>
            </div>
        </div>
    </div>
</div>
@endsection
