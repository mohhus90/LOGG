@extends('admin.layouts.assets')
@section('title') إضافة أصل ثابت @endsection
@section('start') الأصول الثابتة @endsection
@section('home') <a href="{{ route('fixed_assets.index') }}">سجل الأصول</a> @endsection
@section('startpage') إضافة @endsection

@section('content')
<div class="col-lg-8">
    <div class="card card-primary card-outline">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-plus ml-2"></i> إضافة أصل ثابت</h3></div>
        <form action="{{ route('fixed_assets.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>رقم الأصل</label>
                            <input type="text" class="form-control" value="{{ $nextNumber }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>اسم الأصل <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>الفئة <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-control select2" required id="categorySelect">
                                <option value="">-- اختر الفئة --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" data-years="{{ $cat->default_useful_life_years }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>الفرع (اختياري)</label>
                            <select name="branch_id" class="form-control select2">
                                <option value="">-- بدون --</option>
                                @foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>الموقع</label>
                            <input type="text" name="location" class="form-control" value="{{ old('location') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>تاريخ الشراء <span class="text-danger">*</span></label>
                            <input type="date" name="purchase_date" class="form-control" value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>تكلفة الشراء <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="purchase_cost" class="form-control" value="{{ old('purchase_cost') }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>القيمة التخريدية</label>
                            <input type="number" step="0.01" name="salvage_value" class="form-control" value="{{ old('salvage_value', 0) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>العمر الإنتاجي (سنوات) <span class="text-danger">*</span></label>
                            <input type="number" name="useful_life_years" id="usefulLifeInput" class="form-control" value="{{ old('useful_life_years', 5) }}" min="1" required>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>وصف</label>
                            <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ</button>
                <a href="{{ route('fixed_assets.index') }}" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function () {
    $('#categorySelect').on('change', function () {
        const years = $(this).find(':selected').data('years');
        if (years) $('#usefulLifeInput').val(years);
    });
});
</script>
@endsection
