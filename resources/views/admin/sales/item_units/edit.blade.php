@extends('admin.layouts.sales')
@section('title') تعديل وحدة القياس @endsection
@section('start') المبيعات @endsection
@section('home') <a href="{{ route('item_units.index') }}">وحدات القياس</a> @endsection
@section('startpage') تعديل @endsection

@section('content')
<div class="col-lg-6 col-md-8">
    <div class="card card-warning card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-edit ml-2"></i> تعديل: {{ $unit->name }}</h3>
        </div>
        <form action="{{ route('item_units.update', $unit->id) }}" method="POST">
            @csrf
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif
                <div class="form-group">
                    <label>الاسم بالعربية <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $unit->name) }}" required>
                </div>
                <div class="form-group">
                    <label>الاسم بالإنجليزية</label>
                    <input type="text" name="name_en" class="form-control" value="{{ old('name_en', $unit->name_en) }}">
                </div>
                <div class="form-group">
                    <label>الرمز</label>
                    <input type="text" name="symbol" class="form-control" value="{{ old('symbol', $unit->symbol) }}">
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                           {{ $unit->is_active ? 'checked' : '' }}>
                    <label class="custom-control-label" for="is_active">مفعّل</label>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-warning"><i class="fas fa-save ml-1"></i> تحديث</button>
                <a href="{{ route('item_units.index') }}" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
