@extends('admin.layouts.admin')
@section('title')
الوظائف
@endsection
@section('start')
    الضبط العام
@endsection
@section('home')
<a href="{{ route('employees.index') }}">الوظائف</a>

@endsection
@section('startpage')
اضافة
@endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">اضافة وظيفة جديد</h3>
        </div>
        <div class="card-body">
              <form method="POST" action="{{ route('employees.store') }}">
                @csrf
                <div class="card-body">
                  <ul class="nav nav-tabs" id="custom-content-below-tab" role="tablist">
                    <li class="nav-item">
                      <a class="nav-link active" id="custom-content-below-baisc_data-tab" data-toggle="pill" href="#custom-content-below-baisc_data" role="tab" aria-controls="custom-content-below-baisc_data" aria-selected="true">بيانات اساسية</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" id="custom-content-below-personnel_data-tab" data-toggle="pill" href="#custom-content-below-personnel_data" role="tab" aria-controls="custom-content-below-personnel_data" aria-selected="false">بيانات اخرى</a>
                    </li>
                  </ul>
                  <div class="tab-content" id="custom-content-below-tabContent">
                    <div class="tab-pane fade show active" id="custom-content-below-baisc_data" role="tabpanel" aria-labelledby="custom-content-below-baisc_data-tab">
                      <br>
                      <div class="form-group form-inline">
                        <label for="job_name" class="col-sm-2 col-form-label text-center"> اسم الوظيفة</label>
                        <div class="col-sm-5">
                          <input type="text" class="form-control" name="job_name" id="job_name" value="{{ old('job_name') }}" >
                        </div>
                      </div>
                      @error('job_name')
                      <div class="text-danger text-center">{{ $message }}</div>
                      @enderror           

                    </div>
                    <div class="tab-pane fade" id="custom-content-below-personnel_data" role="tabpanel" aria-labelledby="custom-content-below-personnel_data-tab">
                      <br>
                      <div class="form-group form-inline">
                        <label for="job_name" class="col-sm-2 col-form-label text-center"> اسم الوظيفة</label>
                        <div class="col-sm-5">
                          <input type="text" class="form-control" name="job_name" id="job_name" value="{{ old('job_name') }}" >
                        </div>
                      </div>
                      @error('job_name')
                      <div class="text-danger text-center">{{ $message }}</div>
                      @enderror       
                    </div>
                  </div>
                  <div class="text-center">
                    <button type="submit" class="text-center btn btn-primary btn-lg col-2">اضافة</button>
                    <a class="btn btn-warning btn-lg col-2" href="{{ route('employees.index') }}">الغاء</a>
                  </div>
              
              </form>
        </div>
    </div>
</div>
   
@endsection
