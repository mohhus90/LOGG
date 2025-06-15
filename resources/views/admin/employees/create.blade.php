@extends('admin.layouts.admin')
@section('title')
الموظفين
@endsection
@section('start')
    شئون الموظفين
@endsection

@section('css')
<!-- في الـ head -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css">
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
                    <li class="nav-item">
                      <a class="nav-link" id="custom-content-below-Salary_data-tab" data-toggle="pill" href="#custom-content-below-Salary_data" role="tab" aria-controls="custom-content-below-Salary_data" aria-selected="false">بيانات الراتب</a>
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
                        <label for="finger_id" class="col-sm-2 col-form-label text-center"> كود البصمة</label>
                          <div class="col-sm-5">
                          <input type="text" class="form-control" name="finger_id" id="finger_id" value="{{ old('finger_id') }}" >
                          </div>
                      </div>
                      @error('finger_id')
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
                        <label for="national_id" class="col-sm-2 col-form-label text-center"> الرقم القومى</label>
                          <div class="col-sm-5">
                          <input type="text" class="form-control" name="national_id" id="national_id" value="{{ old('national_id') }}" >
                          </div>
                      </div>
                      @error('national_id')
                      <div class="text-danger text-center">{{ $message }}</div>
                      @enderror 
                      <div class="form-group form-inline">
                        <label for="emp_mobile" class="col-sm-2 col-form-label text-center"> موبيل</label>
                          <div class="col-sm-5">
                          <input type="text" class="form-control" name="emp_mobile" id="emp_mobile" value="{{ old('emp_mobile') }}" >
                          </div>
                      </div>
                      @error('emp_mobile')
                      <div class="text-danger text-center">{{ $message }}</div>
                      @enderror 
                      <div class="form-group form-inline">
                        <label for="emp_home_tel" class="col-sm-2 col-form-label text-center"> تليفون المنزل</label>
                          <div class="col-sm-5">
                          <input type="text" class="form-control" name="emp_home_tel" id="emp_home_tel" value="{{ old('emp_home_tel') }}" >
                          </div>
                      </div>
                      @error('emp_home_tel')
                      <div class="text-danger text-center">{{ $message }}</div>
                      @enderror 
                    
                      <div class="form-group form-inline">
                        <label for="emp_email" class="col-sm-2 col-form-label text-center"> Email </label>
                          <div class="col-sm-5">
                          <input type="email" class="form-control" name="emp_email" id="emp_email" value="{{ old('emp_email') }}" >
                          </div>
                      </div>
                      @error('emp_email')
                      <div class="text-danger text-center">{{ $message }}</div>
                      @enderror 
                      <div class="form-group form-inline">
                        <label for="birth_date" class="col-sm-2 col-form-label text-center"> تاريخ الميلاد </label>
                          <div class="col-sm-5">
                          <input type="date" class="form-control" name="birth_date" id="birth_date" value="{{ old('birth_date') }}" >
                          </div>
                      </div>
                      @error('birth_date')
                      <div class="text-danger text-center">{{ $message }}</div>
                      @enderror 
                      <div class="form-group form-inline">
                        <label for="emp_gender" class="col-sm-2 col-form-label text-center"> نوع الجنس</label>
                        <select type="text" class="col-sm-2 form-select" aria-label="Disabled select example" name="type" id="type" >
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
                        <select type="text" class="col-sm-2 form-select" aria-label="Disabled select example" name="type" id="type" >
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
                        <select type="text" class="col-sm-2 form-select" aria-label="Disabled select example" name="type" id="type" >
                          <option selected value="1" > يعمل</option>
                      
                          <option @if (old('functional_status')==2)selected @endif  value="2" > لا يعمل </option>
                        </select>
                      </div>
                      @error('functional_status')
                      <div class="text-danger text-center">{{ $message }}</div>
                      @enderror

                      <div class="form-group form-inline">
                          <label for="emp_jobs_id" class="col-sm-2 col-form-label text-center">الوظيفة</label>
                          
                              <select name="emp_jobs_id" id="emp_jobs_id" class="col-sm-2 select2 form-select">
                                  <option value="">اختر الوظيفة</option>
                                  @foreach($jobs_categories as $job)
                                      <option value="{{ $job->id }}" {{ old('emp_jobs_id') == $job->id ? 'selected' : '' }}>
                                          {{ $job->job_name }}
                                      </option>
                                  @endforeach
                              </select>
                              @error('emp_jobs_id')
                                  <div class="text-danger text-center">{{ $message }}</div>
                              @enderror
                          
                      </div>

                     <div class="form-group form-inline">
                          <label for="emp_departments_id" class="col-sm-2 col-form-label text-center">الادارة</label>
                          <select name="emp_departments_id" id="emp_departments_id" class="col-sm-2 select2 form-select">
                              <option value="">اختر الادارة</option>
                              @foreach($departments as $department)
                                  <option value="{{ $department->id }}" {{ old('emp_departments_id') == $department->id ? 'selected' : '' }}>
                                      {{ $department->dep_name}}
                                  </option>
                              @endforeach
                          </select>
                      </div>
                      @error('emp_departments_id')
                          <div class="text-danger text-center">{{ $message }}</div>
                      @enderror
                      <div class="form-group form-inline">
                          <label for="shifts_types_id" class="col-sm-2 col-form-label text-center">الشيفت</label>
                          <select name="shifts_types_id" id="shifts_types_id" class="col-sm-2 select2 form-select">
                              <option value="">اختر الشيفت</option>
                              @foreach($shifts_types as $shifts_type)
                                  <option value="{{ $shifts_type->id }}" {{ old('shifts_types_id') == $shifts_type->id ? 'selected' : '' }}>
                                      {{ $shifts_type->from_time}}
                                  </option>
                              @endforeach
                          </select>
                      </div>
                      @error('shifts_types_id')
                          <div class="text-danger text-center">{{ $message }}</div>
                      @enderror
                      <div class="form-group form-inline">
                          <label for="branches_id" class="col-sm-2 col-form-label text-center">الفرع</label>
                          <select name="branches_id" id="branches_id" class="col-sm-2 select2 form-select">
                              <option value="">اختر الفرع</option>
                              @foreach($branches as $branche)
                                  <option value="{{ $branche->id }}" {{ old('branches_id') == $branche->id ? 'selected' : '' }}>
                                      {{ $branche->branch_name}}
                                  </option>
                              @endforeach
                          </select>
                      </div>
                      @error('branches_id')
                          <div class="text-danger text-center">{{ $message }}</div>
                      @enderror


                      <div class="form-group form-inline">
                        <label for="daily_work_hours" class="col-sm-2 col-form-label text-center"> عدد ساعات العمل</label>
                          <div class="col-sm-5">
                          <input type="number" class="form-control" name="daily_work_hours" id="daily_work_hours" value="{{ old('daily_work_hours') }}" >
                          </div>
                      </div>
                      @error('daily_work_hours')
                      <div class="text-danger text-center">{{ $message }}</div>
                      @enderror

                      <div class="form-group form-inline">
                        <label for="resignation_status" class="col-sm-2 col-form-label text-center"> حالة ترك العمل</label>
                        <select type="text" class="col-sm-2 form-select" aria-label="Disabled select example" name="type" id="type" >
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
                        <select type="text" class="col-sm-2 form-select" aria-label="Disabled select example" name="type" id="type" >
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
                        <select type="text" class="col-sm-2 form-select" aria-label="Disabled select example" name="type" id="type" >
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
                  
                      {{-- Salary_data --}}
                    <div class="tab-pane fade" id="custom-content-below-Salary_data" role="tabpanel" aria-labelledby="custom-content-below-Salary_data-tab">
                      <br>
                      <div class="form-group form-inline">
                        <label for="emp_sal" class="col-sm-2 col-form-label text-center"> الراتب الاساسى</label>
                          <div class="col-sm-5">
                          <input type="number" class="form-control" name="emp_sal" id="emp_sal" value="{{ old('emp_sal') }}" >
                          </div>
                      </div>
                      @error('emp_sal')
                      <div class="text-danger text-center">{{ $message }}</div>
                      @enderror
                      <div class="form-group form-inline">
                        <label for="emp_sal_insurance" class="col-sm-2 col-form-label text-center"> الراتب التأمينى</label>
                          <div class="col-sm-5">
                          <input type="number" class="form-control" name="emp_sal_insurance" id="emp_sal_insurance" value="{{ old('emp_sal_insurance') }}" >
                          </div>
                      </div>
                      @error('emp_sal_insurance')
                      <div class="text-danger text-center">{{ $message }}</div>
                      @enderror
                      
                      <div class="form-group form-inline">
                        <label for="emp_fixed_allowances" class="col-sm-2 col-form-label text-center"> علاوة ثابتة</label>
                          <div class="col-sm-5">
                          <input type="number" class="form-control" name="emp_fixed_allowances" id="emp_fixed_allowances" value="{{ old('emp_fixed_allowances') }}" >
                          </div>
                      </div>
                      @error('emp_fixed_allowances')
                      <div class="text-danger text-center">{{ $message }}</div>
                      @enderror
                      <div class="form-group form-inline">
                        <label for="mtivation" class="col-sm-2 col-form-label text-center"> الحافز</label>
                          <div class="col-sm-5">
                          <input type="number" class="form-control" name="mtivation" id="mtivation" value="{{ old('mtivation') }}" >
                          </div>
                      </div>
                      @error('mtivation')
                      <div class="text-danger text-center">{{ $message }}</div>
                      @enderror
                      <div class="form-group form-inline">
                        <label for="medical_insurance" class="col-sm-2 col-form-label text-center"> التأمين الصحى الخاص</label>
                          <div class="col-sm-5">
                          <input type="number" class="form-control" name="medical_insurance" id="medical_insurance" value="{{ old('medical_insurance') }}" >
                          </div>
                      </div>
                      @error('medical_insurance')
                      <div class="text-danger text-center">{{ $message }}</div>
                      @enderror

                      <div class="form-group form-inline">
                        <label for="sal_cash_visa" class="col-sm-2 col-form-label text-center"> طريقة الدفع</label>
                        <select type="text" class="col-sm-2 form-select" aria-label="Disabled select example" name="type" id="type" >
                          <option selected value="" > اختر طريقة الدفع</option>
                          <option @if (old('sal_cash_visa')==1)selected @endif value="1" > كاش</option>
                          <option @if (old('sal_cash_visa')==2)selected @endif  value="2" > فيزا</option>
                        </select>
                      </div>
                      @error('sal_cash_visa')
                      <div class="text-danger text-center">{{ $message }}</div>
                      @enderror
                      <div class="form-group form-inline">
                        <label for="bank_name" class="col-sm-2 col-form-label text-center"> Bank account </label>
                          <div class="col-sm-5">
                          <input type="text" class="form-control" name="bank_name" id="bank_name" value="{{ old('bank_name') }}" >
                          </div>
                      </div>
                      @error('bank_name')
                      <div class="text-danger text-center">{{ $message }}</div>
                      @enderror
                      <div class="form-group form-inline">
                        <label for="bank_account" class="col-sm-2 col-form-label text-center"> Bank account </label>
                          <div class="col-sm-5">
                          <input type="text" class="form-control" name="bank_account" id="bank_account" value="{{ old('bank_account') }}" >
                          </div>
                      </div>
                      @error('bank_account')
                      <div class="text-danger text-center">{{ $message }}</div>
                      @enderror
                      <div class="form-group form-inline">
                        <label for="bank_ID" class="col-sm-2 col-form-label text-center"> Bank ID </label>
                          <div class="col-sm-5">
                          <input type="text" class="form-control" name="bank_ID" id="bank_ID" value="{{ old('bank_ID') }}" >
                          </div>
                      </div>
                      @error('bank_ID')
                      <div class="text-danger text-center">{{ $message }}</div>
                      @enderror

                      <div class="form-group form-inline">
                        <label for="bank_branch" class="col-sm-2 col-form-label text-center"> Bank branch </label>
                          <div class="col-sm-5">
                          <input type="text" class="form-control" name="bank_branch" id="bank_branch" value="{{ old('bank_branch') }}" >
                          </div>
                      </div>
                      @error('bank_branch')
                      <div class="text-danger text-center">{{ $message }}</div>
                      @enderror


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

@section("script")


<!-- قبل نهاية الـ body -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4',
            
        });
    });
</script>
@endsection