<div class="sidebar">

  {{-- Module Header --}}
  <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center"
       style="border-bottom:1px solid rgba(255,255,255,.1)">
    <div class="info w-100">
      <div class="d-flex align-items-center justify-content-between">
        <span class="text-white font-weight-bold" style="font-size:.95rem">
          <i class="fas fa-building ml-1" style="color:#94a3b8"></i>
          موديول النظام
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

      {{-- بيانات شركتي --}}
      <li class="nav-item">
        <a href="{{ route('company_profile.edit') }}"
           class="nav-link {{ request()->routeIs('company_profile.*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-building"></i>
          <p>بيانات شركتي</p>
        </a>
      </li>

      @if(Auth::guard('admin')->user()->is_super_admin)
      {{-- سجل الشركات (سوبر أدمن فقط) --}}
      <li class="nav-item">
        <a href="{{ route('companies.index') }}"
           class="nav-link {{ request()->routeIs('companies.*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-city"></i>
          <p>سجل الشركات</p>
        </a>
      </li>
      @endif

    </ul>
  </nav>
</div>
