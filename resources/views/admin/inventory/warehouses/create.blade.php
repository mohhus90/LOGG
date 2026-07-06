@extends('admin.layouts.inventory')
@section('title') إضافة مخزن @endsection
@section('start') المخازن @endsection
@section('home') <a href="{{ route('warehouses.index') }}">المخازن</a> @endsection
@section('startpage') إضافة @endsection

@section('content')
<div class="col-md-8 mx-auto">
    <div class="card card-success">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-warehouse ml-2"></i>إضافة مخزن جديد</h3>
        </div>
        <form action="{{ route('warehouses.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>الكود</label>
                        <input type="text" name="code" class="form-control" placeholder="كود المخزن" value="{{ old('code') }}">
                    </div>
                    <div class="col-md-8 form-group">
                        <label>اسم المخزن <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="اسم المخزن" value="{{ old('name') }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>الفرع</label>
                        <select name="branch_id" class="form-control select2">
                            <option value="">-- اختر الفرع --</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>الموقع</label>
                        <input type="text" name="location" class="form-control" placeholder="موقع المخزن" value="{{ old('location') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label>ملاحظات</label>
                    <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                </div>

                <div class="form-group mb-0">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="is_default" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_default">مخزن افتراضي (يُستخدم تلقائيًا في الفواتير الجديدة)</label>
                    </div>
                    <div class="custom-control custom-checkbox mt-2">
                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">مفعّل</label>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success"><i class="fas fa-save ml-1"></i> حفظ</button>
                <a href="{{ route('warehouses.index') }}" class="btn btn-secondary mr-2">رجوع</a>
            </div>
        </form>
    </div>
</div>
@endsection
