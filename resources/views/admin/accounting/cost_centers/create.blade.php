@extends('admin.layouts.accounting')
@section('title') إضافة مركز تكلفة @endsection
@section('start') المحاسبة @endsection
@section('home') <a href="{{ route('cost_centers.index') }}">مراكز التكلفة</a> @endsection
@section('startpage') إضافة @endsection

@section('content')
<div class="col-lg-6 col-md-8">
    <div class="card card-primary card-outline">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-plus ml-2"></i> إضافة مركز تكلفة</h3></div>
        <form action="{{ route('cost_centers.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
                <div class="form-group">
                    <label>الكود <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control" value="{{ old('code') }}" required>
                </div>
                <div class="form-group">
                    <label>الاسم <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="form-group">
                    <label>المركز الأب (اختياري)</label>
                    <select name="parent_id" class="form-control select2">
                        <option value="">-- بدون --</option>
                        @foreach($parents as $p)<option value="{{ $p->id }}">{{ $p->code }} - {{ $p->name }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>الفرع (اختياري)</label>
                    <select name="branch_id" class="form-control select2">
                        <option value="">-- بدون --</option>
                        @foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
                    </select>
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" checked>
                    <label class="custom-control-label" for="is_active">مفعّل</label>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ</button>
                <a href="{{ route('cost_centers.index') }}" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
