@extends('admin.layouts.treasury')
@section('title') تعديل حساب بنكي @endsection
@section('start') الخزينة @endsection
@section('home') <a href="{{ route('bank_accounts.index') }}">الحسابات البنكية</a> @endsection
@section('startpage') تعديل @endsection

@section('content')
<div class="col-lg-7 col-md-9">
    <div class="card card-primary card-outline">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-edit ml-2"></i> تعديل حساب بنكي</h3></div>
        <form action="{{ route('bank_accounts.update', $account->id) }}" method="POST">
            @csrf
            <div class="card-body">
                @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>اسم البنك <span class="text-danger">*</span></label>
                            <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $account->bank_name) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>اسم الحساب <span class="text-danger">*</span></label>
                            <input type="text" name="account_name" class="form-control" value="{{ old('account_name', $account->account_name) }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>رقم الحساب</label>
                            <input type="text" name="account_number" class="form-control" value="{{ old('account_number', $account->account_number) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>IBAN</label>
                            <input type="text" name="iban" class="form-control" value="{{ old('iban', $account->iban) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Swift Code</label>
                            <input type="text" name="swift_code" class="form-control" value="{{ old('swift_code', $account->swift_code) }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>الفرع (اختياري)</label>
                            <select name="branch_id" class="form-control select2">
                                <option value="">-- بدون --</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" {{ $account->branch_id == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>الحساب المحاسبي المرتبط <span class="text-danger">*</span></label>
                            <select name="gl_account_id" class="form-control select2" required>
                                <option value="">-- اختر الحساب --</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}" {{ $account->gl_account_id == $acc->id ? 'selected' : '' }}>{{ $acc->account_code }} - {{ $acc->account_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ $account->is_active ? 'checked' : '' }}>
                    <label class="custom-control-label" for="is_active">مفعّل</label>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ</button>
                <a href="{{ route('bank_accounts.index') }}" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
