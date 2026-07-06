@extends('admin.layouts.assets')
@section('title') تعديل أصل @endsection
@section('start') الأصول الثابتة @endsection
@section('home') <a href="{{ route('fixed_assets.index') }}">سجل الأصول</a> @endsection
@section('startpage') تعديل @endsection

@section('content')
<div class="col-lg-7 col-md-9">
    <div class="card card-primary card-outline">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-edit ml-2"></i> تعديل: {{ $asset->asset_number }}</h3></div>
        <form action="{{ route('fixed_assets.update', $asset->id) }}" method="POST">
            @csrf
            <div class="card-body">
                @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
                <p class="text-muted small">ملاحظة: بيانات التكلفة والإهلاك لا يمكن تعديلها بعد التسجيل، فقط البيانات الوصفية.</p>
                <div class="form-group">
                    <label>اسم الأصل <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $asset->name) }}" required>
                </div>
                <div class="form-group">
                    <label>الفرع (اختياري)</label>
                    <select name="branch_id" class="form-control select2">
                        <option value="">-- بدون --</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ $asset->branch_id == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>الموقع</label>
                    <input type="text" name="location" class="form-control" value="{{ old('location', $asset->location) }}">
                </div>
                <div class="form-group">
                    <label>وصف</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description', $asset->description) }}</textarea>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ</button>
                <a href="{{ route('fixed_assets.show', $asset->id) }}" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
