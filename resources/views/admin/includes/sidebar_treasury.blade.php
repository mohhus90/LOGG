<div class="sidebar">

  {{-- Module Header --}}
  <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center"
       style="border-bottom:1px solid rgba(255,255,255,.1)">
    <div class="info w-100">
      <div class="d-flex align-items-center justify-content-between">
        <span class="text-white font-weight-bold" style="font-size:.95rem">
          <i class="fas fa-cash-register ml-1" style="color:#f59e0b"></i>
          موديول الخزينة
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
        <a href="{{ route('treasury_reports.index') }}"
           class="nav-link {{ request()->routeIs('treasury_reports.index') ? 'active' : '' }}">
          <i class="nav-icon fas fa-tachometer-alt"></i>
          <p>لوحة الخزينة</p>
        </a>
      </li>

      {{-- Cash Boxes --}}
      <li class="nav-item">
        <a href="{{ route('cash_boxes.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/treasury/cash-boxes*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-cash-register"></i>
          <p>الخزائن النقدية</p>
        </a>
      </li>

      {{-- Bank Accounts --}}
      <li class="nav-item">
        <a href="{{ route('bank_accounts.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/treasury/bank-accounts*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-university"></i>
          <p>الحسابات البنكية</p>
        </a>
      </li>

      {{-- Receipts --}}
      <li class="nav-item">
        <a href="{{ route('treasury_receipts.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/treasury/receipts*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-hand-holding-usd"></i>
          <p>سندات القبض</p>
        </a>
      </li>

      {{-- Payments --}}
      <li class="nav-item">
        <a href="{{ route('treasury_payments.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/treasury/payments*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-money-check"></i>
          <p>سندات الصرف</p>
        </a>
      </li>

      {{-- Cheques --}}
      <li class="nav-item">
        <a href="{{ route('cheques.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/treasury/cheques*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-money-check-alt"></i>
          <p>الشيكات</p>
        </a>
      </li>

      {{-- Reports --}}
      <li class="nav-item">
        <a href="{{ route('treasury_reports.cheques_due') }}"
           class="nav-link {{ request()->routeIs('treasury_reports.cheques_due') ? 'active' : '' }}">
          <i class="nav-icon fas fa-calendar-alt"></i>
          <p>الشيكات المستحقة</p>
        </a>
      </li>

    </ul>
  </nav>
</div>
