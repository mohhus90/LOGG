@extends('admin.layouts.sales')
@section('title') نقطة البيع @endsection
@section('start') المبيعات @endsection
@section('home') <a href="{{ route('pos.select_register') }}">نقطة البيع</a> @endsection
@section('startpage') اختيار الكاشير @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-cash-register ml-2"></i> اختر الكاشير</h3>
            <div class="card-tools">
                <a href="{{ route('pos_registers.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-cog ml-1"></i> إدارة الكاشيرات
                </a>
            </div>
        </div>
        <div class="card-body">
            @if($registers->isEmpty())
                <div class="alert alert-warning">
                    لا يوجد كاشير مُعرَّف بعد.
                    <a href="{{ route('pos_registers.create') }}" class="btn btn-sm btn-success ml-2">إضافة كاشير</a>
                </div>
            @else
                <div class="row">
                    @foreach($registers as $register)
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('pos.terminal', $register->id) }}" class="btn btn-outline-primary btn-block py-4">
                            <i class="fas fa-cash-register fa-2x d-block mb-2"></i>
                            {{ $register->name }}
                        </a>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
