@extends('admin.layouts.admin')
@section('title') تعديل بيانات العميل @endsection
@section('start') الإعدادات @endsection
@section('home') <a href="{{ route('clients.index') }}">العملاء</a> @endsection
@section('startpage') تعديل @endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">تعديل بيانات العميل: {{ $data->client_name }}</h3>
        </div>
        <div class="card-body">
            @if(session('errorUpdate'))
                <div class="alert alert-danger">{{ session('errorUpdate') }}</div>
            @endif

            <form method="POST" action="{{ route('clients.update', $data->id) }}">
                @csrf
                <div class="form-group row mb-3">
                    <label class="col-sm-2 col-form-label">اسم العميل (English) <span class="text-danger">*</span></label>
                    <div class="col-sm-5">
                        <input type="text" class="form-control" name="client_name"
                               value="{{ old('client_name', $data->client_name) }}" required>
                        @error('client_name')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="form-group row mb-3">
                    <label class="col-sm-2 col-form-label">اسم العميل (عربي)</label>
                    <div class="col-sm-5">
                        <input type="text" class="form-control" name="client_name_A"
                               value="{{ old('client_name_A', $data->client_name_A) }}">
                    </div>
                </div>
                <div class="form-group row mb-3">
                    <label class="col-sm-2 col-form-label">جهة الاتصال</label>
                    <div class="col-sm-5">
                        <input type="text" class="form-control" name="contact_person"
                               value="{{ old('contact_person', $data->contact_person) }}">
                    </div>
                </div>
                <div class="form-group row mb-3">
                    <label class="col-sm-2 col-form-label">الهاتف</label>
                    <div class="col-sm-5">
                        <input type="text" class="form-control" name="phone"
                               value="{{ old('phone', $data->phone) }}">
                    </div>
                </div>
                <div class="form-group row mb-3">
                    <label class="col-sm-2 col-form-label">البريد الإلكتروني</label>
                    <div class="col-sm-5">
                        <input type="email" class="form-control" name="email"
                               value="{{ old('email', $data->email) }}">
                    </div>
                </div>
                <div class="form-group row mb-3">
                    <label class="col-sm-2 col-form-label">القطاع / المجال</label>
                    <div class="col-sm-5">
                        <input type="text" class="form-control" name="industry"
                               value="{{ old('industry', $data->industry) }}">
                    </div>
                </div>
                <div class="form-group row mb-3">
                    <label class="col-sm-2 col-form-label">العنوان</label>
                    <div class="col-sm-5">
                        <textarea class="form-control" name="address" rows="2">{{ old('address', $data->address) }}</textarea>
                    </div>
                </div>
                <div class="form-group row mb-3">
                    <label class="col-sm-2 col-form-label">ملاحظات</label>
                    <div class="col-sm-5">
                        <textarea class="form-control" name="notes" rows="2">{{ old('notes', $data->notes) }}</textarea>
                    </div>
                </div>
                <div class="form-group row mb-3">
                    <label class="col-sm-2 col-form-label">الحالة</label>
                    <div class="col-sm-5">
                        <select class="form-control" name="active">
                            <option value="1" @selected(old('active', $data->active) == 1)>مفعّل</option>
                            <option value="0" @selected(old('active', $data->active) == 0)>معطّل</option>
                        </select>
                    </div>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-lg col-2">حفظ التعديلات</button>
                    <a class="btn btn-warning btn-lg col-2" href="{{ route('clients.index') }}">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
