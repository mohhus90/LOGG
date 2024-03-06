@extends('admin.layouts.admin')
@section('title')
الموظفين
@endsection
@section('start')
    شئون الموظفين
@endsection
@section('home')
<a href="{{ route('employees.index') }}">الموظفين</a>

@endsection
@section('startpage')
اضافة
@endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">اضافة موظف جديد</h3>
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
                        <label for="employee_id" class="col-sm-2 col-form-label text-center"> كود الموظف</label>
                        <div class="col-sm-5">
                          <input type="text" class="form-control" name="employee_id" id="employee_id" value="{{ old('employee_id') }}" >
                        </div>
                      </div>
                      @error('employee_id')
                      <div class="text-danger text-center">{{ $message }}</div>
                      @enderror           
                      <div class="form-group form-inline">
                        <label for="fiinger_id" class="col-sm-2 col-form-label text-center"> كود البصمة</label>
                          <div class="col-sm-5">
                          <input type="text" class="form-control" name="fiinger_id" id="fiinger_id" value="{{ old('fiinger_id') }}" >
                          </div>
                      </div>
                      @error('fiinger_id')
                      <div class="text-danger text-center">{{ $message }}</div>
                      @enderror           
                      <div class="form-group form-inline">
                        <label for="employee_name" class="col-sm-2 col-form-label text-center"> اسم الموظف</label>
                          <div class="col-sm-5">
                          <input type="text" class="form-control" name="employee_name" id="employee_name" value="{{ old('employee_name') }}" >
                          </div>
                      </div>
                      @error('employee_name')
                      <div class="text-danger text-center">{{ $message }}</div>
                      @enderror 
                      <div class="form-group form-inline">
                        <label for="employee_adress" class="col-sm-2 col-form-label text-center"> اسم الموظف</label>
                          <div class="col-sm-5">
                          <input type="text" class="form-control" name="employee_adress" id="employee_adress" value="{{ old('employee_adress') }}" >
                          </div>
                      </div>
                      @error('employee_adress')
                      <div class="text-danger text-center">{{ $message }}</div>
                      @enderror 
                      <div class="form-group form-inline">
                        <label for="emp_gender" class="col-sm-2 col-form-label text-center"> نوع الجنس</label>
                        <select type="text" class="col-sm-3 form-select" aria-label="Disabled select example" name="type" id="type" >
                          <option selected value="" > اختر النوع</option>
                          <option @if (old('emp_gender')==1)selected @endif value="1" > ذكر</option>
                          <option @if (old('emp_gender')==2)selected @endif  value="2" > انثى</option>
                        </select>
                      </div>
                      @error('emp_gender')
                      <div class="text-danger text-center">{{ $message }}</div>
                      @enderror
                      <div class="form-group form-inline">
                        <label for="emp_social_status" class="col-sm-2 col-form-label text-center"> الحالة الاجتماعية</label>
                        <select type="text" class="col-sm-3 form-select" aria-label="Disabled select example" name="type" id="type" >
                          <option selected value="" > اختر الحالة</option>
                          <option @if (old('emp_social_status')==1)selected @endif value="1" > اعزب</option>
                          <option @if (old('emp_social_status')==2)selected @endif  value="2" > متزوج</option>
                          <option @if (old('emp_social_status')==2)selected @endif  value="2" > متزوج ويعول</option>
                        </select>
                      </div>
                      @error('emp_social_status')
                      <div class="text-danger text-center">{{ $message }}</div>
                      @enderror
                    </div>
                  {{-- personnel_data --}}
                    <div class="tab-pane fade" id="custom-content-below-personnel_data" role="tabpanel" aria-labelledby="custom-content-below-personnel_data-tab">
                      <br>
                      <div class="form-group form-inline">
                        <label for="employee_id" class="col-sm-2 col-form-label text-center"> كود الموظف</label>
                        <div class="col-sm-5">
                          <input type="text" class="form-control" name="employee_id" id="employee_id" value="{{ old('employee_id') }}" >
                        </div>
                      </div>
                      @error('employee_id')
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
