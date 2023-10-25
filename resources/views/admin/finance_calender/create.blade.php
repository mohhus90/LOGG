@extends('admin.layouts.admin')
@section('title')
    الضبط العام
@endsection
@section('start')
    الضبط العام
@endsection
@section('home')
<a href="{{ route('generalsetting.index') }}">السنوات المالية</a>

@endsection
@section('startpage')
اضافة
@endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">تكويد سنة مالية جديدة</h3>
        </div>
        <div class="card-body">
              <form method="POST" action="{{ route('finance_calender.store') }}">
                @csrf
                <div class="form-group row">
                  <label for="finance_yr" class="col-sm-2 col-form-label "> السنة المالية</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="finance_yr" id="finance_yr" value="{{ old('finance_yr') }}" >
                  </div>
                  @error('finance_yr')
                  <span class="text-danger">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group row">
                  <label for="start_date" class="col-sm-2 col-form-label ">بداية السنة المالية</label>
                  <div class="col-sm-5">
                    <input type="date" class="form-control" name="start_date" id="start_date" value="{{ old('start_date') }}" >
                  </div>
                  @error('start_date')
                  <span class="text-danger">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group row">
                  <label for="end_date" class="col-sm-2 col-form-label ">نهاية السنة المالية</label>
                  <div class="col-sm-5">
                    <input type="date" class="form-control" name="end_date" id="end_date" value="{{ old('end_date') }}" >
                  </div>
                  @error('end_date')
                  <span class="text-danger">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group row">
                  <label for="is_open" class="col-sm-2 col-form-label ">اغلاق</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="is_open" id="is_open" value="{{ old('is_open') }}" >
                  </div>
                </div>  
                <div class="row">
                  <button type="submit" class="text-center" colspan="2" ><a class="btn btn-primary btn-lg col-2">اضافة</a></button>
                </div>
                
              </form>
        </div>
    </div>
</div>
   
@endsection
