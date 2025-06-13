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
                      <a class="nav-link" id="custom-content-below-job_data-tab" data-toggle="pill" href="#custom-content-below-job_data" role="tab" aria-controls="custom-content-below-job_data" aria-selected="false">بيانات الوظيفة</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" id="custom-content-below-other_data-tab" data-toggle="pill" href="#custom-content-below-other_data" role="tab" aria-controls="custom-content-below-other_data" aria-selected="false">بيانات اخرى</a>
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
                        <label for="employee_adress" class="col-sm-2 col-form-label text-center"> عنوان الموظف</label>
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
                          <option @if (old('emp_social_status')==2)selected @endif  value="3" > متزوج ويعول</option>
                        </select>
                      </div>
                      @error('emp_social_status')
                      <div class="text-danger text-center">{{ $message }}</div>
                      @enderror
                    </div>
                    {{-- job_data --}}
                    <div class="tab-pane fade" id="custom-content-below-job_data" role="tabpanel" aria-labelledby="custom-content-below-job_data-tab">
                      <br>
                      
                      <div class="form-group form-inline">
                        <label for="emp_start_date" class="col-sm-2 col-form-label text-center"> تاريخ الالتحاق</label>
                          <div class="col-sm-5">
                          <input type="date" class="form-control" name="emp_start_date" id="emp_start_date" value="{{ old('emp_start_date') }}" >
                          </div>
                      </div>
                      @error('emp_start_date')
                      <div class="text-danger text-center">{{ $message }}</div>
                      @enderror
                      
                      <div class="form-group form-inline">
                        <label for="functional_status" class="col-sm-2 col-form-label text-center"> حالة الموظف</label>
                        <select type="text" class="col-sm-3 form-select" aria-label="Disabled select example" name="type" id="type" >
                          <option selected value="1" > يعمل</option>
                      
                          <option @if (old('functional_status')==2)selected @endif  value="2" > لا يعمل </option>
                        </select>
                      </div>
                      @error('functional_status')
                      <div class="text-danger text-center">{{ $message }}</div>
                      @enderror
                      <div class="form-group form-inline">
                        <label for="resignation_status" class="col-sm-2 col-form-label text-center"> حالة ترك العمل</label>
                        <select type="text" class="col-sm-3 form-select" aria-label="Disabled select example" name="type" id="type" >
                          <option selected value="" > اختر الحالة</option>
                          <option @if (old('resignation_status')==1)selected @endif value="1" > استقالة</option>
                          <option @if (old('resignation_status')==2)selected @endif  value="2" > فصل</option>
                          <option @if (old('resignation_status')==2)selected @endif  value="3" > ترك العمل</option>
                          <option @if (old('resignation_status')==2)selected @endif  value="4" > سن المعاش</option>
                          <option @if (old('resignation_status')==2)selected @endif  value="5" > الوفاة</option>
                        </select>
                      </div>
                      @error('resignation_status')
                      <div class="text-danger text-center">{{ $message }}</div>
                      @enderror
                      <div class="form-group form-inline">
                          <label for="resignation_date" class="col-sm-2 col-form-label text-center"> تاريخ ترك العمل</label>
                            <div class="col-sm-5">
                            <input type="date" class="form-control" name="resignation_date" id="resignation_date" value="{{ old('resignation_date') }}" >
                            </div>
                      </div>
                      @error('resignation_date')
                      <div class="text-danger text-center">{{ $message }}</div>
                      @enderror

                      <div class="form-group form-inline">
                          <label for="resignation_cause" class="col-sm-2 col-form-label text-center"> سبب ترك العمل</label>
                            <div class="col-sm-5">
                            <input type="text" class="form-control" name="resignation_cause" id="resignation_cause" value="{{ old('resignation_cause') }}" >
                            </div>
                      </div>
                      @error('resignation_cause')
                      <div class="text-danger text-center">{{ $message }}</div>
                      @enderror

                    </div>


                  {{-- Other_data --}}
                    <div class="tab-pane fade" id="custom-content-below-other_data" role="tabpanel" aria-labelledby="custom-content-below-other_data-tab">
                      <br>
                      <div class="form-group form-inline">
                        <label for="emp_military_status" class="col-sm-2 col-form-label text-center"> الخدمة العسكرية</label>
                        <select type="text" class="col-sm-3 form-select" aria-label="Disabled select example" name="type" id="type" >
                          <option selected value="" > اختر الحالة</option>
                          <option @if (old('emp_military_status')==1)selected @endif value="1" > ادى الخدمة</option>
                          <option @if (old('emp_military_status')==2)selected @endif  value="2" > اعفاء</option>
                          <option @if (old('emp_military_status')==2)selected @endif  value="3" > مؤجل</option>
                        </select>
                      </div>
                      @error('emp_military_status')
                      <div class="text-danger text-center">{{ $message }}</div>
                      @enderror

                      <div class="form-group form-inline">
                        <label for="emp_qualification" class="col-sm-2 col-form-label text-center"> المؤهل الدراسى</label>
                          <div class="col-sm-5">
                          <input type="text" class="form-control" name="emp_qualification" id="emp_qualification" value="{{ old('emp_qualification') }}" >
                          </div>
                      </div>
                      @error('emp_qualification')
                      <div class="text-danger text-center">{{ $message }}</div>
                      @enderror
                      
                      <div class="form-group form-inline">
                        <label for="qualification_year" class="col-sm-2 col-form-label text-center"> سنة المؤهل </label>
                          <div class="col-sm-5">
                          <input type="text" class="form-control" name="qualification_year" id="qualification_year" value="{{ old('qualification_year') }}" >
                          </div>
                      </div>
                      @error('qualification_year')
                      <div class="text-danger text-center">{{ $message }}</div>
                      @enderror

                      <div class="form-group form-inline">
                        <label for="qualification_grade" class="col-sm-2 col-form-label text-center"> تقدير المؤهل</label>
                        <select type="text" class="col-sm-3 form-select" aria-label="Disabled select example" name="type" id="type" >
                          <option selected value="" > اختر التقدير</option>
                          <option @if (old('qualification_grade')==1)selected @endif value="1" > امتياز</option>
                          <option @if (old('qualification_grade')==2)selected @endif  value="2" > جيد جدا</option>
                          <option @if (old('qualification_grade')==2)selected @endif  value="3" > جيد مرتفع</option>
                          <option @if (old('qualification_grade')==2)selected @endif  value="4" > جيد</option>
                          <option @if (old('qualification_grade')==2)selected @endif  value="5" > مقبول</option>                     
                        </select>
                      </div>
                      @error('qualification_grade')
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
