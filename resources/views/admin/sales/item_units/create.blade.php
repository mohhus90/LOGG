@extends('admin.layouts.sales')
@section('title') إضافة وحدة قياس @endsection
@section('start') المبيعات @endsection
@section('home') <a href="{{ route('item_units.index') }}">وحدات القياس</a> @endsection
@section('startpage') إضافة @endsection

@section('content')
<div class="col-lg-6 col-md-8">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-plus ml-2"></i> إضافة وحدة قياس جديدة</h3>
        </div>
        <form action="{{ route('item_units.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif
                <div class="form-group">
                    <label>الاسم بالعربية <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="مثال: كيلوجرام">
                </div>
                <div class="form-group">
                    <label>الاسم بالإنجليزية</label>
                    <input type="text" name="name_en" class="form-control" value="{{ old('name_en') }}" placeholder="e.g. Kilogram">
                </div>
                <div class="form-group">
                    <label>الرمز</label>
                    <input type="text" name="symbol" class="form-control" value="{{ old('symbol') }}" placeholder="مثال: كجم">
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" checked>
                    <label class="custom-control-label" for="is_active">مفعّل</label>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ</button>
                <a href="{{ route('item_units.index') }}" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
