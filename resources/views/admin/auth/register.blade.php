{{-- FILE: resources/views/admin/auth/register.blade.php --}}
@extends('admin.layouts.app')
@section('title') إنشاء حساب جديد @endsection

@section('content')
<div class="hold-transition" style="background:linear-gradient(135deg,#1a1a2e,#0f3460);min-height:100vh;display:flex;align-items:center;padding:20px 0">
  <div style="width:560px;margin:auto">

    <div class="text-center mb-4">
      <i class="fas fa-building fa-2x text-white mb-2"></i>
      <h3 class="text-white mb-0">LOGG HR — إنشاء حساب</h3>
    </div>

    <div class="card" style="border-radius:16px;border:none;box-shadow:0 20px 60px rgba(0,0,0,.4)">
      <div class="card-body p-4">
        <h5 class="text-center mb-4" style="color:#0f3460;font-weight:700">
          <i class="fas fa-user-plus ml-2 text-primary"></i>تسجيل مستخدم جديد
        </h5>

        @if(session('error'))
          <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if($errors->any())
          <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
          </div>
        @endif

        <form method="POST" action="{{ route('admin.dashboard.store') }}">
          @csrf

          {{-- بيانات المستخدم --}}
          <div class="row">
            <div class="col-md-6 form-group">
              <label class="font-weight-bold">الاسم الكامل <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" required
                value="{{ old('name') }}" placeholder="اسمك الكامل">
            </div>
            <div class="col-md-6 form-group">
              <label class="font-weight-bold">البريد الإلكتروني <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control" required
                value="{{ old('email') }}" placeholder="email@company.com">
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 form-group">
              <label class="font-weight-bold">كلمة المرور <span class="text-danger">*</span></label>
              <input type="password" name="password" class="form-control" required placeholder="••••••••">
            </div>
            <div class="col-md-6 form-group">
              <label class="font-weight-bold">تأكيد كلمة المرور <span class="text-danger">*</span></label>
              <input type="password" name="password_confirmation" class="form-control" required placeholder="••••••••">
            </div>
          </div>

          <hr>

          {{-- اختيار الشركة --}}
          <h6 class="font-weight-bold mb-3 text-primary">
            <i class="fas fa-building ml-1"></i>الشركة
          </h6>

          <div class="form-group">
            <label>هل الشركة مسجلة في النظام؟</label>
            <div class="btn-group btn-group-toggle d-block" data-toggle="buttons">
              <label class="btn btn-outline-primary {{ old('company_mode','existing')=='existing'?'active':'' }}"
                     id="existingBtn" onclick="toggleCompanyMode('existing')">
                <input type="radio" name="company_mode" value="existing"
                  {{ old('company_mode','existing')=='existing'?'checked':'' }}>
                <i class="fas fa-search ml-1"></i>اختر شركة موجودة
              </label>
              <label class="btn btn-outline-success {{ old('company_mode')=='new'?'active':'' }} mr-2"
                     id="newBtn" onclick="toggleCompanyMode('new')">
                <input type="radio" name="company_mode" value="new"
                  {{ old('company_mode')=='new'?'checked':'' }}>
                <i class="fas fa-plus ml-1"></i>إنشاء شركة جديدة
              </label>
            </div>
          </div>

          {{-- اختيار شركة موجودة --}}
          <div id="existingSection" class="{{ old('company_mode')=='new'?'d-none':'' }}">
            <div class="form-group">
              <label>اختر الشركة <span class="text-danger">*</span></label>
              <select name="company_id" class="form-control" id="companySelect">
                <option value="">-- اختر الشركة --</option>
                @foreach($companies as $co)
                <option value="{{ $co->id }}" {{ old('company_id')==$co->id?'selected':'' }}>
                  {{ $co->name }}
                </option>
                @endforeach
              </select>
              @if($companies->isEmpty())
                <small class="text-muted">لا توجد شركات مسجلة بعد. أنشئ شركة جديدة.</small>
              @endif
            </div>
          </div>

          {{-- إنشاء شركة جديدة --}}
          <div id="newSection" class="{{ old('company_mode')!='new'?'d-none':'' }}">
            <div class="alert alert-info py-2">
              <i class="fas fa-info-circle ml-1"></i>
              ستُنشأ الشركة تلقائياً وستكون أنت <strong>السوبر أدمن</strong> لها مع صلاحية كاملة.
            </div>
            <div class="row">
              <div class="col-md-8 form-group">
                <label>اسم الشركة <span class="text-danger">*</span></label>
                <input type="text" name="new_company_name" class="form-control"
                  value="{{ old('new_company_name') }}" placeholder="مثال: شركة النجوم للتجارة">
              </div>
              <div class="col-md-4 form-group">
                <label>هاتف الشركة</label>
                <input type="text" name="company_phone" class="form-control"
                  value="{{ old('company_phone') }}" placeholder="01xxxxxxxxx">
              </div>
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-block btn-lg mt-3" style="border-radius:8px;font-weight:700">
            <i class="fas fa-check ml-2"></i>إنشاء الحساب
          </button>
        </form>

        <div class="text-center mt-3">
          <small class="text-muted">لديك حساب؟</small>
          <a href="{{ route('admin.dashboard.login') }}" class="mr-1">تسجيل الدخول</a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('js')
<script>
function toggleCompanyMode(mode) {
  document.getElementById('existingSection').classList.toggle('d-none', mode !== 'existing');
  document.getElementById('newSection').classList.toggle('d-none', mode !== 'new');

  if (mode === 'existing') {
    document.getElementById('existingBtn').classList.add('active');
    document.getElementById('newBtn').classList.remove('active');
  } else {
    document.getElementById('newBtn').classList.add('active');
    document.getElementById('existingBtn').classList.remove('active');
  }
}

// تفعيل عند التحميل
toggleCompanyMode('{{ old("company_mode","existing") }}');
</script>
@endsection
