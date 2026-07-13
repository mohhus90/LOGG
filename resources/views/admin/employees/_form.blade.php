{{--
    Shared Add/Edit employee form.
    Expects: $mode ('create'|'edit'), $data (Employee|null), $departments, $jobs_categories,
             $shifts_types, $branches, $clients (all already in scope from the including view).
--}}
@php
    $emp = $data; // shorthand
    $tabs = [
        'identity'    => __('admin.emp_tab_identity'),
        'contact'     => __('admin.emp_tab_contact'),
        'job_data'    => __('admin.emp_tab_job'),
        'salary_data' => __('admin.emp_tab_salary'),
        'other_data'  => __('admin.emp_tab_other'),
        'client_data' => 'بيانات العميل',
        'login_data'  => __('admin.emp_tab_login'),
    ];
    if ($mode === 'create') {
        $tabs['docs'] = 'ملفات التعيين';
    }
    $tabIcons = [
        'client_data' => 'fas fa-building',
        'login_data'  => 'fas fa-key',
        'docs'        => 'fas fa-folder-open',
    ];
    $firstTab = array_key_first($tabs);
@endphp

<form method="POST"
      action="{{ $mode === 'create' ? route('employees.store') : route('employees.update', $emp->id) }}"
      enctype="multipart/form-data">
    @csrf
    <div class="card-body">
        <ul class="nav nav-tabs" id="custom-content-below-tab" role="tablist">
            @foreach($tabs as $key => $label)
                <li class="nav-item">
                    <a class="nav-link {{ $key === $firstTab ? 'active' : '' }}"
                       id="custom-content-below-{{ $key }}-tab" data-toggle="pill"
                       href="#custom-content-below-{{ $key }}" role="tab"
                       aria-controls="custom-content-below-{{ $key }}"
                       aria-selected="{{ $key === $firstTab ? 'true' : 'false' }}">
                        @if(isset($tabIcons[$key]))<i class="{{ $tabIcons[$key] }} mr-1"></i>@endif
                        {{ $label }}
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="tab-content" id="custom-content-below-tabContent">

            {{-- ── TAB: الهوية ── --}}
            <div class="tab-pane fade show active" id="custom-content-below-identity" role="tabpanel" aria-labelledby="custom-content-below-identity-tab">
                <br>
                <div class="row">
                    <div class="col-md-4">
                        <label for="employee_id">{{ __('admin.emp_code') }} <span style="color: red">*</span></label>
                        <input type="text" class="form-control @error('employee_id') is-invalid @enderror" name="employee_id" id="employee_id"
                               value="{{ old('employee_id', optional($emp)->employee_id) }}">
                        @error('employee_id')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="finger_id">{{ __('admin.emp_finger_code') }}</label>
                        <input type="text" class="form-control" name="finger_id" id="finger_id"
                               value="{{ old('finger_id', optional($emp)->finger_id) }}">
                        @error('finger_id')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="employee_name_A">
                            {{ __('admin.emp_name_ar') }}<span style="color:red">*</span>
                            @if($mode === 'create')<small class="text-muted">({{ __('admin.emp_name_ar_hint') }})</small>@endif
                        </label>
                        <input type="text" class="form-control @error('employee_name_A') is-invalid @enderror" name="employee_name_A" id="employee_name_A"
                               value="{{ old('employee_name_A', optional($emp)->employee_name_A) }}" dir="rtl"
                               @if($mode === 'create') placeholder="{{ __('admin.emp_name_ar_example') }}" @endif>
                        @error('employee_name_A')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="employee_name_E">
                            {{ __('admin.emp_name_en') }}<span style="color:red">*</span>
                            @if($mode === 'create')<small class="text-muted">({{ __('admin.emp_translate_auto') }})</small>@endif
                        </label>
                        <input type="text" class="form-control @error('employee_name_E') is-invalid @enderror" name="employee_name_E" id="employee_name_E"
                               value="{{ old('employee_name_E', optional($emp)->employee_name_E) }}" dir="ltr"
                               @if($mode === 'create') placeholder="e.g. Mohamed Ahmed Ali Hassan" @endif>
                        @error('employee_name_E')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="national_id">{{ __('admin.emp_national_id') }}@if($mode === 'create')<span style="color: red">*</span>@endif</label>
                        <input type="text" class="form-control @error('national_id') is-invalid @enderror" name="national_id" id="national_id"
                               value="{{ old('national_id', optional($emp)->national_id) }}">
                        @error('national_id')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="insurance_no">{{ __('admin.emp_insurance_no') }}</label>
                        <input type="text" class="form-control" name="insurance_no" id="insurance_no"
                               value="{{ old('insurance_no', optional($emp)->insurance_no) }}">
                        @error('insurance_no')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="birth_date">{{ __('admin.emp_birth_date') }}</label>
                        <input type="date" class="form-control" name="birth_date" id="birth_date"
                               value="{{ old('birth_date', optional($emp)->birth_date) }}">
                        @error('birth_date')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="emp_gender">{{ __('admin.emp_gender') }}</label>
                        <select class="form-control" name="emp_gender" id="emp_gender">
                            <option value="">{{ __('admin.emp_gender_choose') }}</option>
                            <option value="1" @if (old('emp_gender', optional($emp)->emp_gender)==1)selected @endif>{{ __('admin.male') }}</option>
                            <option value="2" @if (old('emp_gender', optional($emp)->emp_gender)==2)selected @endif>{{ __('admin.female') }}</option>
                        </select>
                        @error('emp_gender')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="emp_social_status">{{ __('admin.emp_marital_status') }}</label>
                        <select class="form-control" name="emp_social_status" id="emp_social_status">
                            <option value="">{{ __('admin.emp_marital_choose') }}</option>
                            <option value="1" @if (old('emp_social_status', optional($emp)->emp_social_status)==1)selected @endif>{{ __('admin.emp_single') }}</option>
                            <option value="2" @if (old('emp_social_status', optional($emp)->emp_social_status)==2)selected @endif>{{ __('admin.emp_married') }}</option>
                            <option value="3" @if (old('emp_social_status', optional($emp)->emp_social_status)==3)selected @endif>{{ __('admin.emp_married_dependent') }}</option>
                        </select>
                        @error('emp_social_status')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    @if($mode === 'edit')
                        <div class="col-md-8">
                            <div class="alert alert-info py-2 mt-2 mb-0">
                                <i class="fas fa-info-circle mr-1"></i>
                                يمكنك رفع <strong>الصورة الشخصية</strong> والسيرة الذاتية وجميع ملفات التعيين من قسم <strong>ملفات التعيين</strong> أسفل الصفحة
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ── TAB: بيانات الاتصال ── --}}
            <div class="tab-pane fade" id="custom-content-below-contact" role="tabpanel" aria-labelledby="custom-content-below-contact-tab">
                <br>
                <div class="row">
                    <div class="col-md-4">
                        <label for="employee_address">{{ __('admin.emp_address') }}</label>
                        <input type="text" class="form-control" name="employee_address" id="employee_address"
                               value="{{ old('employee_address', optional($emp)->employee_address) }}">
                        @error('employee_address')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="emp_mobile">{{ __('admin.emp_mobile') }}</label>
                        <input type="text" class="form-control" name="emp_mobile" id="emp_mobile"
                               value="{{ old('emp_mobile', optional($emp)->emp_mobile) }}">
                        @error('emp_mobile')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="emp_home_tel">{{ __('admin.emp_home_phone') }}</label>
                        <input type="text" class="form-control" name="emp_home_tel" id="emp_home_tel"
                               value="{{ old('emp_home_tel', optional($emp)->emp_home_tel) }}">
                        @error('emp_home_tel')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="emp_email">Email</label>
                        <input type="email" class="form-control" name="emp_email" id="emp_email"
                               value="{{ old('emp_email', optional($emp)->emp_email) }}">
                        @error('emp_email')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- ── TAB: بيانات وظيفية ── --}}
            <div class="tab-pane fade" id="custom-content-below-job_data" role="tabpanel" aria-labelledby="custom-content-below-job_data-tab">
                <br>
                <div class="row">
                    <div class="col-md-4">
                        <label for="emp_start_date">{{ __('admin.emp_join_date') }}</label>
                        <input type="date" class="form-control" name="emp_start_date" id="emp_start_date"
                               value="{{ old('emp_start_date', optional($emp)->emp_start_date ?? ($mode === 'create' ? today()->format('Y-m-d') : null)) }}">
                        @error('emp_start_date')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="functional_status">{{ __('admin.emp_status') }}</label>
                        <select class="form-control" name="functional_status" id="functional_status">
                            <option value="1" @if (old('functional_status', optional($emp)->functional_status ?? 1)==1)selected @endif>{{ __('admin.emp_working') }}</option>
                            <option value="2" @if (old('functional_status', optional($emp)->functional_status)==2)selected @endif>{{ __('admin.emp_not_working') }}</option>
                        </select>
                        @error('functional_status')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="insurance_status">{{ __('admin.emp_insurance_status') }}</label>
                        <select class="form-control" name="insurance_status" id="insurance_status">
                            <option value="1" @if (old('insurance_status', optional($emp)->insurance_status)==1)selected @endif>{{ __('admin.emp_insured') }}</option>
                            <option value="2" @if (old('insurance_status', optional($emp)->insurance_status)==2)selected @endif>{{ __('admin.emp_not_insured') }}</option>
                            <option value="3" @if (old('insurance_status', optional($emp)->insurance_status)==3)selected @endif>{{ __('admin.emp_training') }}</option>
                            <option value="4" @if (old('insurance_status', optional($emp)->insurance_status)==4)selected @endif>{{ __('admin.emp_service_ended') }}</option>
                        </select>
                        @error('insurance_status')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="probation_end_date">نهاية فترة الاختبار</label>
                        <input type="date" class="form-control" name="probation_end_date" id="probation_end_date"
                               value="{{ old('probation_end_date', optional($emp)->probation_end_date) }}">
                    </div>
                    <div class="col-md-4">
                        <label for="contract_end_date">نهاية العقد (فارغ = غير محدد المدة)</label>
                        <input type="date" class="form-control" name="contract_end_date" id="contract_end_date"
                               value="{{ old('contract_end_date', optional($emp)->contract_end_date) }}">
                    </div>
                    <div class="col-md-4">
                        <div class="custom-control custom-switch mt-4">
                            <input type="checkbox" class="custom-control-input" id="apply_income_tax" name="apply_income_tax" value="1"
                                   {{ old('apply_income_tax', optional($emp)->apply_income_tax) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="apply_income_tax">خصم ضريبة كسب العمل من هذا الموظف</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="emp_jobs_id">{{ __('admin.emp_job') }}<span style="color: red">*</span></label>
                        <select name="emp_jobs_id" id="emp_jobs_id" class="form-control select2">
                            <option value="">{{ __('admin.emp_job_choose') }}</option>
                            @foreach($jobs_categories as $job)
                                <option value="{{ $job->id }}" {{ old('emp_jobs_id', optional($emp)->emp_jobs_id) == $job->id ? 'selected' : '' }}>
                                    {{ $job->job_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('emp_jobs_id')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="emp_departments_id">{{ __('admin.emp_dept') }}<span style="color: red">*</span></label>
                        <select name="emp_departments_id" id="emp_departments_id" class="form-control select2">
                            <option value="">{{ __('admin.emp_dept_choose') }}</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ old('emp_departments_id', optional($emp)->emp_departments_id) == $department->id ? 'selected' : '' }}>
                                    {{ $department->dep_name}}
                                </option>
                            @endforeach
                        </select>
                        @error('emp_departments_id')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="shifts_types_id">{{ __('admin.emp_shift') }}</label>
                        <select name="shifts_types_id" id="shifts_types_id" class="form-control">
                            <option value="">{{ __('admin.emp_shift_choose') }}</option>
                            @foreach($shifts_types as $shifts_type)
                                <option value="{{ $shifts_type->id }}"
                                    data-hours="{{ $shifts_type->total_hour ?? '' }}"
                                    {{ old('shifts_types_id', optional($emp)->shifts_types_id) == $shifts_type->id ? 'selected' : '' }}>
                                    @if(!empty($shifts_type->from_time) && !empty($shifts_type->to_time))
                                        {{ __('admin.from') }} {{ \Carbon\Carbon::parse($shifts_type->from_time)->format('H:i') }}
                                        {{ __('admin.to') }} {{ \Carbon\Carbon::parse($shifts_type->to_time)->format('H:i') }}
                                        ({{ $shifts_type->total_hour }} {{ __('admin.hour') }})
                                    @else
                                        {{ $shifts_type->type }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('shifts_types_id')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="branches_id">{{ __('admin.emp_branch') }}<span style="color: red">*</span></label>
                        <select name="branches_id" id="branches_id" class="form-control select2">
                            <option value="">{{ __('admin.emp_branch_choose') }}</option>
                            @foreach($branches as $branche)
                                <option value="{{ $branche->id }}" {{ old('branches_id', optional($emp)->branches_id) == $branche->id ? 'selected' : '' }}>
                                    {{ $branche->branch_name}}
                                </option>
                            @endforeach
                        </select>
                        @error('branches_id')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="weekly_off_day">{{ __('admin.emp_weekly_off') }}</label>
                        @php $woff = old('weekly_off_day', optional($emp)->weekly_off_day); @endphp
                        <select name="weekly_off_day" id="weekly_off_day" class="form-control">
                            <option value="">--</option>
                            <option value="0" {{ $woff !== null && $woff !== '' && (int)$woff === 0 ? 'selected' : '' }}>{{ __('admin.emp_sunday') }}</option>
                            <option value="1" {{ (string)$woff === '1' ? 'selected' : '' }}>{{ __('admin.emp_monday') }}</option>
                            <option value="2" {{ (string)$woff === '2' ? 'selected' : '' }}>{{ __('admin.emp_tuesday') }}</option>
                            <option value="3" {{ (string)$woff === '3' ? 'selected' : '' }}>{{ __('admin.emp_wednesday') }}</option>
                            <option value="4" {{ (string)$woff === '4' ? 'selected' : '' }}>{{ __('admin.emp_thursday') }}</option>
                            <option value="5" {{ (string)$woff === '5' ? 'selected' : '' }}>{{ __('admin.emp_friday') }}</option>
                            <option value="6" {{ (string)$woff === '6' ? 'selected' : '' }}>{{ __('admin.emp_saturday') }}</option>
                        </select>
                        <small class="text-muted">{{ __('admin.emp_weekly_off_hint') }}</small>
                        @error('weekly_off_day')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="daily_work_hours">{{ __('admin.emp_work_hours') }}</label>
                        <input type="number" class="form-control" name="daily_work_hours" id="daily_work_hours"
                               value="{{ old('daily_work_hours', optional($emp)->daily_work_hours) }}">
                        @error('daily_work_hours')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="resignation_status">{{ __('admin.emp_leave_reason_label') }}</label>
                        <select class="form-control" name="resignation_status" id="resignation_status">
                            <option value="">{{ __('admin.emp_marital_choose') }}</option>
                            <option value="1" @if (old('resignation_status', optional($emp)->resignation_status)==1)selected @endif>{{ __('admin.emp_resignation') }}</option>
                            <option value="2" @if (old('resignation_status', optional($emp)->resignation_status)==2)selected @endif>{{ __('admin.emp_fired') }}</option>
                            <option value="3" @if (old('resignation_status', optional($emp)->resignation_status)==3)selected @endif>{{ __('admin.emp_left_work') }}</option>
                            <option value="4" @if (old('resignation_status', optional($emp)->resignation_status)==4)selected @endif>{{ __('admin.emp_retirement_age') }}</option>
                            <option value="5" @if (old('resignation_status', optional($emp)->resignation_status)==5)selected @endif>{{ __('admin.emp_death') }}</option>
                        </select>
                        @error('resignation_status')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="resignation_date">{{ __('admin.emp_leave_date') }}</label>
                        <input type="date" class="form-control" name="resignation_date" id="resignation_date"
                               value="{{ old('resignation_date', optional($emp)->resignation_date) }}">
                        @error('resignation_date')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="resignation_cause">{{ __('admin.emp_leave_reason') }}</label>
                        <input type="text" class="form-control" name="resignation_cause" id="resignation_cause"
                               value="{{ old('resignation_cause', optional($emp)->resignation_cause) }}">
                        @error('resignation_cause')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- ── TAB: بيانات الراتب ── --}}
            <div class="tab-pane fade" id="custom-content-below-salary_data" role="tabpanel" aria-labelledby="custom-content-below-salary_data-tab">
                <br>
                <div class="row">
                    <div class="col-md-4">
                        <label for="emp_sal">{{ __('admin.emp_basic_salary') }}</label>
                        <input type="number" class="form-control" name="emp_sal" id="emp_sal"
                               value="{{ old('emp_sal', optional($emp)->emp_sal) }}">
                        @error('emp_sal')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="emp_sal_insurance">{{ __('admin.emp_insurance_salary') }}</label>
                        <input type="number" class="form-control" name="emp_sal_insurance" id="emp_sal_insurance"
                               value="{{ old('emp_sal_insurance', optional($emp)->emp_sal_insurance) }}">
                        @error('emp_sal_insurance')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="emp_fixed_allowances">{{ __('admin.emp_fixed_allowance') }}</label>
                        <input type="number" class="form-control" name="emp_fixed_allowances" id="emp_fixed_allowances"
                               value="{{ old('emp_fixed_allowances', optional($emp)->emp_fixed_allowances) }}">
                        @error('emp_fixed_allowances')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- ── إعدادات الأوفرتايم (تظهر الآن في الإضافة والتعديل) ── --}}
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-header py-2">
                                <strong><i class="fas fa-sliders-h ml-1"></i>{{ __('admin.emp_overtime_settings') }}</strong>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 form-group">
                                        <label>{{ __('admin.emp_overtime_multiplier') }}</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="custom_overtime_multiplier"
                                                step="0.01" min="0" max="10" placeholder="e.g. 2"
                                                value="{{ old('custom_overtime_multiplier', optional($emp)->custom_overtime_multiplier) }}">
                                            <div class="input-group-append"><span class="input-group-text">×</span></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>{{ __('admin.emp_calc_overtime') }}</label>
                                        <select class="form-control" name="overtime_enabled">
                                            <option value="1" {{ old('overtime_enabled', optional($emp)->overtime_enabled ?? 1) == 1 ? 'selected' : '' }}>{{ __('admin.emp_ot_yes') }}</option>
                                            <option value="0" {{ old('overtime_enabled', optional($emp)->overtime_enabled ?? 1) == 0 ? 'selected' : '' }}>{{ __('admin.emp_ot_no') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>{{ __('admin.emp_calc_delay') }}</label>
                                        <select class="form-control" name="late_deduction_enabled">
                                            <option value="1" {{ old('late_deduction_enabled', optional($emp)->late_deduction_enabled ?? 1) == 1 ? 'selected' : '' }}>{{ __('admin.emp_delay_yes') }}</option>
                                            <option value="0" {{ old('late_deduction_enabled', optional($emp)->late_deduction_enabled ?? 1) == 0 ? 'selected' : '' }}>{{ __('admin.emp_delay_no') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-4">
                        <label for="motivation">{{ __('admin.emp_incentive') }}</label>
                        <input type="number" class="form-control" name="motivation" id="motivation"
                               value="{{ old('motivation', optional($emp)->motivation) }}">
                        @error('motivation')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="medical_insurance">{{ __('admin.emp_health_insurance') }}</label>
                        <input type="number" class="form-control" name="medical_insurance" id="medical_insurance"
                               value="{{ old('medical_insurance', optional($emp)->medical_insurance) }}">
                        @error('medical_insurance')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="sal_cash_visa">{{ __('admin.emp_payment_method') }}</label>
                        <select class="form-control" name="sal_cash_visa" id="sal_cash_visa">
                            <option value="">{{ __('admin.emp_marital_choose') }}</option>
                            <option value="1" @if (old('sal_cash_visa', optional($emp)->sal_cash_visa)==1)selected @endif>{{ __('admin.emp_cash') }}</option>
                            <option value="2" @if (old('sal_cash_visa', optional($emp)->sal_cash_visa)==2)selected @endif>{{ __('admin.emp_visa') }}</option>
                        </select>
                        @error('sal_cash_visa')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- ── بيانات التأمين الطبي ── --}}
                <div class="row mt-3">
                    <div class="col-12">
                        <h6 class="text-info border-bottom pb-1"><i class="fas fa-shield-alt mr-1"></i>بيانات التأمين الطبي</h6>
                    </div>
                    <div class="col-md-4">
                        <label for="medical_id">رقم التأمين الطبي (Medical ID)</label>
                        <input type="text" class="form-control" name="medical_id" id="medical_id"
                               value="{{ old('medical_id', optional($emp)->medical_id) }}">
                    </div>
                    <div class="col-md-4">
                        <label for="medical_status">حالة التأمين الطبي (Medical Status)</label>
                        @php $mstatus = old('medical_status', optional($emp)->medical_status); @endphp
                        <select class="form-control" name="medical_status" id="medical_status">
                            <option value="">— اختر —</option>
                            <option value="Active"      {{ $mstatus == 'Active'      ? 'selected' : '' }}>Active</option>
                            <option value="Resigned"    {{ $mstatus == 'Resigned'    ? 'selected' : '' }}>Resigned</option>
                            <option value="Cancelled"   {{ $mstatus == 'Cancelled'   ? 'selected' : '' }}>Cancelled</option>
                            <option value="Terminated"  {{ $mstatus == 'Terminated'  ? 'selected' : '' }}>Terminated</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="medical_progress">تقدم التأمين الطبي (Medical Progress)</label>
                        @php $mprogress = old('medical_progress', optional($emp)->medical_progress); @endphp
                        <select class="form-control" name="medical_progress" id="medical_progress">
                            <option value="">— اختر —</option>
                            <option value="Completed"   {{ $mprogress == 'Completed'   ? 'selected' : '' }}>Completed</option>
                            <option value="Not Started" {{ $mprogress == 'Not Started' ? 'selected' : '' }}>Not Started</option>
                            <option value="In Progress" {{ $mprogress == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="In process"  {{ $mprogress == 'In process'  ? 'selected' : '' }}>In process</option>
                            <option value="Cancelled"   {{ $mprogress == 'Cancelled'   ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-4">
                        <label for="bank_name">{{ __('admin.emp_bank_name') }}</label>
                        <input type="text" class="form-control" name="bank_name" id="bank_name"
                               value="{{ old('bank_name', optional($emp)->bank_name) }}">
                        @error('bank_name')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="bank_account">{{ __('admin.emp_bank_account') }}</label>
                        <input type="text" class="form-control" name="bank_account" id="bank_account"
                               value="{{ old('bank_account', optional($emp)->bank_account) }}">
                        @error('bank_account')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="bank_ID">bank ID</label>
                        <input type="text" class="form-control" name="bank_ID" id="bank_ID"
                               value="{{ old('bank_ID', optional($emp)->bank_ID) }}">
                        @error('bank_ID')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <label for="bank_branch">bank branch</label>
                        <input type="text" class="form-control" name="bank_branch" id="bank_branch"
                               value="{{ old('bank_branch', optional($emp)->bank_branch) }}">
                        @error('bank_branch')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- ── TAB: بيانات أخرى / تعليمية ── --}}
            <div class="tab-pane fade" id="custom-content-below-other_data" role="tabpanel" aria-labelledby="custom-content-below-other_data-tab">
                <br>
                <div class="row">
                    <div class="col-md-4">
                        <label for="emp_military_status">{{ __('admin.emp_military') }}</label>
                        <select class="form-control" name="emp_military_status" id="emp_military_status">
                            <option value="">{{ __('admin.emp_marital_choose') }}</option>
                            <option value="1" @if (old('emp_military_status', optional($emp)->emp_military_status)==1)selected @endif>{{ __('admin.emp_military_served') }}</option>
                            <option value="2" @if (old('emp_military_status', optional($emp)->emp_military_status)==2)selected @endif>{{ __('admin.emp_military_exempt') }}</option>
                            <option value="3" @if (old('emp_military_status', optional($emp)->emp_military_status)==3)selected @endif>{{ __('admin.emp_military_deferred') }}</option>
                            <option value="4" @if (old('emp_military_status', optional($emp)->emp_military_status)==4)selected @endif>{{ __('admin.emp_military_temp_exempt') }}</option>
                            <option value="5" @if (old('emp_military_status', optional($emp)->emp_military_status)==5)selected @endif>{{ __('admin.emp_military_not_required') }}</option>
                        </select>
                        @error('emp_military_status')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="emp_qualification">{{ __('admin.emp_education') }}</label>
                        <input type="text" class="form-control" name="emp_qualification" id="emp_qualification"
                               value="{{ old('emp_qualification', optional($emp)->emp_qualification) }}">
                        @error('emp_qualification')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="qualification_year">{{ __('admin.emp_edu_year') }}</label>
                        <input type="text" class="form-control" name="qualification_year" id="qualification_year"
                               value="{{ old('qualification_year', optional($emp)->qualification_year) }}">
                        @error('qualification_year')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <label for="qualification_grade">{{ __('admin.emp_edu_grade') }}</label>
                        <select class="form-control" name="qualification_grade" id="qualification_grade">
                            <option value="">{{ __('admin.emp_marital_choose') }}</option>
                            <option value="1" @if (old('qualification_grade', optional($emp)->qualification_grade)==1)selected @endif>{{ __('admin.emp_distinction') }}</option>
                            <option value="2" @if (old('qualification_grade', optional($emp)->qualification_grade)==2)selected @endif>{{ __('admin.emp_very_good') }}</option>
                            <option value="3" @if (old('qualification_grade', optional($emp)->qualification_grade)==3)selected @endif>{{ __('admin.emp_very_good_high') }}</option>
                            <option value="4" @if (old('qualification_grade', optional($emp)->qualification_grade)==4)selected @endif>{{ __('admin.emp_good') }}</option>
                            <option value="5" @if (old('qualification_grade', optional($emp)->qualification_grade)==5)selected @endif>{{ __('admin.emp_accepted') }}</option>
                        </select>
                        @error('qualification_grade')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- ── TAB: بيانات العميل ── --}}
            <div class="tab-pane fade" id="custom-content-below-client_data" role="tabpanel" aria-labelledby="custom-content-below-client_data-tab">
                <br>
                <div class="row">
                    <div class="col-md-4">
                        <label>العميل</label>
                        <select class="form-control select2" name="client_id" id="client_id">
                            <option value="">— بدون عميل —</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id', optional($emp)->client_id) == $client->id ? 'selected' : '' }}>{{ $client->client_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>كود العميل (Custom ID / HRID)</label>
                        <input type="text" class="form-control" name="hrid" value="{{ old('hrid', optional($emp)->hrid) }}">
                    </div>
                    <div class="col-md-4">
                        <label>جهة الاتصال الطارئة (Reference Number)</label>
                        <input type="text" class="form-control" name="reference_mobile" value="{{ old('reference_mobile', optional($emp)->reference_mobile) }}">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-4">
                        <label>صلة القرابة (Relative)</label>
                        <input type="text" class="form-control" name="relative_relation" value="{{ old('relative_relation', optional($emp)->relative_relation) }}">
                    </div>
                    <div class="col-md-4">
                        <label>حالة أوراق التعيين (Hiring Documents)</label>
                        <input type="text" class="form-control" name="hiring_documents_status" value="{{ old('hiring_documents_status', optional($emp)->hiring_documents_status) }}">
                    </div>
                    <div class="col-md-4">
                        <label>تاريخ بداية التأمين (Start Date Of Social)</label>
                        <input type="date" class="form-control" name="insurance_start_date" value="{{ old('insurance_start_date', optional($emp)->insurance_start_date) }}">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-4">
                        <label>تاريخ انتهاء التأمين (End Date Of Social)</label>
                        <input type="date" class="form-control" name="insurance_end_date" value="{{ old('insurance_end_date', optional($emp)->insurance_end_date) }}">
                    </div>
                    <div class="col-md-4">
                        <label>ملاحظات نموذج 1 (Form 1 Comments)</label>
                        <textarea class="form-control" name="form1_notes" rows="3">{{ old('form1_notes', optional($emp)->form1_notes) }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label>ملاحظات نموذج 6 (Form 6 Comments)</label>
                        <textarea class="form-control" name="form6_notes" rows="3">{{ old('form6_notes', optional($emp)->form6_notes) }}</textarea>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <label>ملاحظات (Comments)</label>
                        <textarea class="form-control" name="client_notes" rows="3">{{ old('client_notes', optional($emp)->client_notes) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- ── TAB: بيانات الدخول (تعريفية فقط) ── --}}
            <div class="tab-pane fade" id="custom-content-below-login_data" role="tabpanel" aria-labelledby="custom-content-below-login_data-tab">
                <br>
                <div class="alert alert-secondary py-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    {{ __('admin.emp_login_hint') }}
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <label for="login_username">{{ __('admin.emp_login_username') }}</label>
                        <input type="text" class="form-control @error('login_username') is-invalid @enderror" name="login_username" id="login_username" dir="ltr"
                               value="{{ old('login_username', optional($emp)->login_username) }}">
                        @error('login_username')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="login_password">{{ __('admin.emp_login_password') }}</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="login_password" id="login_password" dir="ltr"
                                   value="{{ old('login_password', optional($emp)->login_password) }}">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary emp-generate-password">
                                    <i class="fas fa-sync-alt mr-1"></i>{{ __('admin.emp_generate_password') }}
                                </button>
                            </div>
                        </div>
                        @error('login_password')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            @if($mode === 'create')
                {{-- ── TAB: Documents (preview only pre-save) ── --}}
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
            @endif
        </div>

        <div class="text-center mt-4">
            <button type="submit" class="btn btn-primary btn-lg col-2">{{ $mode === 'create' ? __('admin.add') : __('admin.update') }}</button>
            <a class="btn btn-warning btn-lg col-2" href="{{ route('employees.index') }}">{{ __('admin.cancel') }}</a>
        </div>
    </div>
</form>
