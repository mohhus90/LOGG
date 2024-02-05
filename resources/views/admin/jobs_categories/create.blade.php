@extends('admin.layouts.admin')
@section('title')
الوظائف
@endsection
@section('start')
    الضبط العام
@endsection
@section('home')
<a href="{{ route('jobs_categories.index') }}">الوظائف</a>

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
              <form method="POST" action="{{ route('jobs_categories.store') }}">
                @csrf
                
                <div class="form-group form-inline">
                  
                  <label for="job_name" class="col-sm-2 col-form-label text-center"> اسم الوظيفة</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="job_name" id="job_name" value="{{ old('job_name') }}" >
                </div>
                </div>
                @error('job_name')
                <div class="text-danger text-center">{{ $message }}</div>
                @enderror           
                <div class="text-center">
                  <button type="submit" class="text-center btn btn-primary btn-lg col-2">اضافة</button>
                  <a class="btn btn-warning btn-lg col-2" href="{{ route('jobs_categories.index') }}">الغاء</a>
                </div>
              
              </form>
        </div>
    </div>
</div>
   
@endsection
