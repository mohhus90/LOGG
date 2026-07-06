@extends('admin.layouts.sales')
@section('title') تعديل مجموعة أصناف @endsection
@section('start') المبيعات @endsection
@section('home') <a href="{{ route('item_categories.index') }}">مجموعات الأصناف</a> @endsection
@section('startpage') تعديل @endsection

@section('content')
<div class="col-md-7 mx-auto">
    <div class="card card-warning">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-edit ml-2"></i>
                تعديل مجموعة — {{ $cat->name }}
            </h3>
        </div>
        <form action="{{ route('item_categories.update', $cat->id) }}" method="POST">
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
                            value="{{ old('code', $cat->code) }}">
                    </div>
                    <div class="col-md-8 form-group">
                        <label>الاسم بالعربية <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required
                            placeholder="اسم المجموعة بالعربية"
                            value="{{ old('name', $cat->name) }}">
                    </div>
                </div>

                <div class="form-group">
                    <label>الاسم بالإنجليزية</label>
                    <input type="text" name="name_en" class="form-control"
                        placeholder="Category name in English"
                        value="{{ old('name_en', $cat->name_en) }}">
                </div>

                <div class="form-group">
                    <label>المجموعة الأب</label>
                    <select name="parent_id" class="form-control select2">
                        <option value="">-- بدون مجموعة أب (رئيسية) --</option>
                        @foreach($parents as $parent)
                        @if($parent->id != $cat->id)
                        <option value="{{ $parent->id }}"
                            {{ old('parent_id', $cat->parent_id) == $parent->id ? 'selected' : '' }}>
                            {{ $parent->name }}
                        </option>
                        @endif
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="is_active"
                            name="is_active" value="1"
                            {{ old('is_active', $cat->is_active) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">مفعّل</label>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save ml-1"></i> حفظ التعديلات
                </button>
                <a href="{{ route('item_categories.index') }}" class="btn btn-secondary mr-2">رجوع</a>
            </div>
        </form>
    </div>
</div>
@endsection
