@extends('admin.layouts.accounting')
@section('title') تعديل مركز تكلفة @endsection
@section('start') المحاسبة @endsection
@section('home') <a href="{{ route('cost_centers.index') }}">مراكز التكلفة</a> @endsection
@section('startpage') تعديل @endsection

@section('content')
<div class="col-lg-6 col-md-8">
    <div class="card card-primary card-outline">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-edit ml-2"></i> تعديل مركز تكلفة</h3></div>
        <form action="{{ route('cost_centers.update', $center->id) }}" method="POST">
            @csrf
            <div class="card-body">
                @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
                <div class="form-group">
                    <label>الكود</label>
                    <input type="text" class="form-control" value="{{ $center->code }}" disabled>
                </div>
                <div class="form-group">
                    <label>الاسم <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $center->name) }}" required>
                </div>
                <div class="form-group">
                    <label>المركز الأب (اختياري)</label>
                    <select name="parent_id" class="form-control select2">
                        <option value="">-- بدون --</option>
                        @foreach($parents as $p)
                            <option value="{{ $p->id }}" {{ $center->parent_id == $p->id ? 'selected' : '' }}>{{ $p->code }} - {{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>الفرع (اختياري)</label>
                    <select name="branch_id" class="form-control select2">
                        <option value="">-- بدون --</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ $center->branch_id == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ $center->is_active ? 'checked' : '' }}>
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
