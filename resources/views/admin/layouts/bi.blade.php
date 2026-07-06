<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>@yield('title') — تحليلات NEXA</title>

  <link rel="stylesheet" href="{{ asset('assets/admin/plugins/fontawesome-free/css/all.min.css')}}">
  <link rel="stylesheet" href="{{ asset('assets/admin/dist/css/adminlte.min.css')}}">
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/admin/css/bootstrap_rtl-v4.2.1/bootstrap.min.css')}}">
  <link rel="stylesheet" href="{{ asset('assets/admin/css/bootstrap_rtl-v4.2.1/custom_rtl.css')}}">
  <link rel="stylesheet" href="{{ asset('assets/admin/css/mycustomstyle.css')}}">
  <link rel="stylesheet" href="{{ asset('assets/admin/css/logg-theme.css') }}">

  <style>
    /* BI module accent color — pink */
    .main-sidebar { background: #1a1f2e !important; }
    .main-sidebar .brand-link { border-bottom: 1px solid rgba(244,114,182,.2) !important; }
    .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.active,
    .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link:hover {
      background-color: rgba(244,114,182,.15) !important;
      color: #f472b6 !important;
    }
    .brand-link .brand-text { color: #f472b6 !important; }
    .module-tag {
      display: inline-block;
      background: rgba(244,114,182,.15);
      color: #f472b6;
      border: 1px solid rgba(244,114,182,.3);
      border-radius: 4px;
      font-size: .7rem;
      padding: 1px 6px;
      margin-right: 6px;
    }
  </style>

  @yield('css')
</head>
<body class="hold-transition sidebar-mini layout-rtl">
<div class="wrapper">

  {{-- Navbar --}}
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button">
          <i class="fas fa-bars"></i>
        </a>
      </li>
    </ul>
    <ul class="navbar-nav mr-auto">
      <li class="nav-item d-none d-sm-inline-block">
        <span class="navbar-text">
          <span class="module-tag">
            <i class="fas fa-chart-pie ml-1" style="font-size:.65rem"></i>التقارير والتحليلات
          </span>
          <small class="text-muted">@yield('start') / @yield('startpage')</small>
        </span>
      </li>
    </ul>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.dashboard.home.page') }}" title="تبديل الموديول">
          <i class="fas fa-th-large"></i>
          <span class="d-none d-md-inline mr-1" style="font-size:.8rem">الموديولات</span>
        </a>
      </li>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" role="button">
          <i class="fas fa-user-circle ml-1"></i>
          {{ Auth::guard('admin')->user()->name }}
        </a>
        <div class="dropdown-menu dropdown-menu-right">
          <a class="dropdown-item" href="{{ route('generalsetting.index') }}">
            <i class="fas fa-cog ml-2"></i> الإعدادات العامة
          </a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item text-danger" href="#"
             onclick="event.preventDefault(); document.getElementById('logout-form-bi').submit();">
            <i class="fas fa-sign-out-alt ml-2"></i> تسجيل الخروج
          </a>
          <form id="logout-form-bi" action="{{ route('admin.dashboard.logout') }}" method="POST" style="display:none">
            @csrf
          </form>
        </div>
      </li>
    </ul>
  </nav>

  {{-- Main Sidebar --}}
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ route('bi_dashboard.index') }}" class="brand-link">
      @php
        $__setting = \App\Models\Admin_panel_setting::where('com_code', (int)auth()->guard('admin')->user()->com_code)->first();
      @endphp
      @if($__setting && $__setting->image)
        <img src="{{ asset('storage/' . $__setting->image) }}"
             alt="Logo" class="brand-image elevation-3" style="opacity:.9;object-fit:contain;height:33px">
      @else
        <img src="{{ asset('/assets/admin/dist/img/AdminLTELogo.png') }}"
             alt="Logo" class="brand-image img-circle elevation-3" style="opacity:.8">
      @endif
      <span class="brand-text font-weight-bold" style="color:#f472b6">
        {{ $__setting->com_name ?? 'NEXA' }}
        <small style="color:#6b7280;font-size:.65rem;display:block;font-weight:400;margin-top:-3px">BI & Analytics</small>
      </span>
    </a>
    @include('admin.includes.sidebar_bi')
  </aside>

  {{-- Content Wrapper --}}
  @include('admin.includes.content')

  {{-- Footer --}}
  @include('admin.includes.footer')
</div>

<script src="{{ asset('assets/admin/plugins/jquery/jquery.min.js')}}"></script>
<script src="{{ asset('assets/admin/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
<script src="{{ asset('assets/admin/dist/js/adminlte.min.js')}}"></script>
<script src="{{ asset('assets/admin/js/general.js')}}"></script>
<script src="{{ asset('assets/admin/plugins/chart.js/Chart.min.js')}}"></script>
@yield('script')
</body>
</html>
