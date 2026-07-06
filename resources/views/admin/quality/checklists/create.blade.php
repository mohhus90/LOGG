@extends('admin.layouts.quality')
@section('title') قالب فحص جديد @endsection
@section('start') ضبط الجودة @endsection
@section('home') <a href="{{ route('quality_checklists.index') }}">قوالب الفحص</a> @endsection
@section('startpage') إضافة @endsection

@section('content')
<div class="col-lg-7 col-md-9">
    <div class="card card-primary card-outline">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-plus ml-2"></i> قالب فحص جديد</h3></div>
        <form action="{{ route('quality_checklists.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
                <div class="form-group">
                    <label>اسم القالب <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="form-group">
                    <label>يُطبَّق على <span class="text-danger">*</span></label>
                    <select name="applies_to" class="form-control" required>
                        <option value="both">إنتاج وشراء</option>
                        <option value="production">إنتاج فقط</option>
                        <option value="purchase">شراء فقط</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>بنود الفحص <span class="text-danger">*</span></label>
                    <div id="itemsWrapper">
                        <div class="input-group mb-2">
                            <input type="text" name="items[]" class="form-control" placeholder="مثال: مطابقة المواصفات القياسية" required>
                            <div class="input-group-append"><button type="button" class="btn btn-outline-secondary add-item"><i class="fas fa-plus"></i></button></div>
                        </div>
                    </div>
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" checked>
                    <label class="custom-control-label" for="is_active">مفعّل</label>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ</button>
                <a href="{{ route('quality_checklists.index') }}" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function () {
    $(document).on('click', '.add-item', function () {
        const $row = $(`<div class="input-group mb-2">
            <input type="text" name="items[]" class="form-control" placeholder="بند فحص إضافي">
            <div class="input-group-append"><button type="button" class="btn btn-outline-danger remove-item"><i class="fas fa-times"></i></button></div>
        </div>`);
        $('#itemsWrapper').append($row);
    });
    $(document).on('click', '.remove-item', function () { $(this).closest('.input-group').remove(); });
});
</script>
@endsection
