@extends('admin.layouts.sales')
@section('title') إضافة متجر إلكتروني @endsection
@section('start') المبيعات @endsection
@section('home') <a href="{{ route('sales_ecommerce_stores.index') }}">ربط المتاجر الإلكترونية</a> @endsection
@section('startpage') إضافة @endsection

@section('content')
<div class="col-lg-6 col-md-8">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-plus ml-2"></i> إضافة متجر Wuilt</h3>
        </div>
        <form action="{{ route('sales_ecommerce_stores.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif
                <div class="form-group">
                    <label>اسم المتجر (للتمييز فقط)</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="مثال: متجرنا الرئيسي">
                </div>
                <div class="form-group">
                    <label>Store ID <span class="text-danger">*</span></label>
                    <input type="text" name="store_id" class="form-control" value="{{ old('store_id') }}" required
                           placeholder="Store_cmb95ahxq000j01lk2in401ix">
                    <small class="form-text text-muted">موجود في رابط لوحة تحكم متجرك على Wuilt.</small>
                </div>
                <div class="form-group">
                    <label>API Key <span class="text-danger">*</span></label>
                    <input type="password" name="api_key" class="form-control" required
                           placeholder="يُنشأ من لوحة تحكم Wuilt">
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ</button>
                <a href="{{ route('sales_ecommerce_stores.index') }}" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
