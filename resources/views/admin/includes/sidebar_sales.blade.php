<div class="sidebar">

  {{-- Module Header --}}
  <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center"
       style="border-bottom:1px solid rgba(255,255,255,.1)">
    <div class="info w-100">
      <div class="d-flex align-items-center justify-content-between">
        <span class="text-white font-weight-bold" style="font-size:.95rem">
          <i class="fas fa-chart-line ml-1" style="color:#34d399"></i>
          موديول المبيعات
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
        <a href="{{ route('sales_reports.index') }}"
           class="nav-link {{ request()->routeIs('sales_reports.index') ? 'active' : '' }}">
          <i class="nav-icon fas fa-tachometer-alt"></i>
          <p>لوحة المبيعات</p>
        </a>
      </li>

      {{-- Customers --}}
      <li class="nav-item">
        <a href="{{ route('sales_customers.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/sales/customers*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-users"></i>
          <p>العملاء</p>
        </a>
      </li>

      {{-- Items --}}
      @php $itemsOpen = request()->is('admin/dashboard/sales/items*','admin/dashboard/sales/item-units*','admin/dashboard/sales/item-categories*'); @endphp
      <li class="nav-item has-treeview {{ $itemsOpen ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $itemsOpen ? 'active' : '' }}">
          <i class="nav-icon fas fa-boxes"></i>
          <p>الأصناف <i class="right fas fa-angle-left"></i></p>
        </a>
        <ul class="nav nav-treeview">
          <li class="nav-item">
            <a href="{{ route('items.index') }}"
               class="nav-link {{ request()->is('admin/dashboard/sales/items*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-box"></i><p>قائمة الأصناف</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('item_categories.index') }}"
               class="nav-link {{ request()->is('admin/dashboard/sales/item-categories*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-tags"></i><p>مجموعات الأصناف</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('item_units.index') }}"
               class="nav-link {{ request()->is('admin/dashboard/sales/item-units*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-ruler-combined"></i><p>وحدات القياس</p>
            </a>
          </li>
        </ul>
      </li>

      {{-- Quotations --}}
      <li class="nav-item">
        <a href="{{ route('sales_quotations.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/sales/quotations*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-file-alt"></i>
          <p>عروض الأسعار</p>
        </a>
      </li>

      {{-- Orders --}}
      <li class="nav-item">
        <a href="{{ route('sales_orders.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/sales/orders*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-shopping-cart"></i>
          <p>أوامر البيع</p>
        </a>
      </li>

      {{-- Invoices --}}
      <li class="nav-item">
        <a href="{{ route('sales_invoices.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/sales/invoices*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-file-invoice-dollar"></i>
          <p>فواتير البيع</p>
        </a>
      </li>

      {{-- Payments --}}
      <li class="nav-item">
        <a href="{{ route('sales_payments.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/sales/payments*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-money-bill-wave"></i>
          <p>المدفوعات</p>
        </a>
      </li>

      {{-- Returns --}}
      <li class="nav-item">
        <a href="{{ route('sales_returns.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/sales/returns*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-undo-alt"></i>
          <p>المرتجعات</p>
        </a>
      </li>

      {{-- Reports --}}
      @php $reportsOpen = request()->is('admin/dashboard/sales/reports*'); @endphp
      <li class="nav-item has-treeview {{ $reportsOpen ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $reportsOpen ? 'active' : '' }}">
          <i class="nav-icon fas fa-chart-bar"></i>
          <p>التقارير <i class="right fas fa-angle-left"></i></p>
        </a>
        <ul class="nav nav-treeview">
          <li class="nav-item">
            <a href="{{ route('sales_reports.index') }}"
               class="nav-link {{ request()->routeIs('sales_reports.index') ? 'active' : '' }}">
              <i class="nav-icon fas fa-tachometer-alt"></i><p>ملخص المبيعات</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('sales_reports.summary') }}"
               class="nav-link {{ request()->routeIs('sales_reports.summary') ? 'active' : '' }}">
              <i class="nav-icon fas fa-list-alt"></i><p>تقرير تفصيلي</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('sales_reports.customer') }}"
               class="nav-link {{ request()->routeIs('sales_reports.customer') ? 'active' : '' }}">
              <i class="nav-icon fas fa-user-chart"></i><p>مبيعات بالعميل</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('sales_reports.item') }}"
               class="nav-link {{ request()->routeIs('sales_reports.item') ? 'active' : '' }}">
              <i class="nav-icon fas fa-boxes"></i><p>مبيعات بالصنف</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('sales_reports.debt') }}"
               class="nav-link {{ request()->routeIs('sales_reports.debt') ? 'active' : '' }}">
              <i class="nav-icon fas fa-exclamation-circle text-danger"></i><p>تقرير الديون</p>
            </a>
          </li>
        </ul>
      </li>

    </ul>
  </nav>
</div>
