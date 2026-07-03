@extends('admin.layouts.admin')

@section('title')
{{ __('admin.emp_title') }}
@endsection

@section('start')
    {{ __('admin.hr_management') }}
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
    .doc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 14px; }
    .doc-card {
        border: 2px dashed #e5e7eb; border-radius: 12px;
        padding: 18px 14px; text-align: center;
        transition: all .2s; position: relative;
    }
    .doc-card.has-file { border-style: solid; border-color: #3b82f6; background: #eff6ff; }
    .doc-card .doc-icon { font-size: 2rem; margin-bottom: 8px; color: #9ca3af; }
    .doc-card.has-file .doc-icon { color: #3b82f6; }
    .doc-card .doc-name { font-size: .82rem; font-weight: 600; color: #374151; margin-bottom: 4px; }
    .doc-card .doc-filename { font-size: .73rem; color: #6b7280; margin-bottom: 10px; word-break: break-all; }
    .doc-card .doc-actions { display: flex; gap: 6px; justify-content: center; flex-wrap: wrap; }
    .doc-badge-uploaded {
        position: absolute; top: 8px; right: 8px;
        background: #10b981; color: #fff;
        font-size: .65rem; padding: 2px 7px;
        border-radius: 20px; font-weight: 700;
    }
</style>
@endsection

@section('home')
<a href="{{ route('employees.index') }}">{{ __('admin.emp_title') }}</a>
@endsection

@section('startpage')
{{ __('admin.edit') }}
@endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">{{ __('admin.emp_edit_title') }}</h3>
        </div>
        <div class="card-body">
              <form method="POST" action="{{ route('employees.update',$data['id']) }}" enctype="multipart/form-data" >
                @csrf
                <div class="card-body">
                    <ul class="nav nav-tabs" id="custom-content-below-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="custom-content-below-baisc_data-tab" data-toggle="pill" href="#custom-content-below-baisc_data" role="tab" aria-controls="custom-content-below-baisc_data" aria-selected="true">{{ __('admin.emp_tab_basic') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="custom-content-below-job_data-tab" data-toggle="pill" href="#custom-content-below-job_data" role="tab" aria-controls="custom-content-below-job_data" aria-selected="false">{{ __('admin.emp_tab_job') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="custom-content-below-other_data-tab" data-toggle="pill" href="#custom-content-below-other_data" role="tab" aria-controls="custom-content-below-other_data" aria-selected="false">{{ __('admin.emp_tab_other') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="custom-content-below-Salary_data-tab" data-toggle="pill" href="#custom-content-below-Salary_data" role="tab" aria-controls="custom-content-below-Salary_data" aria-selected="false">{{ __('admin.emp_tab_salary') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="custom-content-below-client_data-tab" data-toggle="pill" href="#custom-content-below-client_data" role="tab" aria-controls="custom-content-below-client_data" aria-selected="false"><i class="fas fa-building mr-1"></i>بيانات العميل</a>
                        </li>
                    </ul>

                    <div class="tab-content" id="custom-content-below-tabContent">
                        <div class="tab-pane fade show active" id="custom-content-below-baisc_data" role="tabpanel" aria-labelledby="custom-content-below-baisc_data-tab">
                            <br>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="employee_id">{{ __('admin.emp_code') }}</label>
                                        <input type="text" class="form-control" name="employee_id" id="employee_id" value="{{ old('employee_id',$data['employee_id']) }}">
                                        @error('employee_id')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="finger_id">{{ __('admin.emp_finger_code') }}</label>
                                        <input type="text" class="form-control" name="finger_id" id="finger_id" value="{{ old('finger_id',$data['finger_id']) }}">
                                        @error('finger_id')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="employee_name_A">{{ __('admin.emp_name_ar') }}</label>
                                        <input type="text" class="form-control" name="employee_name_A" id="employee_name_A" value="{{ old('employee_name_A',$data['employee_name_A']) }}">
                                        @error('employee_name_A')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="employee_name_E">{{ __('admin.emp_name_en') }}</label>
                                        <input type="text" class="form-control" name="employee_name_E" id="employee_name_E" value="{{ old('employee_name_E',$data['employee_name_E']) }}">
                                        @error('employee_name_E')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="employee_address">{{ __('admin.emp_address') }}</label>
                                        <input type="text" class="form-control" name="employee_address" id="employee_address" value="{{ old('employee_address',$data['employee_address']) }}">
                                        @error('employee_address')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="national_id">{{ __('admin.emp_national_id') }}</label>
                                        <input type="text" class="form-control" name="national_id" id="national_id" value="{{ old('national_id',$data['national_id'])}}">
                                        @error('national_id')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="insurance_no">{{ __('admin.emp_insurance_no') }}</label>
                                        <input type="text" class="form-control" name="insurance_no" id="insurance_no" value="{{ old('insurance_no',$data['insurance_no'])}}">
                                        @error('insurance_no')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="emp_mobile">{{ __('admin.emp_mobile') }}</label>
                                        <input type="text" class="form-control" name="emp_mobile" id="emp_mobile" value="{{ old('emp_mobile',$data['emp_mobile']) }}">
                                        @error('emp_mobile')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="emp_home_tel">{{ __('admin.emp_home_phone') }}</label>
                                        <input type="text" class="form-control" name="emp_home_tel" id="emp_home_tel" value="{{ old('emp_home_tel',$data['emp_home_tel']) }}">
                                        @error('emp_home_tel')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="emp_email">Email</label>
                                        <input type="email" class="form-control" name="emp_email" id="emp_email" value="{{ old('emp_email',$data['emp_email'])}}">
                                        @error('emp_email')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="birth_date">{{ __('admin.emp_birth_date') }}</label>
                                        <input type="date" class="form-control" name="birth_date" id="birth_date" value="{{ old('birth_date',$data['birth_date']) }}">
                                        @error('birth_date')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="emp_gender">{{ __('admin.emp_gender') }}</label>
                                        <select class="form-control" name="emp_gender" id="emp_gender">
                                            <option value="">{{ __('admin.emp_gender_choose') }}</option>
                                            <option value="1" @if (old('emp_gender',$data['emp_gender'])==1)selected @endif>{{ __('admin.male') }}</option>
                                            <option value="2" @if (old('emp_gender',$data['emp_gender'])==2)selected @endif>{{ __('admin.female') }}</option>
                                        </select>
                                        @error('emp_gender')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="emp_social_status">{{ __('admin.emp_marital_status') }}</label>
                                        <select class="form-control" name="emp_social_status" id="emp_social_status">
                                            <option value="">{{ __('admin.emp_marital_choose') }}</option>
                                            <option value="1" @if (old('emp_social_status',$data['emp_social_status'])==1)selected @endif>{{ __('admin.emp_single') }}</option>
                                            <option value="2" @if (old('emp_social_status',$data['emp_social_status'])==2)selected @endif>{{ __('admin.emp_married') }}</option>
                                            <option value="3" @if (old('emp_social_status',$data['emp_social_status'])==3)selected @endif>{{ __('admin.emp_married_dependent') }}</option>
                                        </select>
                                        @error('emp_social_status')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="alert alert-info py-2 mt-2 mb-0">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        يمكنك رفع <strong>الصورة الشخصية</strong> والسيرة الذاتية وجميع ملفات التعيين من قسم <strong>ملفات التعيين</strong> أدناه
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="custom-content-below-job_data" role="tabpanel" aria-labelledby="custom-content-below-job_data-tab">
                            <br>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="emp_start_date">{{ __('admin.emp_join_date') }}</label>
                                        <input type="date" class="form-control" name="emp_start_date" id="emp_start_date" value="{{ old('emp_start_date',$data['emp_start_date']) }}">
                                        @error('emp_start_date')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="functional_status">{{ __('admin.emp_status') }}</label>
                                        <select class="form-control" name="functional_status" id="functional_status">
                                            <option value="1" @if (old('functional_status',$data['functional_status'])==1)selected @endif>{{ __('admin.emp_working') }}</option>
                                            <option value="2" @if (old('functional_status',$data['functional_status'])==2)selected @endif>{{ __('admin.emp_not_working') }}</option>
                                        </select>
                                        @error('functional_status')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="insurance_status">{{ __('admin.emp_insurance_status') }}</label>
                                        <select class="form-control" name="insurance_status" id="insurance_status">
                                            <option value="1" @if (old('insurance_status',$data['insurance_status'])==1)selected @endif>{{ __('admin.emp_insured') }}</option>
                                            <option value="2" @if (old('insurance_status',$data['insurance_status'])==2)selected @endif>{{ __('admin.emp_not_insured') }}</option>
                                            <option value="3" @if (old('insurance_status',$data['insurance_status'])==3)selected @endif>{{ __('admin.emp_training') }}</option>
                                            <option value="4" @if (old('insurance_status',$data['insurance_status'])==4)selected @endif>{{ __('admin.emp_service_ended') }}</option>
                                        </select>
                                        @error('insurance_status')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="">
                                        <label for="emp_jobs_id">{{ __('admin.emp_job') }}</label>
                                        <select name="emp_jobs_id" id="emp_jobs_id" class="form-control select2">
                                            <option value="">{{ __('admin.emp_job_choose') }}</option>
                                            @foreach($jobs_categories as $job)
                                                <option value="{{ $job->id }}" {{ old('emp_jobs_id',$data['emp_jobs_id']) == $job->id ? 'selected' : '' }}>
                                                    {{ $job->job_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('emp_jobs_id')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="emp_departments_id">{{ __('admin.emp_dept') }}</label>
                                        <select name="emp_departments_id" id="emp_departments_id" class="form-control select2">
                                            <option value="">{{ __('admin.emp_dept_choose') }}</option>
                                            @foreach($departments as $department)
                                                <option value="{{ $department->id }}" {{ old('emp_departments_id',$data['emp_departments_id']) == $department->id ? 'selected' : '' }}>
                                                    {{ $department->dep_name}}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('emp_departments_id')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="shifts_types_id">{{ __('admin.emp_shift') }}</label>
                                        <select name="shifts_types_id" id="shifts_types_id" class="form-control">
                                            <option value="">{{ __('admin.emp_shift_choose') }}</option>
                                            @foreach($shifts_types as $shifts_type)
                                                <option value="{{ $shifts_type->id }}" {{ old('shifts_types_id',$data['shifts_types_id']) == $shifts_type->id ? 'selected' : '' }}>
                                                    {{ $shifts_type->type}}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('shifts_types_id')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="branches_id">{{ __('admin.emp_branch') }}</label>
                                        <select name="branches_id" id="branches_id" class="form-control select2">
                                            <option value="">{{ __('admin.emp_branch_choose') }}</option>
                                            @foreach($branches as $branche)
                                                <option value="{{ $branche->id }}" {{ old('branches_id',$data['branches_id']) == $branche->id ? 'selected' : '' }}>
                                                    {{ $branche->branch_name}}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('branches_id')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="weekly_off_day">{{ __('admin.emp_weekly_off') }}</label>
                                        <select name="weekly_off_day" id="weekly_off_day" class="form-control">
                                            <option value="">--</option>
                                            <option value="0" {{ old('weekly_off_day', $data['weekly_off_day'] ?? '') === '0' || old('weekly_off_day', $data['weekly_off_day'] ?? '') == 0 && old('weekly_off_day', $data['weekly_off_day'] ?? '') !== '' ? 'selected' : '' }}>{{ __('admin.emp_sunday') }}</option>
                                            <option value="1" {{ old('weekly_off_day', $data['weekly_off_day'] ?? '') == 1 ? 'selected' : '' }}>{{ __('admin.emp_monday') }}</option>
                                            <option value="2" {{ old('weekly_off_day', $data['weekly_off_day'] ?? '') == 2 ? 'selected' : '' }}>{{ __('admin.emp_tuesday') }}</option>
                                            <option value="3" {{ old('weekly_off_day', $data['weekly_off_day'] ?? '') == 3 ? 'selected' : '' }}>{{ __('admin.emp_wednesday') }}</option>
                                            <option value="4" {{ old('weekly_off_day', $data['weekly_off_day'] ?? '') == 4 ? 'selected' : '' }}>{{ __('admin.emp_thursday') }}</option>
                                            <option value="5" {{ old('weekly_off_day', $data['weekly_off_day'] ?? '') == 5 ? 'selected' : '' }}>{{ __('admin.emp_friday') }}</option>
                                            <option value="6" {{ old('weekly_off_day', $data['weekly_off_day'] ?? '') == 6 ? 'selected' : '' }}>{{ __('admin.emp_saturday') }}</option>
                                        </select>
                                        <small class="text-muted">{{ __('admin.emp_weekly_off_hint') }}</small>
                                        @error('weekly_off_day')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="daily_work_hours">{{ __('admin.emp_work_hours') }}</label>
                                        <input type="number" class="form-control" name="daily_work_hours" id="daily_work_hours" value="{{ old('daily_work_hours',$data['daily_work_hours']) }}">
                                        @error('daily_work_hours')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="resignation_status">{{ __('admin.emp_leave_reason_label') }}</label>
                                        <select class="form-control" name="resignation_status" id="resignation_status">
                                            <option value="">{{ __('admin.emp_marital_choose') }}</option>
                                            <option value="1" @if (old('resignation_status',$data['resignation_status'])==1)selected @endif>{{ __('admin.emp_resignation') }}</option>
                                            <option value="2" @if (old('resignation_status',$data['resignation_status'])==2)selected @endif>{{ __('admin.emp_fired') }}</option>
                                            <option value="3" @if (old('resignation_status',$data['resignation_status'])==3)selected @endif>{{ __('admin.emp_left_work') }}</option>
                                            <option value="4" @if (old('resignation_status',$data['resignation_status'])==4)selected @endif>{{ __('admin.emp_retirement_age') }}</option>
                                            <option value="5" @if (old('resignation_status',$data['resignation_status'])==5)selected @endif>{{ __('admin.emp_death') }}</option>
                                        </select>
                                        @error('resignation_status')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="resignation_date">{{ __('admin.emp_leave_date') }}</label>
                                        <input type="date" class="form-control" name="resignation_date" id="resignation_date" value="{{ old('resignation_date',$data['resignation_date']) }}">
                                        @error('resignation_date')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="resignation_cause">{{ __('admin.emp_leave_reason') }}</label>
                                        <input type="text" class="form-control" name="resignation_cause" id="resignation_cause" value="{{ old('resignation_cause',$data['resignation_cause']) }}">
                                        @error('resignation_cause')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="custom-content-below-other_data" role="tabpanel" aria-labelledby="custom-content-below-other_data-tab">
                            <br>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="emp_military_status">{{ __('admin.emp_military') }}</label>
                                        <select class="form-control" name="emp_military_status" id="emp_military_status">
                                            <option value="">{{ __('admin.emp_marital_choose') }}</option>
                                            <option value="1" @if (old('emp_military_status',$data['emp_military_status'])==1)selected @endif>{{ __('admin.emp_military_served') }}</option>
                                            <option value="2" @if (old('emp_military_status',$data['emp_military_status'])==2)selected @endif>{{ __('admin.emp_military_exempt') }}</option>
                                            <option value="3" @if (old('emp_military_status',$data['emp_military_status'])==3)selected @endif>{{ __('admin.emp_military_deferred') }}</option>
                                            <option value="4" @if (old('emp_military_status',$data['emp_military_status'])==4)selected @endif>{{ __('admin.emp_military_temp_exempt') }}</option>
                                            <option value="5" @if (old('emp_military_status',$data['emp_military_status'])==5)selected @endif>{{ __('admin.emp_military_not_required') }}</option>
                                        </select>
                                        @error('emp_military_status')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="emp_qualification">{{ __('admin.emp_education') }}</label>
                                        <input type="text" class="form-control" name="emp_qualification" id="emp_qualification" value="{{ old('emp_qualification',$data['emp_qualification']) }}">
                                        @error('emp_qualification')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="qualification_year">{{ __('admin.emp_edu_year') }}</label>
                                        <input type="text" class="form-control" name="qualification_year" id="qualification_year" value="{{ old('qualification_year',$data['qualification_year']) }}">
                                        @error('qualification_year')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="qualification_grade">{{ __('admin.emp_edu_grade') }}</label>
                                        <select class="form-control" name="qualification_grade" id="qualification_grade">
                                            <option value="">{{ __('admin.emp_marital_choose') }}</option>
                                            <option value="1" @if (old('qualification_grade',$data['qualification_grade'])==1)selected @endif>{{ __('admin.emp_distinction') }}</option>
                                            <option value="2" @if (old('qualification_grade',$data['qualification_grade'])==2)selected @endif>{{ __('admin.emp_very_good') }}</option>
                                            <option value="3" @if (old('qualification_grade',$data['qualification_grade'])==3)selected @endif>{{ __('admin.emp_very_good_high') }}</option>
                                            <option value="4" @if (old('qualification_grade',$data['qualification_grade'])==4)selected @endif>{{ __('admin.emp_good') }}</option>
                                            <option value="5" @if (old('qualification_grade',$data['qualification_grade'])==5)selected @endif>{{ __('admin.emp_accepted') }}</option>
                                        </select>
                                        @error('qualification_grade')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="custom-content-below-Salary_data" role="tabpanel" aria-labelledby="custom-content-below-Salary_data-tab">
                            <br>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="emp_sal">{{ __('admin.emp_basic_salary') }}</label>
                                        <input type="number" class="form-control" name="emp_sal" id="emp_sal" value="{{ old('emp_sal',$data['emp_sal']) }}">
                                        @error('emp_sal')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="emp_sal_insurance">{{ __('admin.emp_insurance_salary') }}</label>
                                        <input type="number" class="form-control" name="emp_sal_insurance" id="emp_sal_insurance" value="{{ old('emp_sal_insurance',$data['emp_sal_insurance']) }}">
                                        @error('emp_sal_insurance')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="emp_fixed_allowances">{{ __('admin.emp_fixed_allowance') }}</label>
                                        <input type="number" class="form-control" name="emp_fixed_allowances" id="emp_fixed_allowances" value="{{ old('emp_fixed_allowances',$data['emp_fixed_allowances']) }}">
                                        @error('emp_fixed_allowances')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                              <div class="col-12">
                                <div class="card bg-light">
                                  <div class="card-header py-2">
                                    <strong><i class="fas fa-sliders-h ml-1"></i>{{ __('admin.emp_overtime_settings') }}</strong>
                                    <small class="text-muted mr-2">{{ __('admin.emp_marital_choose') }}</small>
                                  </div>
                                  <div class="card-body">
                                    <div class="row">
                                      <div class="col-md-4 form-group">
                                        <label>{{ __('admin.emp_overtime_multiplier') }}</label>
                                        <div class="input-group">
                                          <input type="number" class="form-control" name="custom_overtime_multiplier"
                                            step="0.01" min="0" max="10" placeholder="e.g. 2"
                                            value="{{ old('custom_overtime_multiplier', $data['custom_overtime_multiplier'] ?? '') }}">
                                          <div class="input-group-append"><span class="input-group-text">×</span></div>
                                        </div>
                                      </div>
                                      <div class="col-md-4 form-group">
                                        <label>{{ __('admin.emp_calc_overtime') }}</label>
                                        <select class="form-control" name="overtime_enabled">
                                          <option value="1" {{ ($data['overtime_enabled'] ?? 1) == 1 ? 'selected' : '' }}>{{ __('admin.emp_ot_yes') }}</option>
                                          <option value="0" {{ ($data['overtime_enabled'] ?? 1) == 0 ? 'selected' : '' }}>{{ __('admin.emp_ot_no') }}</option>
                                        </select>
                                      </div>
                                      <div class="col-md-4 form-group">
                                        <label>{{ __('admin.emp_calc_delay') }}</label>
                                        <select class="form-control" name="late_deduction_enabled">
                                          <option value="1" {{ ($data['late_deduction_enabled'] ?? 1) == 1 ? 'selected' : '' }}>{{ __('admin.emp_delay_yes') }}</option>
                                          <option value="0" {{ ($data['late_deduction_enabled'] ?? 1) == 0 ? 'selected' : '' }}>{{ __('admin.emp_delay_no') }}</option>
                                        </select>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="mtivation">{{ __('admin.emp_incentive') }}</label>
                                        <input type="number" class="form-control" name="mtivation" id="mtivation" value="{{ old('mtivation',$data['mtivation']) }}">
                                        @error('mtivation')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="medical_insurance">{{ __('admin.emp_health_insurance') }}</label>
                                        <input type="number" class="form-control" name="medical_insurance" id="medical_insurance" value="{{ old('medical_insurance',$data['medical_insurance'])}}">
                                        @error('medical_insurance')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="sal_cash_visa">{{ __('admin.emp_payment_method') }}</label>
                                        <select class="form-control" name="sal_cash_visa" id="sal_cash_visa">
                                            <option value="">{{ __('admin.emp_marital_choose') }}</option>
                                            <option value="1" @if (old('sal_cash_visa',$data['sal_cash_visa'])==1)selected @endif>{{ __('admin.emp_cash') }}</option>
                                            <option value="2" @if (old('sal_cash_visa',$data['sal_cash_visa'])==2)selected @endif>{{ __('admin.emp_visa') }}</option>
                                        </select>
                                        @error('sal_cash_visa')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="bank_name">{{ __('admin.emp_bank_name') }}</label>
                                        <input type="text" class="form-control" name="bank_name" id="bank_name" value="{{ old('bank_name',$data['bank_name']) }}">
                                        @error('bank_name')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="bank_account">{{ __('admin.emp_bank_account') }}</label>
                                        <input type="text" class="form-control" name="bank_account" id="bank_account" value="{{ old('bank_account',$data['bank_account']) }}">
                                        @error('bank_account')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="bank_ID">bank ID</label>
                                        <input type="text" class="form-control" name="bank_ID" id="bank_ID" value="{{ old('bank_ID',$data['bank_ID'])}}">
                                        @error('bank_ID')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="bank_branch">bank branch</label>
                                        <input type="text" class="form-control" name="bank_branch" id="bank_branch" value="{{ old('bank_branch',$data['bank_branch']) }}">
                                        @error('bank_branch')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── TAB: Client Data ── --}}
                        <div class="tab-pane fade" id="custom-content-below-client_data" role="tabpanel" aria-labelledby="custom-content-below-client_data-tab">
                            <br>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="">
                                        <label>العميل</label>
                                        <select class="form-control" name="client_id" id="client_id">
                                            <option value="">— بدون عميل —</option>
                                            @foreach($clients as $client)
                                                <option value="{{ $client->id }}" {{ old('client_id', $data['client_id']) == $client->id ? 'selected' : '' }}>{{ $client->client_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label>كود العميل (Custom ID / HRID)</label>
                                        <input type="text" class="form-control" name="hrid" value="{{ old('hrid', $data['hrid']) }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label>جهة الاتصال الطارئة (Reference Number)</label>
                                        <input type="text" class="form-control" name="reference_mobile" value="{{ old('reference_mobile', $data['reference_mobile']) }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <div class="">
                                        <label>صلة القرابة (Relative)</label>
                                        <input type="text" class="form-control" name="relative_relation" value="{{ old('relative_relation', $data['relative_relation']) }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label>حالة أوراق التعيين (Hiring Documents)</label>
                                        <input type="text" class="form-control" name="hiring_documents_status" value="{{ old('hiring_documents_status', $data['hiring_documents_status']) }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label>تاريخ بداية التأمين (Start Date Of Social)</label>
                                        <input type="date" class="form-control" name="insurance_start_date" value="{{ old('insurance_start_date', $data['insurance_start_date']) }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <div class="">
                                        <label>تاريخ انتهاء التأمين (End Date Of Social)</label>
                                        <input type="date" class="form-control" name="insurance_end_date" value="{{ old('insurance_end_date', $data['insurance_end_date']) }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label>ملاحظات نموذج 1 (Form 1 Comments)</label>
                                        <textarea class="form-control" name="form1_notes" rows="3">{{ old('form1_notes', $data['form1_notes']) }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label>ملاحظات نموذج 6 (Form 6 Comments)</label>
                                        <textarea class="form-control" name="form6_notes" rows="3">{{ old('form6_notes', $data['form6_notes']) }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="">
                                        <label>ملاحظات (Comments)</label>
                                        <textarea class="form-control" name="client_notes" rows="3">{{ old('client_notes', $data['client_notes']) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary btn-lg col-2">{{ __('admin.update') }}</button>
                        <a class="btn btn-warning btn-lg col-2" href="{{ route('employees.index') }}">{{ __('admin.cancel') }}</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Documents Section ── --}}
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-folder-open mr-2"></i>ملفات التعيين</h5>
        </div>
        <div class="card-body">
            <div class="doc-grid">
                @foreach($docTypes as $type => $info)
                    @php $doc = $documents->get($type); @endphp
                    <div class="doc-card {{ $doc ? 'has-file' : '' }}">
                        @if($doc)<span class="doc-badge-uploaded">✓ مرفوع</span>@endif
                        <div class="doc-icon"><i class="fas {{ $info['icon'] }}"></i></div>
                        <div class="doc-name">{{ $info['ar'] }}</div>
                        @if($doc)
                            <div class="doc-filename">{{ Str::limit($doc->doc_original_name, 30) }}</div>
                            <div class="doc-actions">
                                <a href="{{ route('employees.document.download', [$data['id'], $doc->id]) }}"
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-download"></i> تنزيل
                                </a>
                                <a href="{{ route('employees.document.delete', [$data['id'], $doc->id]) }}"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('حذف هذا الملف؟')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        @else
                            <div class="doc-filename" style="color:#9ca3af">لم يُرفع بعد</div>
                        @endif
                        <form action="{{ route('employees.document.upload', $data['id']) }}" method="POST"
                              enctype="multipart/form-data" class="mt-2">
                            @csrf
                            <input type="hidden" name="doc_type" value="{{ $type }}">
                            <label class="btn btn-sm {{ $doc ? 'btn-outline-secondary' : 'btn-outline-primary' }} w-100">
                                <i class="fas fa-upload"></i> {{ $doc ? 'استبدال' : 'رفع ملف' }}
                                <input type="file" name="doc_file" class="d-none"
                                       accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                       onchange="this.closest('form').submit()">
                            </label>
                        </form>
                    </div>
                @endforeach
            </div>
            <div class="mt-3 p-3 rounded" style="background:#f0f9ff;border:1px solid #bae6fd;font-size:.82rem;color:#0369a1">
                <i class="fas fa-info-circle mr-1"></i>
                الملفات المقبولة: PDF، صور (JPG/PNG)، مستندات Word — الحجم الأقصى 10MB لكل ملف
            </div>
        </div>
    </div>

</div>
@endsection

@section("script")
    <script>
        $(document).ready(function() {

            // استخراج تاريخ الميلاد تلقائياً من الرقم القومي المصري (14 رقم)
            $('#national_id').on('input', function() {
                var nid = $(this).val().trim();
                if (!/^[123]\d{13}$/.test(nid)) return;

                var centuryDigit = nid.charAt(0);
                var centuryPrefix = centuryDigit === '3' ? '20' : (centuryDigit === '2' ? '19' : '18');
                var yy = nid.substr(1, 2);
                var mm = nid.substr(3, 2);
                var dd = nid.substr(5, 2);

                var month = parseInt(mm, 10);
                var day   = parseInt(dd, 10);
                if (month < 1 || month > 12 || day < 1 || day > 31) return;

                $('#birth_date').val(centuryPrefix + yy + '-' + mm + '-' + dd);
            });

            const arToEnNames = {
                'محمد':'Mohamed','محمود':'Mahmoud','أحمد':'Ahmed','علي':'Ali','حسن':'Hassan',
                'حسين':'Hussein','عمر':'Omar','عمرو':'Amr','إبراهيم':'Ibrahim','إسماعيل':'Ismail',
                'يوسف':'Youssef','يحيى':'Yahya','ياسر':'Yasser','ياسين':'Yassin',
                'عبدالله':'Abdullah','عبدالرحمن':'Abdelrahman','عبدالرحيم':'Abdelrahim',
                'عبدالعزيز':'Abdelaziz','عبدالحميد':'Abdelhamid','عبدالفتاح':'Abdelfattah',
                'عبدالمنعم':'Abdelmoneam','عبدالسلام':'Abdelsalam',
                'مصطفى':'Mustafa','خالد':'Khaled','طارق':'Tarek','سامي':'Sami',
                'وليد':'Walid','رامي':'Rami','هاني':'Hany','كريم':'Karim','عماد':'Emad',
                'أسامة':'Osama','شريف':'Sherif','مروان':'Marwan','جمال':'Gamal',
                'فريد':'Farid','بسام':'Bassam','ناصر':'Nasser','سعد':'Saad',
                'فتحي':'Fathy','صلاح':'Salah','ماهر':'Maher','نادر':'Nader',
                'عادل':'Adel','سيد':'Sayed','منصور':'Mansour','فيصل':'Faisal',
                'زياد':'Ziad','باسم':'Bassem','أيمن':'Ayman','هشام':'Hisham',
                'مدحت':'Medhat','نبيل':'Nabil','عصام':'Essam','داود':'Dawood',
                'سليمان':'Soliman','جابر':'Gaber','رضا':'Reda','صابر':'Saber',
                'فاروق':'Farouk','عزت':'Ezzat','أنور':'Anwar','منير':'Monir',
                'تامر':'Tamer','بهاء':'Bahaa','إياد':'Eyad','ثروت':'Tharwat',
                'حامد':'Hamed','حمزة':'Hamza','زكريا':'Zakaria','رفعت':'Refaat',
                'ربيع':'Rabie','سعيد':'Saeed','سلامة':'Salama','عطية':'Attia',
                'قدري':'Qadry','مجدي':'Magdy','منتصر':'Montaser','نصر':'Nasr',
                'هادي':'Hady','وجدي':'Wagdy','يسري':'Yosry','أمين':'Amin',
                'أنس':'Anas','بدر':'Badr','حازم':'Hazem','حاتم':'Hatem',
                'خيري':'Khairy','دياب':'Diab','لطفي':'Lotfy','توفيق':'Tawfik',
                'حمدي':'Hamdy','صبري':'Sobhy','مختار':'Mokhtar','رشاد':'Rashad',
                'رشيد':'Rashid','سمير':'Samir','شوقي':'Shawky','علاء':'Alaa',
                'فهمي':'Fahmy','قاسم':'Kassem','كمال':'Kamal','ممدوح':'Mamdouh',
                'نجيب':'Naguib','هيثم':'Haytham','وائل':'Wael','حلمي':'Helmy',
                'خليل':'Khalil','درويش':'Darwish','زهير':'Zohair','سالم':'Salem',
                'شحاتة':'Shahata','طه':'Taha','عبير':'Abeer','فايز':'Fayez',
                'ماجد':'Maged','نصير':'Nassir','هدى':'Hoda','وسيم':'Wassim',
                'يونس':'Younis','أبوبكر':'Abubakr','بكر':'Bakr','جاد':'Gad',
                'خضر':'Khedr','راغب':'Ragheb','زكي':'Zaki','صفوت':'Safwat',
                'ضياء':'Diaa','طلعت':'Talaat','عفيفي':'Afify','فخري':'Fakhry',
                'كرم':'Karam','لبيب':'Labib','مأمون':'Mamoon','نزار':'Nizar',
                'هاشم':'Hashem','وادي':'Wady',
                'فاطمة':'Fatma','عائشة':'Aisha','مريم':'Mariam','سارة':'Sara',
                'نور':'Nour','هبة':'Heba','رنا':'Rana','آية':'Aya','دينا':'Dina',
                'سمر':'Samar','إيمان':'Eman','منى':'Mona','نهاد':'Nehad',
                'هالة':'Hala','ريم':'Reem','لبنى':'Lobna','إنجي':'Engy',
                'شيماء':'Shimaa','وفاء':'Wafaa','ولاء':'Walaa','سلمى':'Salma',
                'غادة':'Ghada','لمياء':'Lamia','نيفين':'Neveen','ياسمين':'Yasmine',
                'أسماء':'Asmaa','بسمة':'Basma','حنان':'Hanan','خديجة':'Khadiga',
                'رشا':'Rasha','زينب':'Zainab','سناء':'Sanaa','صفاء':'Safaa',
                'عفاف':'Afaf','مي':'Mai','نادية':'Nadia','أميرة':'Amira',
                'جيهان':'Gehan','رقية':'Rokaya','شادية':'Shadia','عزة':'Azza',
                'فريدة':'Farida','كريمة':'Karima','لطيفة':'Latifa','نادين':'Nadine',
                'هناء':'Hanaa','إلهام':'Elham','أمل':'Amal','تهاني':'Tahany',
                'حياة':'Hayat','درية':'Doria','عبلة':'Abla','نجلاء':'Naglaa',
                'هويدا':'Howayda','وسام':'Wesam','ميرنا':'Mirna','نيرة':'Nayra',
                'حنين':'Haneen','رهف':'Rahaf','سهير':'Sohair','شروق':'Shorouk',
                'صباح':'Sabah','ضحى':'Doha','فايزة':'Fayza','مها':'Maha',
                'نوران':'Nouran','وجدان':'Wagdan','يمنى':'Yomna',
                'رمضان':'Ramadan','غانم':'Ghanem','نجم':'Nagm','هيكل':'Heikal',
                'مرسي':'Morsy','عوض':'Awad','زيدان':'Zedan','بدوي':'Badawy',
                'حجازي':'Hegazy','شرف':'Sharaf','متولي':'Metwaly','دسوقي':'Desouky',
                'رفاعي':'Refaay','هلال':'Helal','وهبة':'Wahba','ملاك':'Malak',
                'نعمة':'Neama','ديب':'Deeb','قطب':'Qotb','منيم':'Moneim',
                'منجد':'Mongad','حلبي':'Halaby','زناتي':'Zenaty','شبراوي':'Shebrawy',
                'غزالي':'Ghazaly','فقي':'Fiky','قرشي':'Qorashi','كيلاني':'Kilany',
            };

            $.get('{{ route("employees.dictionary.get") }}', function(data) {
                $.each(data, function(i, item) {
                    arToEnNames[item.ar_name] = item.en_name;
                });
            });

            function normalizeAr(w) {
                return w.replace(/[ً-ٰٟ]/g, '').replace(/[أإآ]/g, 'ا').replace(/ة$/, 'ة');
            }

            function arToEn(text) {
                return text.trim().split(/\s+/).map(word => {
                    const clean = normalizeAr(word);
                    if (arToEnNames[word])  return arToEnNames[word];
                    if (arToEnNames[clean]) return arToEnNames[clean];
                    for (const [ar, en] of Object.entries(arToEnNames)) {
                        if (normalizeAr(ar) === clean) return en;
                    }
                    return '[' + word + ']';
                }).join(' ');
            }

            function isArabic(t) { return /[؀-ۿ]/.test(t); }

            let lastUnknownWords = [];

            $('#employee_name_A').on('input', function() {
                const val = $(this).val();
                if (!isArabic(val)) return;

                const result = arToEn(val);
                $('#employee_name_E').val(result);

                lastUnknownWords = [];
                const arWords = val.trim().split(/\s+/);
                const enWords = result.trim().split(/\s+/);
                arWords.forEach((w, i) => {
                    if (enWords[i] && enWords[i].startsWith('[')) {
                        lastUnknownWords.push(w);
                    }
                });

                renderDictionaryPanel();
            });

            function renderDictionaryPanel() {
                $('#dict-panel').remove();
                if (lastUnknownWords.length === 0) return;

                let rows = '';
                lastUnknownWords.forEach(function(arWord) {
                    rows += '<div class="input-group input-group-sm mb-1">' +
                        '<div class="input-group-prepend">' +
                        '<span class="input-group-text" style="min-width:110px;font-weight:bold;">' + arWord + '</span>' +
                        '</div>' +
                        '<span class="input-group-text">=</span>' +
                        '<input type="text" class="form-control dict-en-input" dir="ltr" ' +
                        'placeholder="English translation" data-ar="' + arWord + '">' +
                        '</div>';
                });

                const panel = '<div id="dict-panel" class="mt-2 p-2 border border-warning rounded bg-light">' +
                    '<small class="text-warning font-weight-bold d-block mb-1">' +
                    '<i class="fas fa-exclamation-triangle ml-1"></i>' +
                    'Words not in dictionary — add translation to save for next time' +
                    '</small>' +
                    rows +
                    '<button type="button" id="btn-save-dict" class="btn btn-warning btn-sm mt-1">' +
                    '<i class="fas fa-save ml-1"></i> Save to Dictionary' +
                    '</button>' +
                    '</div>';

                $('#employee_name_E').closest('.col-md-4').append(panel);

                $('#btn-save-dict').on('click', function() {
                    const entries = [];
                    $('.dict-en-input').each(function() {
                        const ar = $(this).data('ar');
                        const en = $(this).val().trim();
                        if (en) entries.push({ ar: ar, en: en });
                    });
                    if (entries.length === 0) {
                        alert('Please enter English translation first');
                        return;
                    }
                    $.ajax({
                        url: '{{ route("employees.dictionary.save") }}',
                        method: 'POST',
                        data: { _token: '{{ csrf_token() }}', entries: entries },
                        success: function() {
                            entries.forEach(function(e) { arToEnNames[e.ar] = e.en; });
                            const arVal = $('#employee_name_A').val();
                            if (arVal) $('#employee_name_E').val(arToEn(arVal));
                            lastUnknownWords = [];
                            $('#dict-panel').remove();
                            $('<div class="alert alert-success alert-dismissible py-1 mt-1" id="dict-saved-msg">' +
                              '<strong>Saved!</strong> Words added to dictionary.' +
                              '<button type="button" class="close py-1" data-dismiss="alert">&times;</button>' +
                              '</div>').insertAfter('#employee_name_E');
                            setTimeout(function(){ $('#dict-saved-msg').fadeOut(500, function(){ $(this).remove(); }); }, 3000);
                        },
                        error: function() { alert('An error occurred, please try again'); }
                    });
                });
            }
        });
    </script>
@endsection
