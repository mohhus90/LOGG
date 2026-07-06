@extends('admin.layouts.sales')
@section('title') إضافة مجموعة أصناف @endsection
@section('start') المبيعات @endsection
@section('home') <a href="{{ route('item_categories.index') }}">مجموعات الأصناف</a> @endsection
@section('startpage') إضافة @endsection

@section('content')
<div class="col-md-7 mx-auto">
    <div class="card card-success">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-plus-circle ml-2"></i>إضافة مجموعة أصناف جديدة
            </h3>
        </div>
        <form action="{{ route('item_categories.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>الكود</label>
                        <input type="text" name="code" class="form-control"
                            placeholder="مثال: CAT-001"
                            value="{{ old('code') }}">
                    </div>
                    <div class="col-md-8 form-group">
                        <label>الاسم بالعربية <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required
                            placeholder="اسم المجموعة بالعربية"
                            value="{{ old('name') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label>الاسم بالإنجليزية</label>
                    <input type="text" name="name_en" class="form-control"
                        placeholder="Category name in English"
                        value="{{ old('name_en') }}">
                </div>

                <div class="form-group">
                    <label>المجموعة الأب</label>
                    <select name="parent_id" class="form-control select2">
                        <option value="">-- بدون مجموعة أب (رئيسية) --</option>
                        @foreach($parents as $parent)
                        <option value="{{ $parent->id }}"
                            {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                            {{ $parent->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="is_active"
                            name="is_active" value="1"
                            {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">مفعّل</label>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save ml-1"></i> حفظ
                </button>
                <a href="{{ route('item_categories.index') }}" class="btn btn-secondary mr-2">رجوع</a>
            </div>
        </form>
    </div>
</div>
@endsection
