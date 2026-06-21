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

    {{-- Language Toggle --}}
    <li class="nav-item">
      @if(app()->getLocale() === 'ar')
        <a class="nav-link" href="{{ route('lang.switch', 'en') }}" title="Switch to English">
          <i class="fas fa-globe mr-1"></i><span style="font-size:.85em;font-weight:600">EN</span>
        </a>
      @else
        <a class="nav-link" href="{{ route('lang.switch', 'ar') }}" title="التبديل إلى العربية">
          <i class="fas fa-globe ml-1"></i><span style="font-size:.85em;font-weight:600">ع</span>
        </a>
      @endif
    </li>

    {{-- Username + Logout --}}
    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" href="#"
         data-toggle="dropdown" data-display="static" role="button"
         aria-haspopup="true" aria-expanded="false">
        <i class="fas fa-user-circle ml-1"></i>
        {{ Auth::guard('admin')->user()->name }}
        @if(Auth::guard('admin')->user()->is_super_admin)
          <span class="badge badge-warning badge-sm mr-1" style="font-size:.7em">{{ __('admin.super_badge') }}</span>
        @endif
      </a>
      <div class="dropdown-menu dropdown-menu-right">
        <a class="dropdown-item" href="{{ route('generalsetting.index') }}">
          <i class="fas fa-cog ml-2"></i>{{ __('admin.general_settings') }}
        </a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item text-danger" href="#"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
          <i class="fas fa-sign-out-alt ml-2"></i>{{ __('admin.logout') }}
        </a>
        <form id="logout-form" action="{{ route('admin.dashboard.logout') }}" method="POST" style="display:none">
          @csrf
        </form>
      </div>
    </li>

    {{-- Notifications --}}
    <li class="nav-item">
      <a class="nav-link" data-toggle="dropdown" href="#">
        <i class="far fa-bell"></i>
      </a>
      <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        <span class="dropdown-header">{{ __('admin.notifications') }}</span>
        <div class="dropdown-divider"></div>
        <a href="#" class="dropdown-item dropdown-footer">{{ __('admin.view_all') }}</a>
      </div>
    </li>

    <li class="nav-item">
      <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#">
        <i class="fas fa-th-large"></i>
      </a>
    </li>
  </ul>
</nav>
