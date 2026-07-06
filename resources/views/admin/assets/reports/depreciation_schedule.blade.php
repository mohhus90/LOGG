@extends('admin.layouts.assets')
@section('title') جدول إهلاك {{ $asset->asset_number }} @endsection
@section('start') الأصول الثابتة @endsection
@section('home') <a href="{{ route('asset_reports.register') }}">سجل الأصول التفصيلي</a> @endsection
@section('startpage') جدول الإهلاك @endsection

@section('content')
<div class="col-lg-7">
    <div class="card card-primary card-outline">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-calendar-alt ml-2"></i> جدول إهلاك: {{ $asset->name }}</h3></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6"><strong>القسط الشهري:</strong> {{ number_format($monthlyAmount, 2) }}</div>
                <div class="col-md-6"><strong>القيمة الدفترية الحالية:</strong> {{ number_format($asset->book_value, 2) }}</div>
                <div class="col-md-6 mt-2"><strong>مجمع الإهلاك حتى الآن:</strong> {{ number_format($asset->accumulated_depreciation, 2) }}</div>
                <div class="col-md-6 mt-2"><strong>الأشهر المتبقية للإهلاك الكامل:</strong> {{ $remainingMonths }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
