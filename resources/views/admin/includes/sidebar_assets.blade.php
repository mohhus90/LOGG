<div class="sidebar">

  {{-- Module Header --}}
  <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center"
       style="border-bottom:1px solid rgba(255,255,255,.1)">
    <div class="info w-100">
      <div class="d-flex align-items-center justify-content-between">
        <span class="text-white font-weight-bold" style="font-size:.95rem">
          <i class="fas fa-building ml-1" style="color:#9ca3af"></i>
          موديول الأصول الثابتة
        </span>
        <a href="{{ route('admin.dashboard.home.page') }}"
           class="btn btn-xs" title="تبديل الموديول"
           style="background:rgba(255,255,255,.1);color:#aaa;font-size:.75rem;padding:3px 8px;border-radius:4px">
          <i class="fas fa-th-large"></i>
        </a>
      </div>
      <small class="text-muted d-block mt-1">{{ Auth::guard('admin')->user()->name }}</small>
    </div>
  </div>

  <nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column nav-sidebar-rtl"
        data-widget="treeview" role="menu" data-accordion="false">

      {{-- Dashboard --}}
      <li class="nav-item">
        <a href="{{ route('asset_reports.index') }}"
           class="nav-link {{ request()->routeIs('asset_reports.index') ? 'active' : '' }}">
          <i class="nav-icon fas fa-tachometer-alt"></i>
          <p>لوحة الأصول</p>
        </a>
      </li>

      {{-- Categories --}}
      <li class="nav-item">
        <a href="{{ route('asset_categories.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/assets/categories*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-tags"></i>
          <p>فئات الأصول</p>
        </a>
      </li>

      {{-- Assets --}}
      <li class="nav-item">
        <a href="{{ route('fixed_assets.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/assets/fixed-assets*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-building"></i>
          <p>سجل الأصول</p>
        </a>
      </li>

      {{-- Depreciation --}}
      <li class="nav-item">
        <a href="{{ route('asset_depreciation.form') }}"
           class="nav-link {{ request()->is('admin/dashboard/assets/depreciation*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-chart-line"></i>
          <p>تشغيل الإهلاك الشهري</p>
        </a>
      </li>

      {{-- Reports --}}
      <li class="nav-item">
        <a href="{{ route('asset_reports.register') }}"
           class="nav-link {{ request()->routeIs('asset_reports.register') ? 'active' : '' }}">
          <i class="nav-icon fas fa-file-alt"></i>
          <p>سجل الأصول التفصيلي</p>
        </a>
      </li>

    </ul>
  </nav>
</div>
