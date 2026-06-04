@extends('admin.layouts.admin')
@section('title') تعديل مستوى وظيفي @endsection
@section('start') الهيكل الوظيفي @endsection
@section('home')
    <a href="{{ route('org_levels.index') }}">الهيكل الوظيفي</a>
@endsection
@section('startpage') تعديل @endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">تعديل مستوى: <strong>{{ $data->name }}</strong></h3>
        </div>
        <div class="card-body">

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
                </div>
            @endif

            <form action="{{ route('org_levels.update', $data->id) }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">اسم المستوى (عربي) <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $data->name) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">اسم المستوى (إنجليزي)</label>
                        <input type="text" name="name_en" class="form-control" value="{{ old('name_en', $data->name_en) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">الترتيب الهرمي <span class="text-danger">*</span></label>
                        <input type="number" name="level_order" class="form-control" min="1"
                            value="{{ old('level_order', $data->level_order) }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">يتبع (المستوى الأعلى)</label>
                        <select name="parent_id" class="form-select">
                            <option value="">— لا يوجد (قمة الهرم) —</option>
                            @foreach($parents as $p)
                                <option value="{{ $p->id }}"
                                    {{ old('parent_id', $data->parent_id) == $p->id ? 'selected' : '' }}>
                                    {{ str_repeat('— ', $p->level_order - 1) }}{{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">نوع المستوى <span class="text-danger">*</span></label>
                        <select name="level_type" class="form-select" required>
                            @foreach(['top_management'=>'إدارة عليا','middle_management'=>'إدارة وسطى','supervisor'=>'مشرف','sales'=>'مبيعات','operational'=>'تشغيلي','support'=>'دعم','other'=>'أخرى'] as $val => $label)
                                <option value="{{ $val }}"
                                    {{ old('level_type', $data->level_type) == $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 mb-3">
                        <div class="card border-secondary">
                            <div class="card-header bg-light"><strong>خصائص المستوى</strong></div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_management" id="is_management"
                                                {{ old('is_management', $data->is_management) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_management">مستوى إداري</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_sales_role" id="is_sales_role"
                                                {{ old('is_sales_role', $data->is_sales_role) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_sales_role">دور مبيعاتي</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="receives_seller_commission"
                                                id="receives_seller_commission"
                                                {{ old('receives_seller_commission', $data->receives_seller_commission) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="receives_seller_commission">
                                                <span class="text-success fw-bold">يستحق عمولة البائع</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="receives_manager_commission"
                                                id="receives_manager_commission"
                                                {{ old('receives_manager_commission', $data->receives_manager_commission) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="receives_manager_commission">
                                                <span class="text-primary fw-bold">يستحق عمولة المدير</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label">وصف (اختياري)</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description', $data->description) }}</textarea>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> حفظ التعديلات
                        </button>
                        <a href="{{ route('org_levels.index') }}" class="btn btn-secondary ms-2">إلغاء</a>
                        <a href="{{ route('org_levels.delete', $data->id) }}"
                           class="btn btn-danger ms-2"
                           onclick="return confirm('هل أنت متأكد من حذف هذا المستوى؟')">
                            <i class="fas fa-trash"></i> حذف
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
