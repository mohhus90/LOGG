@extends('admin.layouts.admin')

@section('title')
بيانات السنوى
@endsection

@section('start')
قائمة السنوى
@endsection

@section('css')
<style>
    .tab-content {
        padding: 15px;
        border-left: 1px solid #dee2e6;
        border-right: 1px solid #dee2e6;
        border-bottom: 1px solid #dee2e6;
        border-radius: 0 0 0.25rem 0.25rem;
    }
</style>
@endsection

@section('home')
<a href="{{ route('Main_vacations_balance.index') }}">رصيد السنوى</a>
@endsection

@section('startpage')
اضافة
@endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">بيانات رصيد الموظفين السنوى</h3>
        </div>
        <div class="card-body">
                <div class="card-body">
                    <ul class="nav nav-tabs" id="custom-content-below-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="custom-content-below-baisc_data-tab" data-toggle="pill" href="#custom-content-below-baisc_data" role="tab" aria-controls="custom-content-below-baisc_data" aria-selected="true">بيانات الرصيد</a>
                        </li>
                    </ul>

                    <div class="tab-content" id="custom-content-below-tabContent">
                        {{-- بيانات اساسية --}}
                        <div class="tab-pane fade show active" id="custom-content-below-baisc_data" role="tabpanel" aria-labelledby="custom-content-below-baisc_data-tab">
                            <br>
                            <div class="row"> {{-- Start a Bootstrap row for grouping inputs --}}
                                <div class="col-md-4"> {{-- Each input will take 4 columns (12/3 = 4) --}}
                                    <div class=""> {{-- Removed form-inline, it's not ideal with col grid --}}
                                        <label for="employee_id">كود الموظف</label>
                                        <input disabled  type="text" class="form-control" name="employee_id" id="employee_id" value="{{  old('employee_id',$data['employee_id']) }}">
                                        @error('employee_id')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="finger_id">كود البصمة</label>
                                        <input disabled  type="text" class="form-control" name="finger_id" id="finger_id" value="{{  old('finger_id',$data['finger_id']) }}">
                                        @error('finger_id')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="employee_name_A">اسم الموظف رباعى</label>
                                        <input disabled  type="text" class="form-control" name="employee_name_A" id="employee_name_A" value="{{  old('employee_name_A',$data['employee_name_A']) }}">
                                        @error('employee_name_A')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="employee_name_E">اسم الموظف انجليزى</label>
                                        <input disabled  type="text" class="form-control" name="employee_name_E" id="employee_name_E" value="{{  old('employee_name_E',$data['employee_name_E']) }}">
                                        @error('employee_name_E')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="emp_start_date">تاريخ الالتحاق</label>
                                        <input disabled  type="date" class="form-control" name="emp_start_date" id="emp_start_date" value="{{  old('emp_start_date',$data['emp_start_date']) }}">
                                        @error('emp_start_date')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="functional_status">الحالة التامينية</label>
                                        <select disabled  class="form-control" name="functional_status" id="functional_status">
                                            <option value="1" @if ( old('functional_status',$data['functional_status'])==1)selected @endif>يعمل</option>
                                            <option value="2" @if ( old('functional_status',$data['functional_status'])==2)selected @endif>لا يعمل</option>
                                        </select>
                                        @error('functional_status')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="emp_jobs_id">الوظيفة</label>
                                        <select disabled  name="emp_jobs_id" id="emp_jobs_id" class="form-control">
                                            <option value=""> الوظيفة</option>
                                            @foreach($jobs_categories as $job)
                                                <option value="{{ $job->id }}" {{  old('emp_jobs_id',$data['emp_jobs_id']) == $job->id ? 'selected' : '' }}>
                                                    {{ $job->job_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('emp_jobs_id')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="emp_departments_id">الادارة</label>
                                        <select disabled  name="emp_departments_id" id="emp_departments_id" class="form-control">
                                            <option value=""> الادارة</option>
                                            @foreach($departments as $department)
                                                <option value="{{ $department->id }}" {{  old('emp_departments_id',$data['emp_departments_id']) == $department->id ? 'selected' : '' }}>
                                                    {{ $department->dep_name}}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('emp_departments_id')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="branches_id">الفرع</label>
                                        <select disabled  name="branches_id" id="branches_id" class="form-control">
                                            <option value=""> الفرع</option>
                                            @foreach($branches as $branche)
                                                <option value="{{ $branche->id }}" {{  old('branches_id',$data['branches_id']) == $branche->id ? 'selected' : '' }}>
                                                    {{ $branche->branch_name}}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('branches_id')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="resignation_status">حالة ترك العمل</label>
                                        <select disabled  class="form-control" name="resignation_status" id="resignation_status">
                                            <option value=""> الحالة</option>
                                            <option value="1" @if ( old('resignation_status',$data['resignation_status'])==1)selected @endif>استقالة</option>
                                            <option value="2" @if ( old('resignation_status',$data['resignation_status'])==2)selected @endif>فصل</option>
                                            <option value="3" @if ( old('resignation_status',$data['resignation_status'])==3)selected @endif>ترك العمل</option>
                                            <option value="4" @if ( old('resignation_status',$data['resignation_status'])==4)selected @endif>سن المعاش</option>
                                            <option value="5" @if ( old('resignation_status',$data['resignation_status'])==5)selected @endif>الوفاة</option>
                                        </select>
                                        @error('resignation_status')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="resignation_date">تاريخ ترك العمل</label>
                                        <input disabled  type="date" class="form-control" name="resignation_date" id="resignation_date" value="{{  old('resignation_date',$data['resignation_date']) }}">
                                        @error('resignation_date')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</div>
<div class="card-title card_title_center" id="ajax_res_search_div"> جدول رصيد الاجازات الثانوى   
@if($dataVacations->isEmpty())
    <div class="text-danger card_title_center"><h1>لا توجد بيانات للعرض</h1></div>
@else
    <table id="example2" class="table table-bordered table-hover">
              <thead>
                <tr>
                <th scope="col">كود الموظف</th>
                <th scope="col">السنة والشهر</th>
                <th scope="col">الرصيد المرحل من الشهر السابق</th>
                <th scope="col">رصيد الشهر الحالى</th>
                <th scope="col">اجمالى الرصيد المتاح</th>
                <th scope="col">الرصيد المستهلك</th>
                <th scope="col">صافى الرصيد</th>
                <th scope="col">الاضافة بواسطة</th>
                <th scope="col">التحديث بواسطة</th>
                <th scope="col">الاضافة بتاريخ</th>
                <th scope="col">التحديث بتاريخ</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($dataVacations as $info)
                  <tr>
                    <td> {{ $info->employee_id }}</td>
                    <td> {{ $info->year_and_month }}</td>
                    <td> {{ $info->carryover_from_previous_month }}</td>
                    <td> {{ $info->currentmonth_balance }}</td>
                    <td> {{ $info->total_available_balance }}</td>
                    <td> {{ $info->spent_balance }}</td>
                    <td> {{ $info->net_balance }}</td>
                    <td> {{ $info->addedBy->name }}</td>
                    <td>
                      @if ($info->updated_by>0)
                      {{ $info->updatedBy->name }}
                      @else
                        لا يوجد
                      @endif
                    </td>
                    <td> {{ $info->created_at }}</td>
                    
                    <td> 
                      @if(@isset($info->updated_at) and !@empty($info->updated_at) )
                      {{ $info->updated_at }}
                      @else
                        لا يوجد
                      @endif
                    </td>
                  </tr>  
                @endforeach
              </tbody>
            </table>
@endif

</div>
@endsection
