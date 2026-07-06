@extends('admin.layouts.documents')
@section('title') رفع وثيقة جديدة @endsection
@section('start') إدارة الوثائق @endsection
@section('home') <a href="{{ route('documents.index') }}">الوثائق</a> @endsection
@section('startpage') رفع @endsection

@section('content')
<div class="col-lg-7">
    <div class="card card-primary card-outline">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-upload ml-2"></i> رفع وثيقة جديدة</h3></div>
        <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
                <div class="form-group">
                    <label>عنوان الوثيقة <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                </div>
                <div class="form-group">
                    <label>الفئة (اختياري)</label>
                    <select name="category_id" class="form-control select2">
                        <option value="">-- بدون --</option>
                        @foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>الملف <span class="text-danger">*</span></label>
                    <input type="file" name="file" class="form-control-file" required>
                    <small class="text-muted">PDF، صورة، Word أو Excel - حتى 20 ميجابايت</small>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> رفع</button>
                <a href="{{ route('documents.index') }}" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
