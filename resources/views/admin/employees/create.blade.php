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
    .form-group { margin-bottom: 1rem; }
    select.form-control {
        height: auto !important;
        line-height: 1.5 !important;
        padding-top: 0.45rem !important;
        padding-bottom: 0.45rem !important;
    }
</style>
@endsection

@section('home')
<a href="{{ route('employees.index') }}">{{ __('admin.emp_title') }}</a>
@endsection

@section('startpage')
{{ __('admin.add') }}
@endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">{{ __('admin.emp_add_new') }}</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data">
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
                        <li class="nav-item">
                            <a class="nav-link" id="custom-content-below-docs-tab" data-toggle="pill" href="#custom-content-below-docs" role="tab" aria-controls="custom-content-below-docs" aria-selected="false"><i class="fas fa-folder-open mr-1"></i>ملفات التعيين</a>
                        </li>
                    </ul>

                    <div class="tab-content" id="custom-content-below-tabContent">
                        <div class="tab-pane fade show active" id="custom-content-below-baisc_data" role="tabpanel" aria-labelledby="custom-content-below-baisc_data-tab">
                            <br>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="employee_id">{{ __('admin.emp_code') }} <span style="color: red">*</span></label>
                                        <input type="text" class="form-control" name="employee_id" id="employee_id" value="{{ old('employee_id') }}">
                                        @error('employee_id')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="finger_id">{{ __('admin.emp_finger_code') }}</label>
                                        <input type="text" class="form-control" name="finger_id" id="finger_id" value="{{ old('finger_id') }}">
                                        @error('finger_id')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="employee_name_A">
                                            {{ __('admin.emp_name_ar') }}<span style="color:red">*</span>
                                            <small class="text-muted">({{ __('admin.emp_name_ar_hint') }})</small>
                                        </label>
                                        <input type="text" class="form-control" name="employee_name_A" id="employee_name_A"
                                            value="{{ old('employee_name_A') }}" dir="rtl"
                                            placeholder="{{ __('admin.emp_name_ar_example') }}">
                                        @error('employee_name_A')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="employee_name_E">
                                            {{ __('admin.emp_name_en') }}<span style="color:red">*</span>
                                            <small class="text-muted">({{ __('admin.emp_translate_auto') }})</small>
                                        </label>
                                        <input type="text" class="form-control" name="employee_name_E" id="employee_name_E"
                                            value="{{ old('employee_name_E') }}" dir="ltr"
                                            placeholder="e.g. Mohamed Ahmed Ali Hassan">
                                        @error('employee_name_E')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="employee_address">{{ __('admin.emp_address') }}</label>
                                        <input type="text" class="form-control" name="employee_address" id="employee_address" value="{{ old('employee_address') }}">
                                        @error('employee_address')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="national_id">{{ __('admin.emp_national_id') }}<span style="color: red">*</span></label>
                                        <input type="text" class="form-control" name="national_id" id="national_id" value="{{ old('national_id') }}">
                                        @error('national_id')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="insurance_no">{{ __('admin.emp_insurance_no') }}</label>
                                        <input type="text" class="form-control" name="insurance_no" id="insurance_no" value="{{ old('insurance_no') }}">
                                        @error('insurance_no')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="emp_mobile">{{ __('admin.emp_mobile') }}</label>
                                        <input type="text" class="form-control" name="emp_mobile" id="emp_mobile" value="{{ old('emp_mobile') }}">
                                        @error('emp_mobile')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="emp_home_tel">{{ __('admin.emp_home_phone') }}</label>
                                        <input type="text" class="form-control" name="emp_home_tel" id="emp_home_tel" value="{{ old('emp_home_tel') }}">
                                        @error('emp_home_tel')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="emp_email">Email</label>
                                        <input type="email" class="form-control" name="emp_email" id="emp_email" value="{{ old('emp_email') }}">
                                        @error('emp_email')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="birth_date">{{ __('admin.emp_birth_date') }}</label>
                                        <input type="date" class="form-control" name="birth_date" id="birth_date" value="{{ old('birth_date') }}">
                                        @error('birth_date')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="emp_gender">{{ __('admin.emp_gender') }}</label>
                                        <select class="form-control" name="emp_gender" id="emp_gender">
                                            <option value="">{{ __('admin.emp_gender_choose') }}</option>
                                            <option value="1" @if (old('emp_gender')==1)selected @endif>{{ __('admin.male') }}</option>
                                            <option value="2" @if (old('emp_gender')==2)selected @endif>{{ __('admin.female') }}</option>
                                        </select>
                                        @error('emp_gender')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="emp_social_status">{{ __('admin.emp_marital_status') }}</label>
                                        <select class="form-control" name="emp_social_status" id="emp_social_status">
                                            <option value="">{{ __('admin.emp_marital_choose') }}</option>
                                            <option value="1" @if (old('emp_social_status')==1)selected @endif>{{ __('admin.emp_single') }}</option>
                                            <option value="2" @if (old('emp_social_status')==2)selected @endif>{{ __('admin.emp_married') }}</option>
                                            <option value="3" @if (old('emp_social_status')==3)selected @endif>{{ __('admin.emp_married_dependent') }}</option>
                                        </select>
                                        @error('emp_social_status')<div class="text-danger">{{ $message }}</div>@enderror
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
                                        <input type="date" class="form-control" name="emp_start_date" id="emp_start_date" value="{{ old('emp_start_date', today()->format('Y-m-d')) }}">
                                        @error('emp_start_date')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="functional_status">{{ __('admin.emp_status') }}</label>
                                        <select class="form-control" name="functional_status" id="functional_status">
                                            <option value="1" @if (old('functional_status')==1)selected @endif>{{ __('admin.emp_working') }}</option>
                                            <option value="2" @if (old('functional_status')==2)selected @endif>{{ __('admin.emp_not_working') }}</option>
                                        </select>
                                        @error('functional_status')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="insurance_status">{{ __('admin.emp_insurance_status') }}</label>
                                        <select class="form-control" name="insurance_status" id="insurance_status">
                                            <option value="1" @if (old('insurance_status')==1)selected @endif>{{ __('admin.emp_insured') }}</option>
                                            <option value="2" @if (old('insurance_status')==2)selected @endif>{{ __('admin.emp_not_insured') }}</option>
                                            <option value="3" @if (old('insurance_status')==3)selected @endif>{{ __('admin.emp_training') }}</option>
                                            <option value="4" @if (old('insurance_status')==4)selected @endif>{{ __('admin.emp_service_ended') }}</option>
                                        </select>
                                        @error('insurance_status')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="probation_end_date">نهاية فترة الاختبار</label>
                                        <input type="date" class="form-control" name="probation_end_date" id="probation_end_date" value="{{ old('probation_end_date') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="contract_end_date">نهاية العقد (فارغ = غير محدد المدة)</label>
                                        <input type="date" class="form-control" name="contract_end_date" id="contract_end_date" value="{{ old('contract_end_date') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="custom-control custom-switch mt-4">
                                        <input type="checkbox" class="custom-control-input" id="apply_income_tax" name="apply_income_tax" value="1" {{ old('apply_income_tax') ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="apply_income_tax">خصم ضريبة كسب العمل من هذا الموظف</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="emp_jobs_id">{{ __('admin.emp_job') }}<span style="color: red">*</span></label>
                                        <select name="emp_jobs_id" id="emp_jobs_id" class="form-control select2">
                                            <option value="">{{ __('admin.emp_job_choose') }}</option>
                                            @foreach($jobs_categories as $job)
                                                <option value="{{ $job->id }}" {{ old('emp_jobs_id') == $job->id ? 'selected' : '' }}>
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
                                        <label for="emp_departments_id">{{ __('admin.emp_dept') }}<span style="color: red">*</span></label>
                                        <select name="emp_departments_id" id="emp_departments_id" class="form-control select2">
                                            <option value="">{{ __('admin.emp_dept_choose') }}</option>
                                            @foreach($departments as $department)
                                                <option value="{{ $department->id }}" {{ old('emp_departments_id') == $department->id ? 'selected' : '' }}>
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
                                                <option value="{{ $shifts_type->id }}"
                                                    data-hours="{{ $shifts_type->total_hour }}"
                                                    {{ old('shifts_types_id') == $shifts_type->id ? 'selected' : '' }}>
                                                    {{ __('admin.from') }} {{ \Carbon\Carbon::parse($shifts_type->from_time)->format('H:i') }}
                                                    {{ __('admin.to') }} {{ \Carbon\Carbon::parse($shifts_type->to_time)->format('H:i') }}
                                                    ({{ $shifts_type->total_hour }} {{ __('admin.hour') }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('shifts_types_id')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="branches_id">{{ __('admin.emp_branch') }}<span style="color: red">*</span></label>
                                        <select name="branches_id" id="branches_id" class="form-control select2">
                                            <option value="">{{ __('admin.emp_branch_choose') }}</option>
                                            @foreach($branches as $branche)
                                                <option value="{{ $branche->id }}" {{ old('branches_id') == $branche->id ? 'selected' : '' }}>
                                                    {{ $branche->branch_name}}
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
                                        <input type="number" class="form-control" name="daily_work_hours" id="daily_work_hours" value="{{ old('daily_work_hours') }}">
                                        @error('daily_work_hours')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="resignation_status">{{ __('admin.emp_leave_reason_label') }}</label>
                                        <select class="form-control" name="resignation_status" id="resignation_status">
                                            <option value="">{{ __('admin.emp_marital_choose') }}</option>
                                            <option value="1" @if (old('resignation_status')==1)selected @endif>{{ __('admin.emp_resignation') }}</option>
                                            <option value="2" @if (old('resignation_status')==2)selected @endif>{{ __('admin.emp_fired') }}</option>
                                            <option value="3" @if (old('resignation_status')==3)selected @endif>{{ __('admin.emp_left_work') }}</option>
                                            <option value="4" @if (old('resignation_status')==4)selected @endif>{{ __('admin.emp_retirement_age') }}</option>
                                            <option value="5" @if (old('resignation_status')==5)selected @endif>{{ __('admin.emp_death') }}</option>
                                        </select>
                                        @error('resignation_status')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="resignation_date">{{ __('admin.emp_leave_date') }}</label>
                                        <input type="date" class="form-control" name="resignation_date" id="resignation_date" value="{{ old('resignation_date') }}">
                                        @error('resignation_date')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="resignation_cause">{{ __('admin.emp_leave_reason') }}</label>
                                        <input type="text" class="form-control" name="resignation_cause" id="resignation_cause" value="{{ old('resignation_cause') }}">
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
                                            <option value="1" @if (old('emp_military_status')==1)selected @endif>{{ __('admin.emp_military_served') }}</option>
                                            <option value="2" @if (old('emp_military_status')==2)selected @endif>{{ __('admin.emp_military_exempt') }}</option>
                                            <option value="3" @if (old('emp_military_status')==3)selected @endif>{{ __('admin.emp_military_deferred') }}</option>
                                            <option value="4" @if (old('emp_military_status')==4)selected @endif>{{ __('admin.emp_military_temp_exempt') }}</option>
                                            <option value="5" @if (old('emp_military_status')==5)selected @endif>{{ __('admin.emp_military_not_required') }}</option>
                                        </select>
                                        @error('emp_military_status')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="emp_qualification">{{ __('admin.emp_education') }}</label>
                                        <input type="text" class="form-control" name="emp_qualification" id="emp_qualification" value="{{ old('emp_qualification') }}">
                                        @error('emp_qualification')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="qualification_year">{{ __('admin.emp_edu_year') }}</label>
                                        <input type="text" class="form-control" name="qualification_year" id="qualification_year" value="{{ old('qualification_year') }}">
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
                                            <option value="1" @if (old('qualification_grade')==1)selected @endif>{{ __('admin.emp_distinction') }}</option>
                                            <option value="2" @if (old('qualification_grade')==2)selected @endif>{{ __('admin.emp_very_good') }}</option>
                                            <option value="3" @if (old('qualification_grade')==3)selected @endif>{{ __('admin.emp_very_good_high') }}</option>
                                            <option value="4" @if (old('qualification_grade')==4)selected @endif>{{ __('admin.emp_good') }}</option>
                                            <option value="5" @if (old('qualification_grade')==5)selected @endif>{{ __('admin.emp_accepted') }}</option>
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
                                        <input type="number" class="form-control" name="emp_sal" id="emp_sal" value="{{ old('emp_sal') }}">
                                        @error('emp_sal')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="emp_sal_insurance">{{ __('admin.emp_insurance_salary') }}</label>
                                        <input type="number" class="form-control" name="emp_sal_insurance" id="emp_sal_insurance" value="{{ old('emp_sal_insurance') }}">
                                        @error('emp_sal_insurance')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="emp_fixed_allowances">{{ __('admin.emp_fixed_allowance') }}</label>
                                        <input type="number" class="form-control" name="emp_fixed_allowances" id="emp_fixed_allowances" value="{{ old('emp_fixed_allowances') }}">
                                        @error('emp_fixed_allowances')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="motivation">{{ __('admin.emp_incentive') }}</label>
                                        <input type="number" class="form-control" name="motivation" id="motivation" value="{{ old('motivation') }}">
                                        @error('motivation')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="medical_insurance">{{ __('admin.emp_health_insurance') }}</label>
                                        <input type="number" class="form-control" name="medical_insurance" id="medical_insurance" value="{{ old('medical_insurance') }}">
                                        @error('medical_insurance')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="sal_cash_visa">{{ __('admin.emp_payment_method') }}</label>
                                        <select class="form-control" name="sal_cash_visa" id="sal_cash_visa">
                                            <option value="">{{ __('admin.emp_marital_choose') }}</option>
                                            <option value="1" @if (old('sal_cash_visa')==1)selected @endif>{{ __('admin.emp_cash') }}</option>
                                            <option value="2" @if (old('sal_cash_visa')==2)selected @endif>{{ __('admin.emp_visa') }}</option>
                                        </select>
                                        @error('sal_cash_visa')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>

                            {{-- ── بيانات التأمين الطبي ── --}}
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6 class="text-info border-bottom pb-1"><i class="fas fa-shield-alt mr-1"></i>بيانات التأمين الطبي</h6>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="medical_id">رقم التأمين الطبي (Medical ID)</label>
                                        <input type="text" class="form-control" name="medical_id" id="medical_id" value="{{ old('medical_id') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="medical_status">حالة التأمين الطبي (Medical Status)</label>
                                        <select class="form-control" name="medical_status" id="medical_status">
                                            <option value="">— اختر —</option>
                                            <option value="Active"      {{ old('medical_status') == 'Active'      ? 'selected' : '' }}>Active</option>
                                            <option value="Resigned"    {{ old('medical_status') == 'Resigned'    ? 'selected' : '' }}>Resigned</option>
                                            <option value="Cancelled"   {{ old('medical_status') == 'Cancelled'   ? 'selected' : '' }}>Cancelled</option>
                                            <option value="Terminated"  {{ old('medical_status') == 'Terminated'  ? 'selected' : '' }}>Terminated</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="medical_progress">تقدم التأمين الطبي (Medical Progress)</label>
                                        <select class="form-control" name="medical_progress" id="medical_progress">
                                            <option value="">— اختر —</option>
                                            <option value="Completed"   {{ old('medical_progress') == 'Completed'   ? 'selected' : '' }}>Completed</option>
                                            <option value="Not Started" {{ old('medical_progress') == 'Not Started' ? 'selected' : '' }}>Not Started</option>
                                            <option value="In Progress" {{ old('medical_progress') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                            <option value="In process"  {{ old('medical_progress') == 'In process'  ? 'selected' : '' }}>In process</option>
                                            <option value="Cancelled"   {{ old('medical_progress') == 'Cancelled'   ? 'selected' : '' }}>Cancelled</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="bank_name">{{ __('admin.emp_bank_name') }}</label>
                                        <input type="text" class="form-control" name="bank_name" id="bank_name" value="{{ old('bank_name') }}">
                                        @error('bank_name')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="bank_account">{{ __('admin.emp_bank_account') }}</label>
                                        <input type="text" class="form-control" name="bank_account" id="bank_account" value="{{ old('bank_account') }}">
                                        @error('bank_account')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="bank_ID">bank ID</label>
                                        <input type="text" class="form-control" name="bank_ID" id="bank_ID" value="{{ old('bank_ID') }}">
                                        @error('bank_ID')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="">
                                        <label for="bank_branch">bank branch</label>
                                        <input type="text" class="form-control" name="bank_branch" id="bank_branch" value="{{ old('bank_branch') }}">
                                        @error('bank_branch')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── TAB: Documents ── --}}
                        <div class="tab-pane fade" id="custom-content-below-docs" role="tabpanel" aria-labelledby="custom-content-below-docs-tab">
                            <br>
                            <div class="alert alert-warning">
                                <i class="fas fa-info-circle mr-2"></i>
                                <strong>ملفات التعيين:</strong>
                                يمكنك رفع الملفات <strong>بعد حفظ بيانات الموظف</strong> — بعد الضغط على زر "إضافة" ستنتقل تلقائياً لصفحة التعديل حيث يمكنك رفع جميع الملفات.
                            </div>
                            <div class="doc-grid-preview row">
                                @foreach(\App\Models\EmployeeDocument::TYPES as $type => $info)
                                <div class="col-md-2 col-4 mb-3 text-center">
                                    <div style="border:2px dashed #dee2e6;border-radius:10px;padding:16px 8px;color:#9ca3af;">
                                        <i class="fas {{ $info['icon'] }}" style="font-size:1.8rem;"></i>
                                        <div style="font-size:.78rem;font-weight:600;margin-top:6px;">{{ $info['ar'] }}</div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- ── TAB: Client Data ── --}}
                        <div class="tab-pane fade" id="custom-content-below-client_data" role="tabpanel" aria-labelledby="custom-content-below-client_data-tab">
                            <br>
                            <div class="row">
                                <div class="col-md-4">
                                    <label>العميل</label>
                                    <select class="form-control" name="client_id">
                                        <option value="">— بدون عميل —</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->client_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label>كود العميل (Custom ID / HRID)</label>
                                    <input type="text" class="form-control" name="hrid" value="{{ old('hrid') }}">
                                </div>
                                <div class="col-md-4">
                                    <label>جهة الاتصال الطارئة (Reference Number)</label>
                                    <input type="text" class="form-control" name="reference_mobile" value="{{ old('reference_mobile') }}">
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <label>صلة القرابة (Relative)</label>
                                    <input type="text" class="form-control" name="relative_relation" value="{{ old('relative_relation') }}">
                                </div>
                                <div class="col-md-4">
                                    <label>حالة أوراق التعيين (Hiring Documents)</label>
                                    <input type="text" class="form-control" name="hiring_documents_status" value="{{ old('hiring_documents_status') }}">
                                </div>
                                <div class="col-md-4">
                                    <label>تاريخ بداية التأمين (Start Date Of Social)</label>
                                    <input type="date" class="form-control" name="insurance_start_date" value="{{ old('insurance_start_date') }}">
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <label>تاريخ انتهاء التأمين (End Date Of Social)</label>
                                    <input type="date" class="form-control" name="insurance_end_date" value="{{ old('insurance_end_date') }}">
                                </div>
                                <div class="col-md-4">
                                    <label>ملاحظات نموذج 1 (Form 1 Comments)</label>
                                    <textarea class="form-control" name="form1_notes" rows="3">{{ old('form1_notes') }}</textarea>
                                </div>
                                <div class="col-md-4">
                                    <label>ملاحظات نموذج 6 (Form 6 Comments)</label>
                                    <textarea class="form-control" name="form6_notes" rows="3">{{ old('form6_notes') }}</textarea>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <label>ملاحظات (Comments)</label>
                                    <textarea class="form-control" name="client_notes" rows="3">{{ old('client_notes') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary btn-lg col-2">{{ __('admin.add') }}</button>
                        <a class="btn btn-warning btn-lg col-2" href="{{ route('employees.index') }}">{{ __('admin.cancel') }}</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section("script")
    <script>
        $(document).ready(function() {

            $('#shifts_types_id').on('change', function() {
                var hours = $(this).find(':selected').data('hours');
                $('#daily_work_hours').val(hours ? hours : '');
            });

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
                return w.replace(/[ً-ٰٟ]/g, '')
                        .replace(/[أإآ]/g, 'ا')
                        .replace(/ة$/, 'ة');
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

            function isArabic(text) { return /[؀-ۿ]/.test(text); }

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
                    'Words not in dictionary — add their translation to save for next time' +
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
                        alert('Please enter the English translation first');
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
