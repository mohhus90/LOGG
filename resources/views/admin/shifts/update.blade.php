@extends('admin.layouts.admin')
@section('title')
الفروع
@endsection
@section('start')
    الضبط العام
@endsection
@section('home')
<a href="{{ route('shifts.index') }}">الفروع</a>

@endsection
@section('startpage')
تعديل
@endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">تعديل فرع</h3>
        </div>
        <div class="card-body">
              <form method="POST" action="{{ route('shifts.update',$data['id']) }}">
                @csrf
                <div class="form-group row">
                  <label for="branch_name" class="col-sm-2 col-form-label "> اسم الفرع</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="branch_name" id="branch_name" value="{{ old('branch_name',$data['branch_name']) }}" >
                  </div>
                  @error('branch_name')
                  <span class="text-danger">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group row">
                  <label for="address" class="col-sm-2 col-form-label ">عنوان الفرع</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="address" id="address" value="{{ old('address',$data['address']) }}" >
                  </div>
                  @error('address')
                  <span class="text-danger">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group row">
                  <label for="phone" class="col-sm-2 col-form-label ">تليفون الفرع</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="phone" id="phone" value="{{ old('phone',$data['phone']) }}" >
                  </div>
                </div>
                <div class="form-group row">
                  <label for="email" class="col-sm-2 col-form-label ">ايميل الفرع</label>
                  <div class="col-sm-5">
                    <input type="email" class="form-control" name="email" id="email" value="{{ old('email',$data['email']) }}" >
                  </div>
                </div>
                <div class="form-group row">
                  <label for="active" class="col-sm-2 col-form-label ">حالة الفرع</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="active" id="active" value="{{ old('active',$data['active']) }}" >
                  </div>
                </div>  
                <div class="text-center">
                  <button type="submit" class="text-center btn btn-primary btn-lg col-2">تحديث</button>
                  <a class="btn btn-warning btn-lg col-2" href="{{ route('shifts.index') }}">الغاء</a>
                </div>
                
              </form>
        </div>
    </div>
</div>
   
@endsection
