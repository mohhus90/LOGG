@extends('admin.layouts.admin')

@section('title')
الموظفين
@endsection

@section('start')
    شئون الموظفين
@endsection

@section('css')
{{-- تأكد من تحميل Bootstrap أولاً --}}
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
{{-- ثم Select2 CSS --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
{{-- وأخيرًا Select2 Bootstrap Theme CSS لضمان التنسيق مع Bootstrap --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css">

{{-- يمكنك إضافة أي CSS مخصص هنا بعد كل مكتبات الـ CSS --}}
<style>
   <style>
    /* لتنسيق Select2 وجعله يبدو مثل حقول الإدخال الأخرى */
    .select2-container--bootstrap4 .select2-selection--single {
        height: calc(2.25rem + 2px); /* لتطابق ارتفاع input.form-control */
        padding: 0.375rem 0.75rem; /* لتطابق padding input.form-control */
        display: flex; /* لجعل المحتوى يتوسط عمودياً */
        align-items: center; /* لجعل المحتوى يتوسط عمودياً */

        /* إضافة أو التأكد من تنسيقات الحدود والخلفية هنا */
        border: 1px solid #ced4da; /* هذا هو لون الحدود الافتراضي في Bootstrap forms */
        border-radius: .25rem; /* هذا هو نصف قطر الحدود الافتراضي في Bootstrap forms */
        background-color: #fff; /* لون الخلفية الافتراضي */
    }

    .select2-container--bootstrap4.select2-container--focus .select2-selection--single,
    .select2-container--bootstrap4.select2-container--open .select2-selection--single {
        border-color: #80bdff; /* لون الحدود عند التركيز (Focus) */
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25); /* الظل عند التركيز */
    }

    .select2-container--bootstrap4 .select2-selection__arrow {
        height: calc(2.25rem + 2px); /* لتطابق ارتفاع input.form-control */
        display: flex;
        align-items: center;
        top: 0; /* إعادة ضبط الموضع ليتناسب مع الارتفاع الجديد */
    }
    .select2-container--bootstrap4 .select2-selection__rendered {
        line-height: inherit; /* لإزالة أي تباعد سطر غير مرغوب فيه */
    }

    /* لتحسين المحاذاة في النموذج، خاصة مع RTL */
    .form-group.row .col-form-label {
        text-align: right; /* محاذاة النص لليمين */
    }

    /* مسافات بين الحقول داخل الـ tab-pane */
    .tab-pane .form-group {
        margin-bottom: 1rem; /* مسافة افتراضية أفضل بين الحقول */
    }
    /* تحسين تنسيق Select2 */
.select2-container {
    width: 100% !important; /* لجعل Select2 يأخذ العرض الكامل */
    margin-bottom: 15px; /* مسافة مناسبة أسفل الحقل */
}

/* تحسين محاذاة العناصر في النموذج */
.form-group {
    margin-bottom: 1.5rem; /* زيادة المسافة بين الحقول */
}

/* تحسين محاذاة الـ labels */
.col-form-label {
    padding-top: calc(0.375rem + 1px);
    padding-bottom: calc(0.375rem + 1px);
    margin-bottom: 0;
    font-size: inherit;
    line-height: 1.5;
    text-align: right; /* لمحاذاة النص لليمين في RTL */
}

/* تحسين عرض حقول الإدخال */
.form-control, .select2-selection {
    height: calc(2.25rem + 2px);
    padding: 0.375rem 0.75rem;
    border-radius: 0.25rem;
}

/* تحسين التباعد في الـ tabs */
.tab-content {
    padding: 15px;
    border-left: 1px solid #dee2e649!important;
    border-right: 1px solid #dee2e649!important;
    border-bottom: 1px solid #dee2e649!important;
    border-radius: 0 0 0.25rem 0.25rem!important;
}

/* تحسين عرض العناصر في الصفوف */
.row {
    margin-right: -7.5px;
    margin-left: -7.5px;
}

.row > div {
    padding-right: 7.5px;
    padding-left: 7.5px;
}
</style>
</style>
</style>
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
                        {{-- بيانات اساسية --}}
                        <div class="tab-pane fade show active" id="custom-content-below-baisc_data" role="tabpanel" aria-labelledby="custom-content-below-baisc_data-tab">
                            <br>
                            <div class="row"> {{-- Start a Bootstrap row for grouping inputs --}}
                                <div class="col-md-4"> {{-- Each input will take 4 columns (12/3 = 4) --}}
                                    <div class="form-group"> {{-- Removed form-inline, it's not ideal with col grid --}}
                                        <label for="employee_id">كود الموظف</label>
                                        <input type="text" class="form-control" name="employee_id" id="employee_id" value="{{ old('employee_id') }}">
                                        @error('employee_id')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="finger_id">كود البصمة</label>
                                        <input type="text" class="form-control" name="finger_id" id="finger_id" value="{{ old('finger_id') }}">
                                        @error('finger_id')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="employee_name">اسم الموظف</label>
                                        <input type="text" class="form-control" name="employee_name" id="employee_name" value="{{ old('employee_name') }}">
                                        @error('employee_name')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div> {{-- End of row --}}

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="employee_address">عنوان الموظف</label>
                                        <input type="text" class="form-control" name="employee_address" id="employee_address" value="{{ old('employee_address') }}">
                                        @error('employee_address')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="national_id">الرقم القومي</label>
                                        <input type="text" class="form-control" name="national_id" id="national_id" value="{{ old('national_id') }}">
                                        @error('national_id')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="emp_mobile">موبيل</label>
                                        <input type="text" class="form-control" name="emp_mobile" id="emp_mobile" value="{{ old('emp_mobile') }}">
                                        @error('emp_mobile')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="emp_home_tel">تليفون المنزل</label>
                                        <input type="text" class="form-control" name="emp_home_tel" id="emp_home_tel" value="{{ old('emp_home_tel') }}">
                                        @error('emp_home_tel')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="emp_email">Email</label>
                                        <input type="email" class="form-control" name="emp_email" id="emp_email" value="{{ old('emp_email') }}">
                                        @error('emp_email')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="birth_date">تاريخ الميلاد</label>
                                        <input type="date" class="form-control" name="birth_date" id="birth_date" value="{{ old('birth_date') }}">
                                        @error('birth_date')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="emp_gender">نوع الجنس</label>
                                        <select class="form-control select2" name="emp_gender" id="emp_gender">
                                            <option value="">اختر النوع</option>
                                            <option value="1" @if (old('emp_gender')==1)selected @endif>ذكر</option>
                                            <option value="2" @if (old('emp_gender')==2)selected @endif>انثى</option>
                                        </select>
                                        @error('emp_gender')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="emp_social_status">الحالة الاجتماعية</label>
                                        <select class="form-control select2" name="emp_social_status" id="emp_social_status">
                                            <option value="">اختر الحالة</option>
                                            <option value="1" @if (old('emp_social_status')==1)selected @endif>اعزب</option>
                                            <option value="2" @if (old('emp_social_status')==2)selected @endif>متزوج</option>
                                            <option value="3" @if (old('emp_social_status')==3)selected @endif>متزوج ويعول</option>
                                        </select>
                                        @error('emp_social_status')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div> {{-- End of row --}}
                        </div>

                        {{-- بيانات الوظيفة --}}
                        <div class="tab-pane fade" id="custom-content-below-job_data" role="tabpanel" aria-labelledby="custom-content-below-job_data-tab">
                            <br>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="emp_start_date">تاريخ الالتحاق</label>
                                        <input type="date" class="form-control" name="emp_start_date" id="emp_start_date" value="{{ old('emp_start_date') }}">
                                        @error('emp_start_date')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="functional_status">حالة الموظف</label>
                                        <select class="form-control select2" name="functional_status" id="functional_status">
                                            <option value="1" @if (old('functional_status')==1)selected @endif>يعمل</option>
                                            <option value="2" @if (old('functional_status')==2)selected @endif>لا يعمل</option>
                                        </select>
                                        @error('functional_status')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="emp_jobs_id">الوظيفة</label>
                                        <select name="emp_jobs_id" id="emp_jobs_id" class="form-control select2">
                                            <option value="">اختر الوظيفة</option>
                                            @foreach($jobs_categories as $job)
                                                <option value="{{ $job->id }}" {{ old('emp_jobs_id') == $job->id ? 'selected' : '' }}>
                                                    {{ $job->job_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('emp_jobs_id')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="emp_departments_id">الادارة</label>
                                        <select name="emp_departments_id" id="emp_departments_id" class="form-control select2">
                                            <option value="">اختر الادارة</option>
                                            @foreach($departments as $department)
                                                <option value="{{ $department->id }}" {{ old('emp_departments_id') == $department->id ? 'selected' : '' }}>
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
                                    <div class="form-group">
                                        <label for="shifts_types_id">الشيفت</label>
                                        <select name="shifts_types_id" id="shifts_types_id" class="form-control select2">
                                            <option value="">اختر الشيفت</option>
                                            @foreach($shifts_types as $shifts_type)
                                                <option value="{{ $shifts_type->id }}" {{ old('shifts_types_id') == $shifts_type->id ? 'selected' : '' }}>
                                                    {{ $shifts_type->type}}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('shifts_types_id')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="branches_id">الفرع</label>
                                        <select name="branches_id" id="branches_id" class="form-control select2">
                                            <option value="">اختر الفرع</option>
                                            @foreach($branches as $branche)
                                                <option value="{{ $branche->id }}" {{ old('branches_id') == $branche->id ? 'selected' : '' }}>
                                                    {{ $branche->branch_name}}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('branches_id')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="daily_work_hours">عدد ساعات العمل</label>
                                        <input type="number" class="form-control" name="daily_work_hours" id="daily_work_hours" value="{{ old('daily_work_hours') }}">
                                        @error('daily_work_hours')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="resignation_status">حالة ترك العمل</label>
                                        <select class="form-control select2" name="resignation_status" id="resignation_status">
                                            <option value="">اختر الحالة</option>
                                            <option value="1" @if (old('resignation_status')==1)selected @endif>استقالة</option>
                                            <option value="2" @if (old('resignation_status')==2)selected @endif>فصل</option>
                                            <option value="3" @if (old('resignation_status')==3)selected @endif>ترك العمل</option>
                                            <option value="4" @if (old('resignation_status')==4)selected @endif>سن المعاش</option>
                                            <option value="5" @if (old('resignation_status')==5)selected @endif>الوفاة</option>
                                        </select>
                                        @error('resignation_status')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="resignation_date">تاريخ ترك العمل</label>
                                        <input type="date" class="form-control" name="resignation_date" id="resignation_date" value="{{ old('resignation_date') }}">
                                        @error('resignation_date')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="resignation_cause">سبب ترك العمل</label>
                                        <input type="text" class="form-control" name="resignation_cause" id="resignation_cause" value="{{ old('resignation_cause') }}">
                                        @error('resignation_cause')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- بيانات أخرى --}}
                        <div class="tab-pane fade" id="custom-content-below-other_data" role="tabpanel" aria-labelledby="custom-content-below-other_data-tab">
                            <br>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="emp_military_status">الخدمة العسكرية</label>
                                        <select class="form-control select2" name="emp_military_status" id="emp_military_status">
                                            <option value="">اختر الحالة</option>
                                            <option value="1" @if (old('emp_military_status')==1)selected @endif>أدى الخدمة</option>
                                            <option value="2" @if (old('emp_military_status')==2)selected @endif>إعفاء</option>
                                            <option value="3" @if (old('emp_military_status')==3)selected @endif>مؤجل</option>
                                        </select>
                                        @error('emp_military_status')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="emp_qualification">المؤهل الدراسي</label>
                                        <input type="text" class="form-control" name="emp_qualification" id="emp_qualification" value="{{ old('emp_qualification') }}">
                                        @error('emp_qualification')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="qualification_year">سنة المؤهل</label>
                                        <input type="text" class="form-control" name="qualification_year" id="qualification_year" value="{{ old('qualification_year') }}">
                                        @error('qualification_year')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="qualification_grade">تقدير المؤهل</label>
                                        <select class="form-control select2" name="qualification_grade" id="qualification_grade">
                                            <option value="">اختر التقدير</option>
                                            <option value="1" @if (old('qualification_grade')==1)selected @endif>امتياز</option>
                                            <option value="2" @if (old('qualification_grade')==2)selected @endif>جيد جداً</option>
                                            <option value="3" @if (old('qualification_grade')==3)selected @endif>جيد مرتفع</option>
                                            <option value="4" @if (old('qualification_grade')==4)selected @endif>جيد</option>
                                            <option value="5" @if (old('qualification_grade')==5)selected @endif>مقبول</option>
                                        </select>
                                        @error('qualification_grade')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- بيانات الراتب --}}
                        <div class="tab-pane fade" id="custom-content-below-Salary_data" role="tabpanel" aria-labelledby="custom-content-below-Salary_data-tab">
                            <br>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="emp_sal">الراتب الاساسي</label>
                                        <input type="number" class="form-control" name="emp_sal" id="emp_sal" value="{{ old('emp_sal') }}">
                                        @error('emp_sal')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="emp_sal_insurance">الراتب التأميني</label>
                                        <input type="number" class="form-control" name="emp_sal_insurance" id="emp_sal_insurance" value="{{ old('emp_sal_insurance') }}">
                                        @error('emp_sal_insurance')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="emp_fixed_allowances">علاوة ثابتة</label>
                                        <input type="number" class="form-control" name="emp_fixed_allowances" id="emp_fixed_allowances" value="{{ old('emp_fixed_allowances') }}">
                                        @error('emp_fixed_allowances')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="motivation">الحافز</label>
                                        <input type="number" class="form-control" name="motivation" id="motivation" value="{{ old('motivation') }}">
                                        @error('motivation')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="medical_insurance">التأمين الصحي الخاص</label>
                                        <input type="number" class="form-control" name="medical_insurance" id="medical_insurance" value="{{ old('medical_insurance') }}">
                                        @error('medical_insurance')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="sal_cash_visa">طريقة الدفع</label>
                                        <select class="form-control select2" name="sal_cash_visa" id="sal_cash_visa">
                                            <option value="">اختر طريقة الدفع</option>
                                            <option value="1" @if (old('sal_cash_visa')==1)selected @endif>كاش</option>
                                            <option value="2" @if (old('sal_cash_visa')==2)selected @endif>فيزا</option>
                                        </select>
                                        @error('sal_cash_visa')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="bank_name">اسم البنك</label>
                                        <input type="text" class="form-control" name="bank_name" id="bank_name" value="{{ old('bank_name') }}">
                                        @error('bank_name')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="bank_account">رقم الحساب البنكي</label>
                                        <input type="text" class="form-control" name="bank_account" id="bank_account" value="{{ old('bank_account') }}">
                                        @error('bank_account')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="bank_ID">كود البنك (SWIFT/IBAN)</label>
                                        <input type="text" class="form-control" name="bank_ID" id="bank_ID" value="{{ old('bank_ID') }}">
                                        @error('bank_ID')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="bank_branch">فرع البنك</label>
                                        <input type="text" class="form-control" name="bank_branch" id="bank_branch" value="{{ old('bank_branch') }}">
                                        @error('bank_branch')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary btn-lg col-2">اضافة</button>
                        <a class="btn btn-warning btn-lg col-2" href="{{ route('employees.index') }}">الغاء</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section("script")
    {{-- تأكد أن jQuery و Bootstrap JS يتم تحميلهما قبل Select2 --}}
    {{-- بما أن jQuery و Bootstrap JS يتم تحميلهما في الـ Layout الرئيسي، فلا حاجة لتحميلهما هنا مرة أخرى --}}
    {{-- <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script> --}}
    {{-- <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script> --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // تهيئة جميع عناصر select التي تحتوي على الكلاس 'select2'
            // باستخدام ثيم Bootstrap 4 لضمان الدمج الصحيح
            $('.select2').select2({
                theme: 'bootstrap4' // هذا السطر يخبر Select2 باستخدام ثيم Bootstrap 4
            });
        });
    </script>
@endsection