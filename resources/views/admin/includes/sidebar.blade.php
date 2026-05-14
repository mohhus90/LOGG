{{--
  ملف: resources/views/admin/includes/sidebar.blade.php
  نسخة محدّثة تشمل الأقسام الجديدة
  استبدل محتوى الملف الأصلي بهذا الملف
--}}

<div class="sidebar">
  <!-- Sidebar user panel -->
  <div class="user-panel mt-3 pb-3 mb-3 d-flex">
    <div class="image">
      <img src="{{ asset('/assets/admin/dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2" alt="User Image">
    </div>
    <div class="info">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item dropdown">
          <a id="navbarDropdown" class="nav-link" href="#" role="button"
             data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            {{ Auth::guard('admin')->user()->name }}
            @if(Auth::guard('admin')->user()->is_super_admin)
              <small class="badge badge-warning">سوبر أدمن</small>
            @endif
          </a>
        </li>
      </ul>
    </div>
  </div>

  <nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

      {{-- ===== قائمة الضبط ===== --}}
      <li class="nav-item has-treeview {{ request()->is('admin/dashboard/generalsetting*','admin/dashboard/branches*','admin/dashboard/shifts*','admin/dashboard/departs*','admin/dashboard/jobs_categories*','admin/dashboard/finance_calender*') ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ request()->is('admin/dashboard/generalsetting*','admin/dashboard/branches*','admin/dashboard/shifts*','admin/dashboard/departs*','admin/dashboard/jobs_categories*','admin/dashboard/finance_calender*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-cog"></i>
          <p>قائمة الضبط <i class="right fas fa-angle-left"></i></p>
        </a>
        <ul class="nav nav-treeview">
          <li class="nav-item">
            <a href="{{ route('generalsetting.index') }}" class="nav-link {{ request()->is('admin/dashboard/generalsetting*')?'active':'' }}">
              <i class="far fa-circle nav-icon"></i><p>الضبط العام</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('finance_calender.index') }}" class="nav-link {{ request()->is('admin/dashboard/finance_calender*')?'active':'' }}">
              <i class="far fa-circle nav-icon"></i><p>السنوات المالية</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('branches.index') }}" class="nav-link {{ request()->is('admin/dashboard/branches*')?'active':'' }}">
              <i class="far fa-circle nav-icon"></i><p>الفروع</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('shifts.index') }}" class="nav-link {{ request()->is('admin/dashboard/shifts*')?'active':'' }}">
              <i class="far fa-circle nav-icon"></i><p>الشيفتات</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('departs.index') }}" class="nav-link {{ request()->is('admin/dashboard/departs*')?'active':'' }}">
              <i class="far fa-circle nav-icon"></i><p>الإدارات</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('jobs_categories.index') }}" class="nav-link {{ request()->is('admin/dashboard/jobs_categories*')?'active':'' }}">
              <i class="far fa-circle nav-icon"></i><p>الوظائف</p>
            </a>
          </li>
        </ul>
      </li>

      {{-- ===== شئون الموظفين ===== --}}
      <li class="nav-item has-treeview {{ request()->is('admin/dashboard/employees*','admin/dashboard/Main_vacations_balance*') ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ request()->is('admin/dashboard/employees*','admin/dashboard/Main_vacations_balance*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-users"></i>
          <p>شئون الموظفين <i class="right fas fa-angle-left"></i></p>
        </a>
        <ul class="nav nav-treeview">
          <li class="nav-item">
            <a href="{{ route('employees.index') }}" class="nav-link {{ request()->is('admin/dashboard/employees*')?'active':'' }}">
              <i class="far fa-circle nav-icon"></i><p>الموظفين</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('Main_vacations_balance.index') }}" class="nav-link {{ request()->is('admin/dashboard/Main_vacations_balance*')?'active':'' }}">
              <i class="far fa-circle nav-icon"></i><p>الرصيد السنوي</p>
            </a>
          </li>
        </ul>
      </li>

      {{-- ===== الحضور والانصراف (محدّث) ===== --}}
      <li class="nav-item has-treeview {{ request()->is('admin/dashboard/attendance*','admin/dashboard/fingerprint*') ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ request()->is('admin/dashboard/attendance*','admin/dashboard/fingerprint*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-fingerprint"></i>
          <p>الحضور والانصراف <i class="right fas fa-angle-left"></i></p>
        </a>
        <ul class="nav nav-treeview">

          {{-- 1. إدخال فردي --}}
          <li class="nav-item">
            <a href="{{ route('attendance.create') }}"
              class="nav-link {{ request()->is('admin/dashboard/attendance/create') ? 'active' : '' }}">
              <i class="far fa-circle nav-icon text-success"></i>
              <p>تسجيل حضور فردي</p>
            </a>
          </li>

          {{-- 2. إدخال دفعي --}}
          <li class="nav-item">
            <a href="{{ route('attendance.bulk_create') }}"
              class="nav-link {{ request()->is('admin/dashboard/attendance/bulk') ? 'active' : '' }}">
              <i class="far fa-circle nav-icon text-info"></i>
              <p>إدخال دفعي يدوي</p>
            </a>
          </li>

          {{-- 3. رفع Excel --}}
          <li class="nav-item">
            <a href="{{ route('attendance.excel_import_form') }}"
              class="nav-link {{ request()->is('admin/dashboard/attendance/excel*') ? 'active' : '' }}">
              <i class="far fa-circle nav-icon text-warning"></i>
              <p>استيراد من Excel <small class="badge badge-warning">Finger ID</small></p>
            </a>
          </li>

          {{-- 4. أجهزة البصمة --}}
          <li class="nav-item">
            <a href="{{ route('fingerprint_devices.index') }}"
              class="nav-link {{ request()->is('admin/dashboard/fingerprint*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-microchip text-primary"></i>
              <p>أجهزة البصمة المباشرة</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="{{ route('attendance.index') }}"
              class="nav-link {{ request()->routeIs('attendance.index') ? 'active' : '' }}">
              <i class="far fa-circle nav-icon"></i>
              <p>سجلات الحضور</p>
            </a>
          </li>

        </ul>
      </li>

      {{-- ===== الرواتب والمؤثرات ===== --}}
      <li class="nav-item has-treeview {{ request()->is('admin/dashboard/advances*','admin/dashboard/commissions*','admin/dashboard/deductions*','admin/dashboard/payroll*') ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ request()->is('admin/dashboard/advances*','admin/dashboard/commissions*','admin/dashboard/deductions*','admin/dashboard/payroll*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-money-bill-wave"></i>
          <p>الرواتب والمؤثرات <i class="right fas fa-angle-left"></i></p>
        </a>
        <ul class="nav nav-treeview">
          <li class="nav-item">
            <a href="{{ route('advances.index') }}" class="nav-link {{ request()->is('admin/dashboard/advances*')?'active':'' }}">
              <i class="nav-icon fas fa-hand-holding-usd"></i><p>السلف</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('commissions.index') }}" class="nav-link {{ request()->is('admin/dashboard/commissions*')?'active':'' }}">
              <i class="nav-icon fas fa-percentage"></i><p>العمولات</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('deductions.index') }}" class="nav-link {{ request()->is('admin/dashboard/deductions*')?'active':'' }}">
              <i class="nav-icon fas fa-minus-circle"></i><p>الخصومات</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('payroll.index') }}" class="nav-link {{ request()->is('admin/dashboard/payroll*')?'active':'' }}">
              <i class="nav-icon fas fa-money-check-alt"></i><p>مسير الرواتب</p>
            </a>
          </li>
        </ul>
      </li>

      {{-- ===== الإدارة (سوبر أدمن فقط) ===== --}}
      @if(Auth::guard('admin')->user()->is_super_admin)
      <li class="nav-item has-treeview {{ request()->is('admin/dashboard/permissions*') ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ request()->is('admin/dashboard/permissions*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-user-shield"></i>
          <p>الإدارة <i class="right fas fa-angle-left"></i></p>
        </a>
        <ul class="nav nav-treeview">
          <li class="nav-item">
            <a href="{{ route('admin.permissions.index') }}" class="nav-link {{ request()->is('admin/dashboard/permissions*')?'active':'' }}">
              <i class="far fa-circle nav-icon"></i><p>صلاحيات المستخدمين</p>
            </a>
          </li>
        </ul>
      </li>
      @endif

    </ul>
  </nav>
</div>
