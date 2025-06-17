@extends('admin.layouts.admin')
@section('title')
الوظائف
@endsection
@section('start')
الضبط العام
@endsection
@section('home')
<a href="{{ route('employees.index') }}"> الوظائف</a>

@endsection
@section('startpage')
    عرض
@endsection

@section('content')
  <div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">اضافة بيانات الوظائف من الاكسيل
            </h3>
        </div>

        <div class="card-body">
          <form enctype="multipart/form-data" action="{{ route('employees.douploadexcel') }}" method="post">
            @csrf
            <div class="col-ms-12">
              <div class="form-group">
                  <label >اختر ملف الاكسيل</label>
                  <input type="file" name="excel_file" id="excel_file" class="form-contrlo">
              </div>
              @error('excel_file')
               <div class="text-danger">{{ $message }}</div>
             @enderror
            </div>
            <div class="col-ms-12">
              <div class="form-group text-center">
                <button class="btn btn-sm btn-success" type="submit" name="submit" >ارفاق الملف</button>
                <a href="{{ route('employees.index') }}" class="btn btn-danger btn-sm">الغاء</a>
              </div>
            </div>
          </form>
    </div>
  </div>
   
@endsection

