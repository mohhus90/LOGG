{{-- FILE: resources/views/admin/auth/register.blade.php --}}
@extends('admin.layouts.app')
@section('title') إنشاء حساب جديد @endsection

@section('content')
<div class="hold-transition" style="background:linear-gradient(135deg,#1a1a2e,#0f3460);min-height:100vh;display:flex;align-items:center;padding:20px 0">
  <div style="width:580px;margin:auto;padding:0 15px">

    <div class="text-center mb-4">
      <i class="fas fa-building fa-2x text-white mb-2 d-block"></i>
      <h3 class="text-white mb-0" style="font-weight:700">LOGG HR</h3>
      <small class="text-white-50">إنشاء حساب جديد</small>
    </div>

    <div class="card" style="border-radius:16px;border:none;box-shadow:0 20px 60px rgba(0,0,0,.4)">
      <div class="card-body p-4">
        <h5 class="text-center mb-4" style="color:#0f3460;font-weight:700">
          <i class="fas fa-user-plus ml-2 text-primary"></i>تسجيل مستخدم جديد
        </h5>

        @if(session('error'))
          <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('error') }}
          </div>
        @endif
        @if($errors->any())
          <div class="alert alert-danger">
            <ul class="mb-0">
              @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
          </div>
        @endif

        <form method="POST" action="{{ route('admin.dashboard.store') }}">
          @csrf

          {{-- بيانات المستخدم --}}
          <div class="row">
            <div class="col-md-6 form-group">
              <label class="font-weight-bold">الاسم الكامل <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                required value="{{ old('name') }}" placeholder="اسمك الكامل">
              @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 form-group">
              <label class="font-weight-bold">البريد الإلكتروني <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                required value="{{ old('email') }}" placeholder="email@company.com">
              @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 form-group">
              <label class="font-weight-bold">كلمة المرور <span class="text-danger">*</span></label>
              <input type="password" name="password"
                class="form-control @error('password') is-invalid @enderror"
                required placeholder="••••••••" minlength="6">
              @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 form-group">
              <label class="font-weight-bold">تأكيد كلمة المرور <span class="text-danger">*</span></label>
              <input type="password" name="password_confirmation" class="form-control"
                required placeholder="••••••••">
            </div>
          </div>

          <hr>

          {{-- اختيار الشركة --}}
          <h6 class="font-weight-bold mb-3 text-primary">
            <i class="fas fa-building ml-1"></i>الشركة
          </h6>

          <div class="form-group">
            <label class="d-block mb-2">هل الشركة مسجلة في النظام؟</label>
            <div class="d-flex">
              {{-- ✅ FIX: استخدام onclick مباشرة بدلاً من data-toggle --}}
              <button type="button"
                id="btnExisting"
                onclick="selectMode('existing')"
                class="btn btn-outline-primary ml-2"
                style="border-radius:8px;min-width:160px">
                <i class="fas fa-search ml-1"></i>اختر شركة موجودة
              </button>
              <button type="button"
                id="btnNew"
                onclick="selectMode('new')"
                class="btn btn-outline-success"
                style="border-radius:8px;min-width:160px">
                <i class="fas fa-plus ml-1"></i>إنشاء شركة جديدة
              </button>
            </div>
            {{-- ✅ FIX: hidden input يحمل القيمة الفعلية --}}
            <input type="hidden" name="company_mode" id="companyModeInput" value="{{ old('company_mode', 'existing') }}">
          </div>

          {{-- قسم الشركة الموجودة --}}
          <div id="existingSection" style="display:none">
            <div class="form-group">
              <label>اختر الشركة <span class="text-danger">*</span></label>
              <select name="company_id" class="form-control @error('company_id') is-invalid @enderror"
                id="companySelect">
                <option value="">-- اختر الشركة --</option>
                @forelse($companies as $co)
                  <option value="{{ $co->id }}"
                    {{ old('company_id') == $co->id ? 'selected' : '' }}>
                    {{ $co->name }}
                  </option>
                @empty
                  <option value="" disabled>لا توجد شركات مسجلة — أنشئ شركة جديدة</option>
                @endforelse
              </select>
              @error('company_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
              @if($companies->isEmpty())
                <div class="alert alert-warning mt-2 py-2">
                  <i class="fas fa-exclamation-triangle ml-1"></i>
                  لا توجد شركات مسجلة بعد. اختر "إنشاء شركة جديدة".
                </div>
              @endif
            </div>
          </div>

          {{-- قسم الشركة الجديدة --}}
          <div id="newSection" style="display:none">
            <div class="alert alert-info py-2 mb-3" style="font-size:.88em">
              <i class="fas fa-info-circle ml-1"></i>
              ستُنشأ الشركة تلقائياً وستكون أنت <strong>السوبر أدمن</strong> لها.
            </div>
            <div class="row">
              <div class="col-md-8 form-group">
                <label>اسم الشركة <span class="text-danger">*</span></label>
                <input type="text" name="new_company_name"
                  class="form-control @error('new_company_name') is-invalid @enderror"
                  value="{{ old('new_company_name') }}"
                  placeholder="مثال: شركة النجوم للتجارة">
                @error('new_company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-4 form-group">
                <label>هاتف الشركة</label>
                <input type="text" name="company_phone" class="form-control"
                  value="{{ old('company_phone') }}" placeholder="01xxxxxxxxx">
              </div>
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-block btn-lg mt-3"
            style="border-radius:8px;font-weight:700">
            <i class="fas fa-check ml-2"></i>إنشاء الحساب
          </button>
        </form>

        <div class="text-center mt-3">
          <small class="text-muted">لديك حساب؟</small>
          <a href="{{ route('admin.dashboard.login') }}" class="mr-1 font-weight-bold">
            تسجيل الدخول
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
/**
 * ✅ FIX: وضع داخل DOMContentLoaded لضمان جاهزية العناصر
 */
document.addEventListener('DOMContentLoaded', function () {

  // تطبيق الوضع المحفوظ (old value أو الافتراضي)
  var savedMode = '{{ old("company_mode", $companies->isEmpty() ? "new" : "existing") }}';
  selectMode(savedMode);

  // إذا الشركات فارغة، انتقل تلقائياً لوضع "new"
  @if($companies->isEmpty())
    selectMode('new');
  @endif
});

function selectMode(mode) {
  var existingSection = document.getElementById('existingSection');
  var newSection      = document.getElementById('newSection');
  var modeInput       = document.getElementById('companyModeInput');
  var btnExisting     = document.getElementById('btnExisting');
  var btnNew          = document.getElementById('btnNew');

  if (!existingSection || !newSection) return;

  // إظهار/إخفاء الأقسام
  if (mode === 'existing') {
    existingSection.style.display = '';
    newSection.style.display      = 'none';
    // تأكيد اختيار شركة
    var companySelect = document.getElementById('companySelect');
    if (companySelect) companySelect.setAttribute('required', 'required');
    // إلغاء required من حقل الشركة الجديدة
    var newName = document.querySelector('input[name="new_company_name"]');
    if (newName) newName.removeAttribute('required');
  } else {
    existingSection.style.display = 'none';
    newSection.style.display      = '';
    // إلغاء required من select
    var companySelect = document.getElementById('companySelect');
    if (companySelect) companySelect.removeAttribute('required');
    // تأكيد required على حقل اسم الشركة
    var newName = document.querySelector('input[name="new_company_name"]');
    if (newName) newName.setAttribute('required', 'required');
  }

  // تحديث الـ hidden input
  if (modeInput) modeInput.value = mode;

  // تحديث شكل الأزرار
  if (btnExisting && btnNew) {
    if (mode === 'existing') {
      btnExisting.className = 'btn btn-primary ml-2';
      btnNew.className      = 'btn btn-outline-success';
    } else {
      btnExisting.className = 'btn btn-outline-primary ml-2';
      btnNew.className      = 'btn btn-success';
    }
    btnExisting.style.borderRadius = '8px';
    btnExisting.style.minWidth     = '160px';
    btnNew.style.borderRadius      = '8px';
    btnNew.style.minWidth          = '160px';
  }
}
</script>
@endpush
