@extends('admin.layouts.system')
@section('title') تعديل شركة @endsection
@section('start') النظام @endsection
@section('home') <a href="{{ route('companies.index') }}">سجل الشركات</a> @endsection
@section('startpage') تعديل @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-city ml-2"></i> تعديل بيانات: {{ $company->name }}</h3>
        </div>
        <form method="POST" action="{{ route('companies.update', $company->id) }}">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>اسم الشركة <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" value="{{ old('name', $company->name) }}" required>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>الهاتف</label>
                        <input type="text" class="form-control" name="phone" value="{{ old('phone', $company->phone) }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>البريد الإلكتروني</label>
                        <input type="email" class="form-control" name="email" value="{{ old('email', $company->email) }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8 form-group">
                        <label>العنوان</label>
                        <input type="text" class="form-control" name="address" value="{{ old('address', $company->address) }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="d-block">حالة الشركة</label>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                                {{ old('is_active', $company->is_active) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">مفعّلة</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ</button>
                <a href="{{ route('companies.index') }}" class="btn btn-secondary mr-2">رجوع</a>
            </div>
        </form>
    </div>
</div>
@endsection
