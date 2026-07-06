@extends('admin.layouts.treasury')
@section('title') تعديل خزنة @endsection
@section('start') الخزينة @endsection
@section('home') <a href="{{ route('cash_boxes.index') }}">الخزائن النقدية</a> @endsection
@section('startpage') تعديل @endsection

@section('content')
<div class="col-lg-6 col-md-8">
    <div class="card card-primary card-outline">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-edit ml-2"></i> تعديل خزنة: {{ $box->code }}</h3></div>
        <form action="{{ route('cash_boxes.update', $box->id) }}" method="POST">
            @csrf
            <div class="card-body">
                @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
                <div class="form-group">
                    <label>الكود</label>
                    <input type="text" class="form-control" value="{{ $box->code }}" disabled>
                </div>
                <div class="form-group">
                    <label>الاسم <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $box->name) }}" required>
                </div>
                <div class="form-group">
                    <label>الفرع (اختياري)</label>
                    <select name="branch_id" class="form-control select2">
                        <option value="">-- بدون --</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ $box->branch_id == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>الحساب المحاسبي المرتبط <span class="text-danger">*</span></label>
                    <select name="gl_account_id" class="form-control select2" required>
                        <option value="">-- اختر الحساب --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ $box->gl_account_id == $acc->id ? 'selected' : '' }}>{{ $acc->account_code }} - {{ $acc->account_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ $box->is_active ? 'checked' : '' }}>
                    <label class="custom-control-label" for="is_active">مفعّل</label>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ</button>
                <a href="{{ route('cash_boxes.index') }}" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
