@extends('admin.layouts.sales')
@section('title') تعديل متجر إلكتروني @endsection
@section('start') المبيعات @endsection
@section('home') <a href="{{ route('sales_ecommerce_stores.index') }}">ربط المتاجر الإلكترونية</a> @endsection
@section('startpage') تعديل @endsection

@section('content')
<div class="col-lg-6 col-md-8">
    <div class="card card-warning card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-edit ml-2"></i> تعديل متجر: {{ $store->name ?: $store->store_id }}</h3>
        </div>
        <form action="{{ route('sales_ecommerce_stores.update', $store->id) }}" method="POST">
            @csrf
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif
                <div class="form-group">
                    <label>اسم المتجر (للتمييز فقط)</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $store->name) }}">
                </div>
                <div class="form-group">
                    <label>Store ID <span class="text-danger">*</span></label>
                    <input type="text" name="store_id" class="form-control" value="{{ old('store_id', $store->store_id) }}" required>
                </div>
                <div class="form-group">
                    <label>API Key</label>
                    <input type="password" name="api_key" class="form-control" placeholder="اتركه فارغاً للإبقاء على القيمة الحالية">
                </div>
                <div class="form-group">
                    <label>فترة المزامنة (دقائق)</label>
                    <input type="number" name="sync_interval_minutes" class="form-control" min="5"
                           value="{{ old('sync_interval_minutes', $store->sync_interval_minutes) }}">
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                           {{ old('is_active', $store->is_active) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="is_active">مفعّل (يشارك في المزامنة الدورية)</label>
                </div>

                @if($store->last_sync_error)
                <div class="alert alert-danger mt-3" style="white-space:pre-line">
                    <strong>آخر خطأ مزامنة:</strong><br>{{ $store->last_sync_error }}
                </div>
                @endif
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-warning"><i class="fas fa-save ml-1"></i> حفظ التعديلات</button>
                <a href="{{ route('sales_ecommerce_stores.index') }}" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
