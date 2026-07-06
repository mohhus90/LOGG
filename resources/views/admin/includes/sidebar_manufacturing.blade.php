<div class="sidebar">

  {{-- Module Header --}}
  <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center"
       style="border-bottom:1px solid rgba(255,255,255,.1)">
    <div class="info w-100">
      <div class="d-flex align-items-center justify-content-between">
        <span class="text-white font-weight-bold" style="font-size:.95rem">
          <i class="fas fa-industry ml-1" style="color:#f87171"></i>
          موديول الإنتاج
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
        <a href="{{ route('manufacturing_reports.index') }}"
           class="nav-link {{ request()->routeIs('manufacturing_reports.index') ? 'active' : '' }}">
          <i class="nav-icon fas fa-tachometer-alt"></i>
          <p>لوحة الإنتاج</p>
        </a>
      </li>

      {{-- BOM --}}
      <li class="nav-item">
        <a href="{{ route('bill_of_materials.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/manufacturing/boms*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-sitemap"></i>
          <p>قوائم المواد (BOM)</p>
        </a>
      </li>

      {{-- Production Orders --}}
      <li class="nav-item">
        <a href="{{ route('production_orders.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/manufacturing/orders*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-industry"></i>
          <p>أوامر الإنتاج</p>
        </a>
      </li>

      {{-- Reports --}}
      <li class="nav-item">
        <a href="{{ route('manufacturing_reports.cost_summary') }}"
           class="nav-link {{ request()->routeIs('manufacturing_reports.cost_summary') ? 'active' : '' }}">
          <i class="nav-icon fas fa-chart-bar"></i>
          <p>ملخص تكاليف الإنتاج</p>
        </a>
      </li>

    </ul>
  </nav>
</div>
