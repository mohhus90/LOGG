@extends('admin.layouts.admin')
@section('title')
الادارات
@endsection
@section('start')
    الضبط العام
@endsection
@section('home')
<a href="{{ route('branches.index') }}">الادارات</a>

@endsection
@section('startpage')
اضافة
@endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">اضافة ادارة جديد</h3>
        </div>
        <div class="card-body">
              <form method="POST" action="{{ route('departs.store') }}">
                @csrf
                <div class="form-group row">
                  <label for="dep_name" class="col-sm-2 col-form-label "> اسم الادارة</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="dep_name" id="dep_name" value="{{ old('dep_name') }}" >
                  </div>
                  @error('dep_name')
                  <span class="text-danger">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group row">
                  <label for="phone" class="col-sm-2 col-form-label ">تليفون الادارة</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="phone" id="phone" value="{{ old('phone') }}" >
                  </div>
                  @error('phone')
                  <span class="text-danger">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group row">
                  <label for="notes" class="col-sm-2 col-form-label ">ملاحظات</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="notes" id="notes" value="{{ old('notes') }}" >
                  </div>
                </div>
                <div class="form-group row">
                  <label for="email" class="col-sm-2 col-form-label ">ايميل الادارة</label>
                  <div class="col-sm-5">
                    <input type="email" class="form-control" name="email" id="email" value="{{ old('email') }}" >
                  </div>
                </div>
                <div class="text-center">
                  <button type="submit" class="text-center btn btn-primary btn-lg col-2">اضافة</button>
                  <a class="btn btn-warning btn-lg col-2" href="{{ route('departs.index') }}">الغاء</a>
                </div>
                
              </form>
        </div>
    </div>
</div>
   
@endsection
