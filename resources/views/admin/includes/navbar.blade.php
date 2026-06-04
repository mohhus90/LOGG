<nav class="main-header navbar navbar-expand navbar-white navbar-light">
  <!-- Left navbar links -->
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button">
        <i class="fas fa-bars"></i>
      </a>
    </li>
  </ul>

  <!-- Right navbar links -->
  <ul class="navbar-nav ml-auto">

    {{-- ── اسم المستخدم + logout ── --}}
    <li class="nav-item dropdown">
      {{-- ✅ FIX: data-toggle (Bootstrap 4) بدلاً من data-bs-toggle (Bootstrap 5) --}}
      <a class="nav-link dropdown-toggle" href="#"
         data-toggle="dropdown" role="button"
         aria-haspopup="true" aria-expanded="false">
        <i class="fas fa-user-circle ml-1"></i>
        {{ Auth::guard('admin')->user()->name }}
        @if(Auth::guard('admin')->user()->is_super_admin)
          <span class="badge badge-warning badge-sm mr-1" style="font-size:.7em">سوبر</span>
        @endif
      </a>
      <div class="dropdown-menu dropdown-menu-right">
        <a class="dropdown-item" href="{{ route('generalsetting.index') }}">
          <i class="fas fa-cog ml-2"></i>الضبط العام
        </a>
        <div class="dropdown-divider"></div>
        {{-- ✅ FIX: POST logout بدلاً من GET --}}
        <a class="dropdown-item text-danger" href="#"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
          <i class="fas fa-sign-out-alt ml-2"></i>تسجيل الخروج
        </a>
        <form id="logout-form" action="{{ route('admin.dashboard.logout') }}" method="POST" style="display:none">
          @csrf
        </form>
      </div>
    </li>

    {{-- إشعارات --}}
    <li class="nav-item">
      <a class="nav-link" data-toggle="dropdown" href="#">
        <i class="far fa-bell"></i>
      </a>
      <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        <span class="dropdown-header">الإشعارات</span>
        <div class="dropdown-divider"></div>
        <a href="#" class="dropdown-item dropdown-footer">عرض الكل</a>
      </div>
    </li>

    <li class="nav-item">
      <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#">
        <i class="fas fa-th-large"></i>
      </a>
    </li>
  </ul>
</nav>
