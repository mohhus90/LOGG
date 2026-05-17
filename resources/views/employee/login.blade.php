{{-- FILE: resources/views/employee/login.blade.php --}}
@extends('admin.layouts.app')
@section('title') دخول الموظفين @endsection

@section('content')
<div style="background:linear-gradient(135deg,#11998e,#38ef7d);min-height:100vh;display:flex;align-items:center">
  <div style="width:420px;margin:auto;padding:20px">

    <div class="text-center mb-4">
      <div style="background:rgba(255,255,255,.2);border-radius:16px;padding:20px;display:inline-block">
        <i class="fas fa-user-tie fa-3x text-white mb-2 d-block"></i>
        <h4 class="text-white mb-0">بوابة الموظفين</h4>
        <small class="text-white-50">LOGG HR Employee Portal</small>
      </div>
    </div>

    <div class="card" style="border-radius:16px;border:none;box-shadow:0 20px 60px rgba(0,0,0,.3)">
      <div class="card-body p-4">
        <h5 class="text-center mb-4" style="color:#11998e;font-weight:700">
          <i class="fas fa-sign-in-alt ml-2"></i>تسجيل دخول الموظف
        </h5>

        @if(session('error'))
          <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if(session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('employee.login.check') }}">
          @csrf
          <div class="form-group">
            <label>كود الموظف</label>
            <div class="input-group">
              <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-id-badge"></i></span></div>
              <input type="text" name="employee_code" class="form-control form-control-lg"
                placeholder="EMP001" required value="{{ old('employee_code') }}">
            </div>
          </div>
          <div class="form-group">
            <label>كلمة المرور</label>
            <div class="input-group">
              <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-lock"></i></span></div>
              <input type="password" name="password" class="form-control form-control-lg"
                placeholder="••••••" required>
            </div>
            <small class="text-muted">كلمة المرور الافتراضية هي رقم الهاتف</small>
          </div>
          <button type="submit" class="btn btn-block btn-lg text-white" style="background:#11998e;border-radius:8px;font-weight:700">
            <i class="fas fa-sign-in-alt ml-2"></i>دخول
          </button>
        </form>

        <div class="text-center mt-3">
          <a href="{{ route('admin.dashboard.login') }}" class="text-muted">
            <i class="fas fa-arrow-right ml-1"></i>عودة لدخول الإدارة
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
