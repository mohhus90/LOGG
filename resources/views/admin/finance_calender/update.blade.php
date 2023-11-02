@extends('admin.layouts.admin')
@section('title')
تحديث السنة المالية
@endsection
@section('start')
    الضبط العام
@endsection
@section('home')
<a href="{{ route('finance_calender.edit',$data['id']) }}">تحديث السنوات المالية</a>

@endsection
@section('startpage')
تحديث
@endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">تحديث السنة المالية</h3>
        </div>
        <div class="card-body">
              <form action="{{ route('finance_calender.updatee',$data['id']) }}" >
                @csrf
                @method('PUT')
                <div class="form-group row">
                  <label for="finance_yr" class="col-sm-2 col-form-label "> السنة المالية</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="finance_yr" id="finance_yr" value="{{ old('finance_yr',$data['finance_yr']) }}" >
                  </div>
                  @error('finance_yr')
                  <span class="text-danger">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group row">
                  <label for="start_date" class="col-sm-2 col-form-label ">بداية السنة المالية</label>
                  <div class="col-sm-5">
                    <input type="date" class="form-control" name="start_date" id="start_date" value="{{ old('start_date',$data['start_date']) }}" >
                  </div>
                  @error('start_date')
                  <span class="text-danger">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group row">
                  <label for="end_date" class="col-sm-2 col-form-label ">نهاية السنة المالية</label>
                  <div class="col-sm-5">
                    <input type="date" class="form-control" name="end_date" id="end_date" value="{{ old('end_date',$data['end_date']) }}" >
                  </div>
                  @error('end_date')
                  <span class="text-danger">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group row">
                  <label for="is_open" class="col-sm-2 col-form-label ">اغلاق</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="is_open" id="is_open" value="{{ old('is_open',$data['is_open']) }}" >
                  </div>
                </div>  
                <div class="text-center">
                  <button type="submit" class="text-center btn btn-primary btn-lg col-2">تحديث</button>
                  <a class="btn btn-warning btn-lg col-2" href="{{ route('finance_calender.index') }}">الغاء</a>
                </div>
              </form>
        </div>
    </div>
</div>
   
@endsection
