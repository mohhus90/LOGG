<div class="sidebar">

  {{-- Module Header --}}
  <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center"
       style="border-bottom:1px solid rgba(255,255,255,.1)">
    <div class="info w-100">
      <div class="d-flex align-items-center justify-content-between">
        <span class="text-white font-weight-bold" style="font-size:.95rem">
          <i class="fas fa-certificate ml-1" style="color:#22d3ee"></i>
          موديول ضبط الجودة
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

      <li class="nav-item">
        <a href="{{ route('quality_reports.index') }}"
           class="nav-link {{ request()->routeIs('quality_reports.index') ? 'active' : '' }}">
          <i class="nav-icon fas fa-tachometer-alt"></i>
          <p>لوحة الجودة</p>
        </a>
      </li>

      <li class="nav-item">
        <a href="{{ route('quality_checklists.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/quality/checklists*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-clipboard-list"></i>
          <p>قوالب الفحص</p>
        </a>
      </li>

      <li class="nav-item">
        <a href="{{ route('quality_inspections.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/quality/inspections*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-search"></i>
          <p>فحوصات الجودة</p>
        </a>
      </li>

    </ul>
  </nav>
</div>
