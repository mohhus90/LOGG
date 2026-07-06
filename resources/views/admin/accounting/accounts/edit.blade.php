@extends('admin.layouts.accounting')
@section('title') تعديل حساب @endsection
@section('start') المحاسبة @endsection
@section('home') <a href="{{ route('chart_of_accounts.index') }}">دليل الحسابات</a> @endsection
@section('startpage') تعديل @endsection

@section('content')
<div class="col-lg-7 col-md-9">
    <div class="card card-primary card-outline">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-edit ml-2"></i> تعديل حساب: {{ $account->account_code }}</h3></div>
        <form action="{{ route('chart_of_accounts.update', $account->id) }}" method="POST">
            @csrf
            <div class="card-body">
                @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>رقم الحساب</label>
                            <input type="text" class="form-control" value="{{ $account->account_code }}" disabled>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>اسم الحساب بالعربية <span class="text-danger">*</span></label>
                            <input type="text" name="account_name" class="form-control" value="{{ old('account_name', $account->account_name) }}" required>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>اسم الحساب بالإنجليزية</label>
                            <input type="text" name="account_name_en" class="form-control" value="{{ old('account_name_en', $account->account_name_en) }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>نوع الحساب <span class="text-danger">*</span></label>
                            <select name="account_type" class="form-control" required>
                                @foreach(['asset'=>'أصول','liability'=>'التزامات','equity'=>'حقوق ملكية','revenue'=>'إيرادات','expense'=>'مصروفات'] as $val=>$label)
                                    <option value="{{ $val }}" {{ $account->account_type == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>طبيعة الحساب</label>
                            <select name="account_nature" class="form-control">
                                <option value="debit" {{ $account->account_nature=='debit'?'selected':'' }}>مدين</option>
                                <option value="credit" {{ $account->account_nature=='credit'?'selected':'' }}>دائن</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>الحساب الأب</label>
                            <select name="parent_id" class="form-control select2">
                                <option value="">-- بدون --</option>
                                @foreach($groups as $g)
                                    <option value="{{ $g->id }}" {{ old('parent_id', $account->parent_id) == $g->id ? 'selected' : '' }}>
                                        {{ $g->account_code }} - {{ $g->account_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>ملاحظات</label>
                            <input type="text" name="notes" class="form-control" value="{{ old('notes', $account->notes) }}">
                        </div>
                    </div>
                </div>
                <div class="custom-control custom-switch mb-2">
                    <input type="checkbox" class="custom-control-input" id="is_group" name="is_group" value="1" {{ $account->is_group ? 'checked' : '' }}>
                    <label class="custom-control-label" for="is_group">حساب رئيسي (مجموعة)</label>
                </div>
                <div class="custom-control custom-switch mb-2">
                    <input type="checkbox" class="custom-control-input" id="allow_cost_center" name="allow_cost_center" value="1" {{ $account->allow_cost_center ? 'checked' : '' }}>
                    <label class="custom-control-label" for="allow_cost_center">يتطلب مركز تكلفة عند الترحيل</label>
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ $account->is_active ? 'checked' : '' }}>
                    <label class="custom-control-label" for="is_active">مفعّل</label>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ التعديلات</button>
                <a href="{{ route('chart_of_accounts.index') }}" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
