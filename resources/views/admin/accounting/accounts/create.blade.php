@extends('admin.layouts.accounting')
@section('title') إضافة حساب @endsection
@section('start') المحاسبة @endsection
@section('home') <a href="{{ route('chart_of_accounts.index') }}">دليل الحسابات</a> @endsection
@section('startpage') إضافة @endsection

@section('content')
<div class="col-lg-7 col-md-9">
    <div class="card card-primary card-outline">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-plus ml-2"></i> إضافة حساب جديد</h3></div>
        <form action="{{ route('chart_of_accounts.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>رقم الحساب <span class="text-danger">*</span></label>
                            <input type="text" name="account_code" class="form-control" value="{{ old('account_code') }}" required>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>اسم الحساب بالعربية <span class="text-danger">*</span></label>
                            <input type="text" name="account_name" class="form-control" value="{{ old('account_name') }}" required>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>اسم الحساب بالإنجليزية</label>
                            <input type="text" name="account_name_en" class="form-control" value="{{ old('account_name_en') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>نوع الحساب <span class="text-danger">*</span></label>
                            <select name="account_type" class="form-control" required>
                                <option value="asset">أصول</option>
                                <option value="liability">التزامات</option>
                                <option value="equity">حقوق ملكية</option>
                                <option value="revenue">إيرادات</option>
                                <option value="expense">مصروفات</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>طبيعة الحساب</label>
                            <select name="account_nature" class="form-control">
                                <option value="debit">مدين</option>
                                <option value="credit">دائن</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>الحساب الأب (اختياري)</label>
                            <select name="parent_id" class="form-control select2">
                                <option value="">-- بدون --</option>
                                @foreach($groups as $g)
                                    <option value="{{ $g->id }}" {{ old('parent_id') == $g->id ? 'selected' : '' }}>
                                        {{ $g->account_code }} - {{ $g->account_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>الرصيد الافتتاحي</label>
                            <input type="number" step="0.0001" name="opening_balance" class="form-control" value="{{ old('opening_balance', 0) }}">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>ملاحظات</label>
                            <input type="text" name="notes" class="form-control" value="{{ old('notes') }}">
                        </div>
                    </div>
                </div>
                <div class="custom-control custom-switch mb-2">
                    <input type="checkbox" class="custom-control-input" id="is_group" name="is_group" value="1">
                    <label class="custom-control-label" for="is_group">حساب رئيسي (مجموعة) - لا يستقبل قيود مباشرة</label>
                </div>
                <div class="custom-control custom-switch mb-2">
                    <input type="checkbox" class="custom-control-input" id="allow_cost_center" name="allow_cost_center" value="1">
                    <label class="custom-control-label" for="allow_cost_center">يتطلب مركز تكلفة عند الترحيل</label>
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" checked>
                    <label class="custom-control-label" for="is_active">مفعّل</label>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ</button>
                <a href="{{ route('chart_of_accounts.index') }}" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
