@extends('admin.layouts.admin')
@section('title'){{ __('admin.emp_title') }}@endsection
@section('start'){{ __('admin.general_settings') }}@endsection
@section('home')
<a href="{{ route('employees.index') }}">{{ __('admin.emp_title') }}</a>
@endsection
@section('startpage'){{ __('admin.view') }}@endsection

@section('content')
<div class="col-12">

  {{-- ── رسائل النجاح / الخطأ ── --}}
  @if(session('success'))
    <div class="alert alert-success alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
    </div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      {{ session('error') }}
    </div>
  @endif

  {{-- ── تصدير CSV بنظام خارجي ── --}}
  <div class="card mb-4">
    <div class="card-header bg-secondary">
      <h3 class="card-title text-white"><i class="fas fa-file-download mr-2"></i>تصدير بيانات الموظفين (System CSV)</h3>
    </div>
    <div class="card-body">
      <p class="text-muted mb-3">
        يُصدر ملف CSV بنفس تنسيق النظام الخارجي (32 عمود) — الحقول الفارغة تُملأ تلقائياً بـ <code>Null</code> أو الأصفار حسب نوع الحقل.
      </p>
      <a href="{{ route('employees.export.system.csv') }}" class="btn btn-secondary">
        <i class="fas fa-file-csv mr-1"></i>تنزيل System CSV
      </a>
    </div>
  </div>

  {{-- ── استيراد موظفين جدد ── --}}
  <div class="card mb-4">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-file-excel mr-2"></i>{{ __('admin.emp_upload_excel_title') }}</h3>
    </div>
    <div class="card-body">
      <form enctype="multipart/form-data" action="{{ route('employees.douploadexcel') }}" method="post">
        @csrf
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>{{ __('admin.emp_choose_excel') }}</label>
              <input type="file" name="excel_file" id="excel_file" class="form-control" accept=".xlsx,.xls">
            </div>
            @error('excel_file')<div class="text-danger">{{ $message }}</div>@enderror
          </div>
        </div>
        <div class="form-group">
          <button class="btn btn-success" type="submit"><i class="fas fa-upload mr-1"></i>{{ __('admin.emp_attach_file') }}</button>
          <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary mr-2"><i class="fas fa-arrow-right mr-1"></i>{{ __('admin.cancel') }}</a>
        </div>
      </form>
    </div>
  </div>

  {{-- ── استيراد / تحديث بيانات الموظفين ── --}}
  <div class="card mb-4">
    <div class="card-header bg-info">
      <h3 class="card-title text-white"><i class="fas fa-file-upload mr-2"></i>استيراد / تحديث بيانات الموظفين</h3>
    </div>
    <div class="card-body">

      <div class="alert alert-info">
        <i class="fas fa-info-circle mr-1"></i>
        <strong>يدعم صيغتين تلقائياً:</strong>
        <ul class="mb-0 mt-2">
          <li>
            <strong>Client CSV</strong> — ملف العميل (31 عمود يبدأ بـ SR):
            <code>SR, Fake ID, HRID, English Name, Arabic Name, Position, Mobile, Reference Number, Relative, Address, NID, Gender, Date Of Birth, Age, Marital Status, Status, Hiring Date, Resignation Date, Hiring Documents, Military Certificate, Social Number, Start Date Of Social, Form 1 Comments, End Date Of Social, Form 6 Comments, Comments, Medical ID, , Status(medical), Progress, Salary, Social Insurance salary</code>
            — المطابقة عبر <strong>NID → Fake ID → HRID</strong>
          </li>
          <li class="mt-2">
            <strong>System CSV</strong> — ملف النظام (32 عمود يبدأ بالاسم):
            <code>Name, namear, Mobile, emergency contact, emergency mobile, Email, National Id, Medical Id, Social Number, social status, Job, Salary, Date Of Birth, Gender, Marital Status, Military Status, Company, Education, Cv, Extra Comments, Username, Password, Status, Photo, Bank, Payment Channel, Card No, Vacation Type, hire date, hrid, serial number, Education Filed</code>
            — المطابقة عبر <strong>National Id → Username</strong>
          </li>
          <li class="mt-2">يتم <strong>تحديث</strong> الموظفين الحاليين فقط — لا يُنشأ موظف جديد</li>
          <li>يحافظ على ربط الموظف بعميله دون تغيير</li>
          <li>يقبل ملفات xlsx أو xls أو csv</li>
        </ul>
      </div>

      <form enctype="multipart/form-data" action="{{ route('employees.do.upload.medical') }}" method="post">
        @csrf
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>اختر ملف System CSV / Excel</label>
              <input type="file" name="medical_file" class="form-control" accept=".xlsx,.xls,.csv">
            </div>
            @error('medical_file')<div class="text-danger">{{ $message }}</div>@enderror
          </div>
        </div>
        <div class="form-group">
          <button class="btn btn-info text-white" type="submit">
            <i class="fas fa-upload mr-1"></i>تحديث بيانات الموظفين
          </button>
        </div>
      </form>

    </div>
  </div>

  {{-- ── تحديث الأرقام القومية ── --}}
  <div class="card">
    <div class="card-header bg-warning">
      <h3 class="card-title text-dark"><i class="fas fa-id-card mr-2"></i>تحديث الأرقام القومية (NID) للموظفين الحاليين</h3>
    </div>
    <div class="card-body">

      <div class="alert alert-info">
        <i class="fas fa-info-circle mr-1"></i>
        <strong>طريقة عمل الملف:</strong>
        يجب أن يحتوي ملف Excel/CSV على <strong>صف عناوين في السطر الأول</strong> ثم البيانات من السطر الثاني:
        <ul class="mb-0 mt-1">
          <li>العمود الأول (A): <strong>كود الموظف</strong> (نفس الكود المسجل في النظام)</li>
          <li>العمود الثاني (B): <strong>الرقم القومي (NID)</strong></li>
        </ul>
        سيتم مطابقة كود الموظف وتحديث الرقم القومي تلقائياً.
      </div>

      <form enctype="multipart/form-data" action="{{ route('employees.update.nid.excel') }}" method="post">
        @csrf
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>اختر ملف Excel أو CSV</label>
              <input type="file" name="nid_file" class="form-control" accept=".xlsx,.xls,.csv">
            </div>
            @error('nid_file')<div class="text-danger">{{ $message }}</div>@enderror
          </div>
        </div>
        <div class="form-group">
          <button class="btn btn-warning text-dark" type="submit">
            <i class="fas fa-sync-alt mr-1"></i>تحديث الأرقام القومية
          </button>
        </div>
      </form>

      <div class="mt-3">
        <small class="text-muted">
          <i class="fas fa-download mr-1"></i>
          مثال على تنسيق الملف:
        </small>
        <table class="table table-sm table-bordered mt-1" style="max-width:300px;font-size:.85rem">
          <thead class="thead-light">
            <tr><th>كود الموظف</th><th>NID</th></tr>
          </thead>
          <tbody>
            <tr><td>EMP001</td><td>29012345678901</td></tr>
            <tr><td>EMP002</td><td>30101020304050</td></tr>
          </tbody>
        </table>
      </div>

    </div>
  </div>

  {{-- ── تحديث بيانات الدخول (اسم مستخدم / كلمة مرور) ── --}}
  <div class="card mt-4">
    <div class="card-header bg-primary">
      <h3 class="card-title text-white"><i class="fas fa-key mr-2"></i>تحديث بيانات الدخول (اسم مستخدم / كلمة مرور)</h3>
    </div>
    <div class="card-body">

      <div class="alert alert-info">
        <i class="fas fa-info-circle mr-1"></i>
        استخدم ملف تصدير بيانات الموظفين (<a href="{{ route('employees.export') }}">Employee.xlsx</a>) بعد تعديل عمودي
        <strong>Username</strong> و<strong>Password</strong> في نهايته، ثم أعد رفعه هنا — سيتم تحديث
        <strong>اسم المستخدم وكلمة المرور فقط</strong> بناءً على كود الموظف (العمود الثاني)، دون أي تأثير على باقي البيانات.
      </div>

      <form enctype="multipart/form-data" action="{{ route('employees.import.credentials') }}" method="post">
        @csrf
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>اختر ملف Excel أو CSV</label>
              <input type="file" name="credentials_file" class="form-control" accept=".xlsx,.xls,.csv">
            </div>
            @error('credentials_file')<div class="text-danger">{{ $message }}</div>@enderror
          </div>
        </div>
        <div class="form-group">
          <button class="btn btn-primary" type="submit">
            <i class="fas fa-upload mr-1"></i>تحديث بيانات الدخول
          </button>
        </div>
      </form>

    </div>
  </div>

</div>
@endsection
