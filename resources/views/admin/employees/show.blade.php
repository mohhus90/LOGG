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
</style>
@endsection

@section('home')
<a href="{{ route('employees.index') }}">{{ __('admin.emp_view_data') }}</a>
@endsection

@section('startpage')
{{ __('admin.view') }}
@endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">{{ __('admin.emp_view_data') }}</h3>
        </div>
        <div class="card-body">
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
                </ul>

                <div class="tab-content" id="custom-content-below-tabContent">
                    <div class="tab-pane fade show active" id="custom-content-below-baisc_data" role="tabpanel" aria-labelledby="custom-content-below-baisc_data-tab">
                        <br>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="">
                                    <label for="employee_id">{{ __('admin.emp_code') }}</label>
                                    <input disabled type="text" class="form-control" name="employee_id" id="employee_id" value="{{ old('employee_id',$data['employee_id']) }}">
                                    @error('employee_id')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="">
                                    <label for="finger_id">{{ __('admin.emp_finger_code') }}</label>
                                    <input disabled type="text" class="form-control" name="finger_id" id="finger_id" value="{{ old('finger_id',$data['finger_id']) }}">
                                    @error('finger_id')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="">
                                    <label for="employee_name_A">{{ __('admin.emp_name_ar') }}</label>
                                    <input disabled type="text" class="form-control" name="employee_name_A" id="employee_name_A" value="{{ old('employee_name_A',$data['employee_name_A']) }}">
                                    @error('employee_name_A')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="">
                                    <label for="employee_name_E">{{ __('admin.emp_name_en') }}</label>
                                    <input disabled type="text" class="form-control" name="employee_name_E" id="employee_name_E" value="{{ old('employee_name_E',$data['employee_name_E']) }}">
                                    @error('employee_name_E')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="">
                                    <label for="employee_address">{{ __('admin.emp_address') }}</label>
                                    <input disabled type="text" class="form-control" name="employee_address" id="employee_address" value="{{ old('employee_address',$data['employee_address']) }}">
                                    @error('employee_address')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="">
                                    <label for="national_id">{{ __('admin.emp_national_id') }}</label>
                                    <input disabled type="text" class="form-control" name="national_id" id="national_id" value="{{ old('national_id',$data['national_id']) }}">
                                    @error('national_id')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="">
                                    <label for="insurance_no">{{ __('admin.emp_insurance_no') }}</label>
                                    <input disabled type="text" class="form-control" name="insurance_no" id="insurance_no" value="{{ old('insurance_no',$data['insurance_no']) }}">
                                    @error('insurance_no')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="">
                                    <label for="emp_mobile">{{ __('admin.emp_mobile') }}</label>
                                    <input disabled type="text" class="form-control" name="emp_mobile" id="emp_mobile" value="{{ old('emp_mobile',$data['emp_mobile']) }}">
                                    @error('emp_mobile')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="">
                                    <label for="emp_home_tel">{{ __('admin.emp_home_phone') }}</label>
                                    <input disabled type="text" class="form-control" name="emp_home_tel" id="emp_home_tel" value="{{ old('emp_home_tel',$data['emp_home_tel']) }}">
                                    @error('emp_home_tel')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="">
                                    <label for="emp_email">Email</label>
                                    <input disabled type="email" class="form-control" name="emp_email" id="emp_email" value="{{ old('emp_email',$data['emp_email']) }}">
                                    @error('emp_email')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="">
                                    <label for="birth_date">{{ __('admin.emp_birth_date') }}</label>
                                    <input disabled type="date" class="form-control" name="birth_date" id="birth_date" value="{{ old('birth_date',$data['birth_date']) }}">
                                    @error('birth_date')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="">
                                    <label for="emp_gender">{{ __('admin.emp_gender') }}</label>
                                    <select disabled class="form-control" name="emp_gender" id="emp_gender">
                                        <option value="">{{ __('admin.emp_gender_choose') }}</option>
                                        <option value="1" @if(old('emp_gender',$data['emp_gender'])==1)selected @endif>{{ __('admin.male') }}</option>
                                        <option value="2" @if(old('emp_gender',$data['emp_gender'])==2)selected @endif>{{ __('admin.female') }}</option>
                                    </select>
                                    @error('emp_gender')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="">
                                    <label for="emp_social_status">{{ __('admin.emp_marital_status') }}</label>
                                    <select disabled class="form-control" name="emp_social_status" id="emp_social_status">
                                        <option value="">{{ __('admin.emp_marital_choose') }}</option>
                                        <option value="1" @if(old('emp_social_status',$data['emp_social_status'])==1)selected @endif>{{ __('admin.emp_single') }}</option>
                                        <option value="2" @if(old('emp_social_status',$data['emp_social_status'])==2)selected @endif>{{ __('admin.emp_married') }}</option>
                                        <option value="3" @if(old('emp_social_status',$data['emp_social_status'])==3)selected @endif>{{ __('admin.emp_married_dependent') }}</option>
                                    </select>
                                    @error('emp_social_status')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="">
                                    <label for="emp_photo">{{ __('admin.emp_photo') }}</label>
                                    @if(!@empty($data['emp_photo']))
                                        <img src="{{ asset('assets/admin/uploads/' . $data['emp_photo']) }}" style="width: 80px; height: 80px;" class="rounded-circle" alt="{{ __('admin.emp_photo') }}">
                                    @else
                                        @if(($data['emp_gender'])==2)
                                        <img src="{{ asset('assets/admin/uploads/woman.png') }}" style="width: 80px; height: 80px;" class="rounded-circle" alt="{{ __('admin.emp_photo') }}">
                                        @else
                                        <img src="{{ asset('assets/admin/uploads/man.png') }}" style="width: 80px; height: 80px;" class="rounded-circle" alt="{{ __('admin.emp_photo') }}">
                                        @endif
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="">
                                    <label for="emp_ؤر">{{ __('admin.emp_cv') }}</label>
                                    <input disabled type="file" class="form-control" name="emp_ؤر" id="emp_ؤر" value="{{ old('emp_ؤر',$data['emp_ؤر']) }}">
                                    @error('emp_ؤر')<div class="text-danger">{{ $message }}</div>@enderror
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
                                    <input disabled type="date" class="form-control" name="emp_start_date" id="emp_start_date" value="{{ old('emp_start_date',$data['emp_start_date']) }}">
                                    @error('emp_start_date')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="">
                                    <label for="functional_status">{{ __('admin.emp_status') }}</label>
                                    <select disabled class="form-control" name="functional_status" id="functional_status">
                                        <option value="1" @if(old('functional_status',$data['functional_status'])==1)selected @endif>{{ __('admin.emp_working') }}</option>
                                        <option value="2" @if(old('functional_status',$data['functional_status'])==2)selected @endif>{{ __('admin.emp_not_working') }}</option>
                                    </select>
                                    @error('functional_status')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="">
                                    <label for="insurance_status">{{ __('admin.emp_insurance_status') }}</label>
                                    <select disabled class="form-control" name="insurance_status" id="insurance_status">
                                        <option value="1" @if(old('insurance_status',$data['insurance_status'])==1)selected @endif>{{ __('admin.emp_insured') }}</option>
                                        <option value="2" @if(old('insurance_status',$data['insurance_status'])==2)selected @endif>{{ __('admin.emp_not_insured') }}</option>
                                        <option value="3" @if(old('insurance_status',$data['insurance_status'])==3)selected @endif>{{ __('admin.emp_training') }}</option>
                                        <option value="4" @if(old('insurance_status',$data['insurance_status'])==4)selected @endif>{{ __('admin.emp_service_ended') }}</option>
                                    </select>
                                    @error('insurance_status')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="">
                                    <label for="emp_jobs_id">{{ __('admin.emp_job') }}</label>
                                    <select disabled name="emp_jobs_id" id="emp_jobs_id" class="form-control">
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
                                    <select disabled name="emp_departments_id" id="emp_departments_id" class="form-control">
                                        <option value="">{{ __('admin.emp_dept_choose') }}</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" {{ old('emp_departments_id',$data['emp_departments_id']) == $department->id ? 'selected' : '' }}>
                                                {{ $department->dep_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('emp_departments_id')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="">
                                    <label for="shifts_types_id">{{ __('admin.emp_shift') }}</label>
                                    <select disabled name="shifts_types_id" id="shifts_types_id" class="form-control">
                                        <option value="">{{ __('admin.emp_shift_choose') }}</option>
                                        @foreach($shifts_types as $shifts_type)
                                            <option value="{{ $shifts_type->id }}" {{ old('shifts_types_id',$data['shifts_types_id']) == $shifts_type->id ? 'selected' : '' }}>
                                                {{ $shifts_type->type }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('shifts_types_id')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="">
                                    <label for="branches_id">{{ __('admin.emp_branch') }}</label>
                                    <select disabled name="branches_id" id="branches_id" class="form-control">
                                        <option value="">{{ __('admin.emp_branch_choose') }}</option>
                                        @foreach($branches as $branche)
                                            <option value="{{ $branche->id }}" {{ old('branches_id',$data['branches_id']) == $branche->id ? 'selected' : '' }}>
                                                {{ $branche->branch_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('branches_id')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="">
                                    <label for="daily_work_hours">{{ __('admin.emp_work_hours') }}</label>
                                    <input disabled type="number" class="form-control" name="daily_work_hours" id="daily_work_hours" value="{{ old('daily_work_hours',$data['daily_work_hours']) }}">
                                    @error('daily_work_hours')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="">
                                    <label for="resignation_status">{{ __('admin.emp_leave_reason_label') }}</label>
                                    <select disabled class="form-control" name="resignation_status" id="resignation_status">
                                        <option value="">{{ __('admin.emp_marital_choose') }}</option>
                                        <option value="1" @if(old('resignation_status',$data['resignation_status'])==1)selected @endif>{{ __('admin.emp_resignation') }}</option>
                                        <option value="2" @if(old('resignation_status',$data['resignation_status'])==2)selected @endif>{{ __('admin.emp_fired') }}</option>
                                        <option value="3" @if(old('resignation_status',$data['resignation_status'])==3)selected @endif>{{ __('admin.emp_left_work') }}</option>
                                        <option value="4" @if(old('resignation_status',$data['resignation_status'])==4)selected @endif>{{ __('admin.emp_retirement_age') }}</option>
                                        <option value="5" @if(old('resignation_status',$data['resignation_status'])==5)selected @endif>{{ __('admin.emp_death') }}</option>
                                    </select>
                                    @error('resignation_status')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="">
                                    <label for="resignation_date">{{ __('admin.emp_leave_date') }}</label>
                                    <input disabled type="date" class="form-control" name="resignation_date" id="resignation_date" value="{{ old('resignation_date',$data['resignation_date']) }}">
                                    @error('resignation_date')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="">
                                    <label for="resignation_cause">{{ __('admin.emp_leave_reason') }}</label>
                                    <input disabled type="text" class="form-control" name="resignation_cause" id="resignation_cause" value="{{ old('resignation_cause',$data['resignation_cause']) }}">
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
                                    <select disabled class="form-control" name="emp_military_status" id="emp_military_status">
                                        <option value="">{{ __('admin.emp_marital_choose') }}</option>
                                        <option value="1" @if(old('emp_military_status',$data['emp_military_status'])==1)selected @endif>{{ __('admin.emp_military_served') }}</option>
                                        <option value="2" @if(old('emp_military_status',$data['emp_military_status'])==2)selected @endif>{{ __('admin.emp_military_exempt') }}</option>
                                        <option value="3" @if(old('emp_military_status',$data['emp_military_status'])==3)selected @endif>{{ __('admin.emp_military_deferred') }}</option>
                                    </select>
                                    @error('emp_military_status')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="">
                                    <label for="emp_qualification">{{ __('admin.emp_education') }}</label>
                                    <input disabled type="text" class="form-control" name="emp_qualification" id="emp_qualification" value="{{ old('emp_qualification',$data['emp_qualification']) }}">
                                    @error('emp_qualification')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="">
                                    <label for="qualification_year">{{ __('admin.emp_edu_year') }}</label>
                                    <input disabled type="text" class="form-control" name="qualification_year" id="qualification_year" value="{{ old('qualification_year',$data['qualification_year']) }}">
                                    @error('qualification_year')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="">
                                    <label for="qualification_grade">{{ __('admin.emp_edu_grade') }}</label>
                                    <select disabled class="form-control" name="qualification_grade" id="qualification_grade">
                                        <option value="">{{ __('admin.emp_marital_choose') }}</option>
                                        <option value="1" @if(old('qualification_grade',$data['qualification_grade'])==1)selected @endif>{{ __('admin.emp_distinction') }}</option>
                                        <option value="2" @if(old('qualification_grade',$data['qualification_grade'])==2)selected @endif>{{ __('admin.emp_very_good') }}</option>
                                        <option value="3" @if(old('qualification_grade',$data['qualification_grade'])==3)selected @endif>{{ __('admin.emp_very_good_high') }}</option>
                                        <option value="4" @if(old('qualification_grade',$data['qualification_grade'])==4)selected @endif>{{ __('admin.emp_good') }}</option>
                                        <option value="5" @if(old('qualification_grade',$data['qualification_grade'])==5)selected @endif>{{ __('admin.emp_accepted') }}</option>
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
                                    <input disabled type="number" class="form-control" name="emp_sal" id="emp_sal" value="{{ old('emp_sal',$data['emp_sal']) }}">
                                    @error('emp_sal')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="">
                                    <label for="emp_sal_insurance">{{ __('admin.emp_insurance_salary') }}</label>
                                    <input disabled type="number" class="form-control" name="emp_sal_insurance" id="emp_sal_insurance" value="{{ old('emp_sal_insurance',$data['emp_sal_insurance']) }}">
                                    @error('emp_sal_insurance')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="">
                                    <label for="emp_fixed_allowances">{{ __('admin.emp_fixed_allowance') }}</label>
                                    <input disabled type="number" class="form-control" name="emp_fixed_allowances" id="emp_fixed_allowances" value="{{ old('emp_fixed_allowances',$data['emp_fixed_allowances']) }}">
                                    @error('emp_fixed_allowances')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="">
                                    <label for="mtivation">{{ __('admin.emp_incentive') }}</label>
                                    <input disabled type="number" class="form-control" name="mtivation" id="mtivation" value="{{ old('mtivation',$data['mtivation']) }}">
                                    @error('mtivation')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="">
                                    <label for="medical_insurance">{{ __('admin.emp_health_insurance') }}</label>
                                    <input disabled type="number" class="form-control" name="medical_insurance" id="medical_insurance" value="{{ old('medical_insurance',$data['medical_insurance']) }}">
                                    @error('medical_insurance')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="">
                                    <label for="sal_cash_visa">{{ __('admin.emp_payment_method') }}</label>
                                    <select disabled class="form-control" name="sal_cash_visa" id="sal_cash_visa">
                                        <option value="">{{ __('admin.emp_marital_choose') }}</option>
                                        <option value="1" @if(old('sal_cash_visa',$data['sal_cash_visa'])==1)selected @endif>{{ __('admin.emp_cash') }}</option>
                                        <option value="2" @if(old('sal_cash_visa',$data['sal_cash_visa'])==2)selected @endif>{{ __('admin.emp_visa') }}</option>
                                    </select>
                                    @error('sal_cash_visa')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="">
                                    <label for="bank_name">{{ __('admin.emp_bank_name') }}</label>
                                    <input disabled type="text" class="form-control" name="bank_name" id="bank_name" value="{{ old('bank_name',$data['bank_name']) }}">
                                    @error('bank_name')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="">
                                    <label for="bank_account">{{ __('admin.emp_bank_account') }}</label>
                                    <input disabled type="text" class="form-control" name="bank_account" id="bank_account" value="{{ old('bank_account',$data['bank_account']) }}">
                                    @error('bank_account')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="">
                                    <label for="bank_ID">bank ID</label>
                                    <input disabled type="text" class="form-control" name="bank_ID" id="bank_ID" value="{{ old('bank_ID',$data['bank_ID']) }}">
                                    @error('bank_ID')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="">
                                    <label for="bank_branch">bank branch</label>
                                    <input disabled type="text" class="form-control" name="bank_branch" id="bank_branch" value="{{ old('bank_branch',$data['bank_branch']) }}">
                                    @error('bank_branch')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
