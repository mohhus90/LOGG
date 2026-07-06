@extends('admin.layouts.assets')
@section('title') إضافة فئة أصول @endsection
@section('start') الأصول الثابتة @endsection
@section('home') <a href="{{ route('asset_categories.index') }}">فئات الأصول</a> @endsection
@section('startpage') إضافة @endsection

@section('content')
<div class="col-lg-7 col-md-9">
    <div class="card card-primary card-outline">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-plus ml-2"></i> إضافة فئة أصول</h3></div>
        <form action="{{ route('asset_categories.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
                <div class="form-group">
                    <label>اسم الفئة <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="مثال: سيارات، أجهزة حاسب، أثاث مكتبي">
                </div>
                <div class="form-group">
                    <label>العمر الافتراضي بالسنوات <span class="text-danger">*</span></label>
                    <input type="number" name="default_useful_life_years" class="form-control" value="{{ old('default_useful_life_years', 5) }}" min="1" required>
                </div>
                <div class="form-group">
                    <label>حساب الأصل (تكلفة الأصل) <span class="text-danger">*</span></label>
                    <select name="asset_gl_account_id" class="form-control select2" required>
                        <option value="">-- اختر الحساب --</option>
                        @foreach($accounts as $acc)<option value="{{ $acc->id }}">{{ $acc->account_code }} - {{ $acc->account_name }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>حساب مجمع الإهلاك <span class="text-danger">*</span></label>
                    <select name="accum_depreciation_gl_account_id" class="form-control select2" required>
                        <option value="">-- اختر الحساب --</option>
                        @foreach($accounts as $acc)<option value="{{ $acc->id }}">{{ $acc->account_code }} - {{ $acc->account_name }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>حساب مصروف الإهلاك <span class="text-danger">*</span></label>
                    <select name="depreciation_expense_gl_account_id" class="form-control select2" required>
                        <option value="">-- اختر الحساب --</option>
                        @foreach($accounts as $acc)<option value="{{ $acc->id }}">{{ $acc->account_code }} - {{ $acc->account_name }}</option>@endforeach
                    </select>
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" checked>
                    <label class="custom-control-label" for="is_active">مفعّل</label>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ</button>
                <a href="{{ route('asset_categories.index') }}" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
