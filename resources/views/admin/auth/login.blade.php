{{-- ============================================================ --}}
{{-- FILE: resources/views/admin/auth/login.blade.php           --}}
{{-- ============================================================ --}}
@extends('admin.layouts.app')
@section('title') تسجيل الدخول @endsection

@section('content')
<div class="hold-transition login-page" style="background:linear-gradient(135deg,#1a1a2e 0%,#16213e 50%,#0f3460 100%);min-height:100vh;display:flex;align-items:center">
  <div class="login-box" style="width:420px;margin:auto">

    {{-- Logo / Header --}}
    <div class="login-logo text-center mb-4">
      <div style="background:rgba(255,255,255,.1);border-radius:16px;padding:20px 30px;display:inline-block">
        <i class="fas fa-building fa-3x text-white mb-2 d-block"></i>
        <h3 class="text-white mb-0" style="font-weight:700;letter-spacing:2px">LOGG HR</h3>
        <small class="text-white-50">نظام إدارة الموارد البشرية</small>
      </div>
    </div>

    <div class="card" style="border-radius:16px;border:none;box-shadow:0 20px 60px rgba(0,0,0,.4)">
      <div class="card-body p-4">
        <h4 class="text-center mb-4" style="color:#0f3460;font-weight:700">
          <i class="fas fa-sign-in-alt ml-2 text-primary"></i>تسجيل الدخول
        </h4>

        @if(session('success'))
          <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
          </div>
        @endif
        @if(session('errorrLogin'))
          <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle ml-1"></i>
            بيانات الدخول غير صحيحة
          </div>
        @endif

        <form method="POST" action="{{ route('admin.dashboard.home') }}">
          @csrf
          <div class="form-group">
            <label class="font-weight-bold">البريد الإلكتروني</label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
              </div>
              <input type="email" name="email" class="form-control form-control-lg @error('email') is-invalid @enderror"
                value="{{ old('email') }}" placeholder="admin@company.com" required autofocus>
              @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>

          <div class="form-group">
            <label class="font-weight-bold">كلمة المرور</label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
              </div>
              <input type="password" name="password" class="form-control form-control-lg"
                placeholder="••••••••" required>
            </div>
          </div>

          <div class="form-check mb-3">
            <input type="checkbox" class="form-check-input" name="remember" id="remember">
            <label class="form-check-label" for="remember">تذكرني</label>
          </div>

          <button type="submit" class="btn btn-primary btn-block btn-lg" style="border-radius:8px;font-weight:700">
            <i class="fas fa-sign-in-alt ml-2"></i>دخول
          </button>
        </form>

        <hr class="my-3">
        <div class="text-center">
          <small class="text-muted">ليس لديك حساب؟</small>
          <a href="{{ route('admin.dashboard.register') }}" class="btn btn-sm btn-outline-success mr-2">
            <i class="fas fa-user-plus ml-1"></i>إنشاء حساب جديد
          </a>
        </div>

        <div class="text-center mt-2">
          <small class="text-muted">هل أنت موظف؟</small>
          <a href="{{ route('employee.login') }}" class="btn btn-sm btn-outline-info mr-2">
            <i class="fas fa-user ml-1"></i>دخول الموظفين
          </a>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection
