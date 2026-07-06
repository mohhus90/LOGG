<div class="sidebar">

  {{-- Module Header --}}
  <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center"
       style="border-bottom:1px solid rgba(255,255,255,.1)">
    <div class="info w-100">
      <div class="d-flex align-items-center justify-content-between">
        <span class="text-white font-weight-bold" style="font-size:.95rem">
          <i class="fas fa-shopping-cart ml-1" style="color:#c084fc"></i>
          موديول المشتريات
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
        <a href="{{ route('purchase_reports.index') }}"
           class="nav-link {{ request()->routeIs('purchase_reports.index') ? 'active' : '' }}">
          <i class="nav-icon fas fa-tachometer-alt"></i>
          <p>لوحة المشتريات</p>
        </a>
      </li>

      {{-- Suppliers --}}
      <li class="nav-item">
        <a href="{{ route('suppliers.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/purchasing/suppliers*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-truck"></i>
          <p>الموردون</p>
        </a>
      </li>

      {{-- Items (shared catalog) --}}
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

      {{-- Requests --}}
      <li class="nav-item">
        <a href="{{ route('purchase_requests.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/purchasing/requests*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-clipboard-list"></i>
          <p>طلبات الشراء</p>
        </a>
      </li>

      {{-- Orders --}}
      <li class="nav-item">
        <a href="{{ route('purchase_orders.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/purchasing/orders*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-file-signature"></i>
          <p>أوامر الشراء</p>
        </a>
      </li>

      {{-- Invoices --}}
      <li class="nav-item">
        <a href="{{ route('purchase_invoices.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/purchasing/invoices*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-file-invoice-dollar"></i>
          <p>فواتير الشراء</p>
        </a>
      </li>

      {{-- Payments --}}
      <li class="nav-item">
        <a href="{{ route('purchase_payments.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/purchasing/payments*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-money-check-alt"></i>
          <p>المدفوعات</p>
        </a>
      </li>

      {{-- Returns --}}
      <li class="nav-item">
        <a href="{{ route('purchase_returns.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/purchasing/returns*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-undo-alt"></i>
          <p>المرتجعات</p>
        </a>
      </li>

      {{-- Reports --}}
      @php $reportsOpen = request()->is('admin/dashboard/purchasing/reports*'); @endphp
      <li class="nav-item has-treeview {{ $reportsOpen ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $reportsOpen ? 'active' : '' }}">
          <i class="nav-icon fas fa-chart-bar"></i>
          <p>التقارير <i class="right fas fa-angle-left"></i></p>
        </a>
        <ul class="nav nav-treeview">
          <li class="nav-item">
            <a href="{{ route('purchase_reports.index') }}"
               class="nav-link {{ request()->routeIs('purchase_reports.index') ? 'active' : '' }}">
              <i class="nav-icon fas fa-tachometer-alt"></i><p>ملخص المشتريات</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('purchase_reports.summary') }}"
               class="nav-link {{ request()->routeIs('purchase_reports.summary') ? 'active' : '' }}">
              <i class="nav-icon fas fa-list-alt"></i><p>تقرير تفصيلي</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('purchase_reports.supplier') }}"
               class="nav-link {{ request()->routeIs('purchase_reports.supplier') ? 'active' : '' }}">
              <i class="nav-icon fas fa-truck"></i><p>مشتريات بالمورد</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('purchase_reports.item') }}"
               class="nav-link {{ request()->routeIs('purchase_reports.item') ? 'active' : '' }}">
              <i class="nav-icon fas fa-boxes"></i><p>مشتريات بالصنف</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('purchase_reports.debt') }}"
               class="nav-link {{ request()->routeIs('purchase_reports.debt') ? 'active' : '' }}">
              <i class="nav-icon fas fa-exclamation-circle text-danger"></i><p>تقرير المستحقات</p>
            </a>
          </li>
        </ul>
      </li>

    </ul>
  </nav>
</div>
