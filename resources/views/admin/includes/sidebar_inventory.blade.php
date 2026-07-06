<div class="sidebar">

  {{-- Module Header --}}
  <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center"
       style="border-bottom:1px solid rgba(255,255,255,.1)">
    <div class="info w-100">
      <div class="d-flex align-items-center justify-content-between">
        <span class="text-white font-weight-bold" style="font-size:.95rem">
          <i class="fas fa-warehouse ml-1" style="color:#fbbf24"></i>
          موديول المخازن
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
        <a href="{{ route('inventory_reports.index') }}"
           class="nav-link {{ request()->routeIs('inventory_reports.index') ? 'active' : '' }}">
          <i class="nav-icon fas fa-tachometer-alt"></i>
          <p>لوحة المخازن</p>
        </a>
      </li>

      {{-- Warehouses --}}
      <li class="nav-item">
        <a href="{{ route('warehouses.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/inventory/warehouses*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-warehouse"></i>
          <p>المخازن</p>
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

      {{-- Stock Levels --}}
      <li class="nav-item">
        <a href="{{ route('stock_levels.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/inventory/stock*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-cubes"></i>
          <p>أرصدة المخزون</p>
        </a>
      </li>

      {{-- Movements --}}
      <li class="nav-item">
        <a href="{{ route('stock_movements.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/inventory/movements*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-exchange-alt"></i>
          <p>حركة الأصناف</p>
        </a>
      </li>

      {{-- Adjustments --}}
      <li class="nav-item">
        <a href="{{ route('stock_adjustments.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/inventory/adjustments*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-balance-scale"></i>
          <p>تسويات المخزون</p>
        </a>
      </li>

      {{-- Transfers --}}
      <li class="nav-item">
        <a href="{{ route('stock_transfers.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/inventory/transfers*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-dolly"></i>
          <p>تحويلات المخازن</p>
        </a>
      </li>

      {{-- Reports --}}
      @php $reportsOpen = request()->is('admin/dashboard/inventory/reports*'); @endphp
      <li class="nav-item has-treeview {{ $reportsOpen ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $reportsOpen ? 'active' : '' }}">
          <i class="nav-icon fas fa-chart-bar"></i>
          <p>التقارير <i class="right fas fa-angle-left"></i></p>
        </a>
        <ul class="nav nav-treeview">
          <li class="nav-item">
            <a href="{{ route('inventory_reports.index') }}"
               class="nav-link {{ request()->routeIs('inventory_reports.index') ? 'active' : '' }}">
              <i class="nav-icon fas fa-tachometer-alt"></i><p>ملخص المخازن</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('inventory_reports.valuation') }}"
               class="nav-link {{ request()->routeIs('inventory_reports.valuation') ? 'active' : '' }}">
              <i class="nav-icon fas fa-coins"></i><p>تقييم المخزون</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('inventory_reports.low_stock') }}"
               class="nav-link {{ request()->routeIs('inventory_reports.low_stock') ? 'active' : '' }}">
              <i class="nav-icon fas fa-exclamation-circle text-danger"></i><p>تنبيهات نقص المخزون</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('inventory_reports.movements_summary') }}"
               class="nav-link {{ request()->routeIs('inventory_reports.movements_summary') ? 'active' : '' }}">
              <i class="nav-icon fas fa-list-alt"></i><p>ملخص الحركة</p>
            </a>
          </li>
        </ul>
      </li>

    </ul>
  </nav>
</div>
