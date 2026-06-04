@extends('admin.layouts.admin')
@section('title') السنوات المالية @endsection
@section('start') الضبط العام @endsection
@section('home') <a href="{{ route('finance_calender.index') }}">السنوات المالية</a> @endsection
@section('startpage') إضافة @endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">إضافة سنة مالية جديدة</h3>
        </div>
        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('errorUpdate'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    {{ session('errorUpdate') }}
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('finance_calender.store') }}">
                @csrf

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label">السنة المالية <span class="text-danger">*</span></label>
                    <div class="col-sm-5">
                        <input type="text" class="form-control" name="finance_yr"
                            value="{{ old('finance_yr') }}" placeholder="مثال: 2025">
                        @error('finance_yr')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label">تاريخ البداية <span class="text-danger">*</span></label>
                    <div class="col-sm-5">
                        <input type="date" class="form-control" name="start_date"
                            value="{{ old('start_date') }}">
                        @error('start_date')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label">تاريخ النهاية <span class="text-danger">*</span></label>
                    <div class="col-sm-5">
                        <input type="date" class="form-control" name="end_date"
                            value="{{ old('end_date') }}">
                        @error('end_date')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label">الحالة</label>
                    <div class="col-sm-5">
                        <select class="form-control" name="is_open">
                            <option value="0">مفتوح</option>
                            <option value="1">مغلق</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-sm-12 text-center mt-2">
                        <button type="submit" class="btn btn-primary col-md-3">
                            <i class="fas fa-save ml-1"></i> حفظ
                        </button>
                        <a href="{{ route('finance_calender.index') }}" class="btn btn-warning col-md-2 mr-2">
                            إلغاء
                        </a>
                    </div>
                </div>

            </form>

        </div>
    </div>
</div>
@endsection
