<div class="sidebar">

  {{-- Module Header --}}
  <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center"
       style="border-bottom:1px solid rgba(255,255,255,.1)">
    <div class="info w-100">
      <div class="d-flex align-items-center justify-content-between">
        <span class="text-white font-weight-bold" style="font-size:.95rem">
          <i class="fas fa-handshake ml-1" style="color:#2dd4bf"></i>
          موديول إدارة علاقات العملاء
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
        <a href="{{ route('crm_leads.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/crm/leads*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-user-plus"></i>
          <p>العملاء المحتملون</p>
        </a>
      </li>

      <li class="nav-item">
        <a href="{{ route('crm_opportunities.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/crm/opportunities*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-bullseye"></i>
          <p>الفرص البيعية</p>
        </a>
      </li>

    </ul>
  </nav>
</div>
