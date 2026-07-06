<div class="sidebar">

  {{-- Module Header --}}
  <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center"
       style="border-bottom:1px solid rgba(255,255,255,.1)">
    <div class="info w-100">
      <div class="d-flex align-items-center justify-content-between">
        <span class="text-white font-weight-bold" style="font-size:.95rem">
          <i class="fas fa-calculator ml-1" style="color:#a78bfa"></i>
          موديول المحاسبة
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
        <a href="{{ route('accounting_reports.index') }}"
           class="nav-link {{ request()->routeIs('accounting_reports.index') ? 'active' : '' }}">
          <i class="nav-icon fas fa-tachometer-alt"></i>
          <p>لوحة المحاسبة</p>
        </a>
      </li>

      {{-- Chart of Accounts --}}
      <li class="nav-item">
        <a href="{{ route('chart_of_accounts.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/accounting/accounts*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-sitemap"></i>
          <p>دليل الحسابات</p>
        </a>
      </li>

      {{-- Cost Centers --}}
      <li class="nav-item">
        <a href="{{ route('cost_centers.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/accounting/cost-centers*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-project-diagram"></i>
          <p>مراكز التكلفة</p>
        </a>
      </li>

      {{-- Journal Entries --}}
      <li class="nav-item">
        <a href="{{ route('journal_entries.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/accounting/journal-entries*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-book"></i>
          <p>القيود اليومية</p>
        </a>
      </li>

      {{-- Accounting Periods --}}
      <li class="nav-item">
        <a href="{{ route('accounting_periods.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/accounting/periods*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-calendar-check"></i>
          <p>الفترات المحاسبية</p>
        </a>
      </li>

      {{-- Posting Rules --}}
      <li class="nav-item">
        <a href="{{ route('gl_posting_rules.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/accounting/posting-rules*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-cogs"></i>
          <p>إعدادات الترحيل التلقائي</p>
        </a>
      </li>

      {{-- Reports --}}
      @php $reportsOpen = request()->is('admin/dashboard/accounting/reports*'); @endphp
      <li class="nav-item has-treeview {{ $reportsOpen ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $reportsOpen ? 'active' : '' }}">
          <i class="nav-icon fas fa-chart-pie"></i>
          <p>التقارير المالية <i class="right fas fa-angle-left"></i></p>
        </a>
        <ul class="nav nav-treeview">
          <li class="nav-item">
            <a href="{{ route('accounting_reports.trial_balance') }}"
               class="nav-link {{ request()->routeIs('accounting_reports.trial_balance') ? 'active' : '' }}">
              <i class="nav-icon fas fa-balance-scale"></i><p>ميزان المراجعة</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('accounting_reports.income_statement') }}"
               class="nav-link {{ request()->routeIs('accounting_reports.income_statement') ? 'active' : '' }}">
              <i class="nav-icon fas fa-file-invoice-dollar"></i><p>قائمة الدخل</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('accounting_reports.balance_sheet') }}"
               class="nav-link {{ request()->routeIs('accounting_reports.balance_sheet') ? 'active' : '' }}">
              <i class="nav-icon fas fa-university"></i><p>الميزانية العمومية</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('accounting_reports.ledger') }}"
               class="nav-link {{ request()->routeIs('accounting_reports.ledger') ? 'active' : '' }}">
              <i class="nav-icon fas fa-list-alt"></i><p>كشف حساب</p>
            </a>
          </li>
        </ul>
      </li>

    </ul>
  </nav>
</div>
