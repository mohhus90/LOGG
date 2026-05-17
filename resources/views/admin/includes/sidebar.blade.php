{{--
  FILE: resources/views/admin/includes/sidebar.blade.php
  النسخة الكاملة النهائية
--}}

<div class="sidebar">
  <div class="user-panel mt-3 pb-3 mb-3 d-flex">
    <div class="image">
      @php $settings = \App\Models\Admin_panel_setting::where('com_code', Auth::guard('admin')->user()->com_code)->first(); @endphp
      @if($settings && $settings->image)
        <img src="{{ asset('storage/'.$settings->image) }}" class="img-circle elevation-2"
          alt="Logo" style="object-fit:cover">
      @else
        <img src="{{ asset('/assets/admin/dist/img/user2-160x160.jpg') }}"
          class="img-circle elevation-2" alt="User Image">
      @endif
    </div>
    <div class="info">
      <a href="#" class="d-block">{{ Auth::guard('admin')->user()->name }}</a>
      @if(Auth::guard('admin')->user()->is_super_admin)
        <span class="badge badge-warning badge-sm">سوبر أدمن</span>
      @endif
      <small class="text-muted d-block">{{ Auth::guard('admin')->user()->company?->name }}</small>
    </div>
  </div>

  {{-- طلبات الموظفين المعلقة --}}
  @php
    $pendingReqs = \App\Models\EmployeeRequest::where('com_code', Auth::guard('admin')->user()->com_code)->where('status',0)->count();
  @endphp

  <nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

      {{-- الصفحة الرئيسية --}}
      <li class="nav-item">
        <a href="{{ route('admin.dashboard.home.page') }}"
           class="nav-link {{ request()->routeIs('admin.dashboard.home.page')?'active':'' }}">
          <i class="nav-icon fas fa-home"></i><p>الرئيسية</p>
        </a>
      </li>

      {{-- ══════ قائمة الضبط ══════ --}}
      <li class="nav-item has-treeview {{ request()->is('admin/dashboard/generalsetting*','admin/dashboard/branches*','admin/dashboard/shifts*','admin/dashboard/departs*','admin/dashboard/jobs*','admin/dashboard/finance*') ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ request()->is('admin/dashboard/generalsetting*','admin/dashboard/branches*','admin/dashboard/shifts*','admin/dashboard/departs*','admin/dashboard/jobs*','admin/dashboard/finance*') ? 'active' : '' }}">
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

      {{-- ══════ شئون الموظفين ══════ --}}
      <li class="nav-item has-treeview {{ request()->is('admin/dashboard/employees*','admin/dashboard/Main_vacations*','admin/dashboard/employee_requests*') ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ request()->is('admin/dashboard/employees*','admin/dashboard/Main_vacations*','admin/dashboard/employee_requests*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-users"></i>
          <p>شئون الموظفين
            @if($pendingReqs > 0)
              <span class="badge badge-danger badge-pill mr-2">{{ $pendingReqs }}</span>
            @endif
            <i class="right fas fa-angle-left"></i>
          </p>
        </a>
        <ul class="nav nav-treeview">
          <li class="nav-item">
            <a href="{{ route('employees.index') }}" class="nav-link {{ request()->is('admin/dashboard/employees*')?'active':'' }}">
              <i class="far fa-circle nav-icon"></i><p>الموظفين</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('Main_vacations_balance.index') }}" class="nav-link {{ request()->is('admin/dashboard/Main_vacations_balance*')?'active':'' }}">
              <i class="far fa-circle nav-icon"></i><p>الرصيد السنوي للإجازات</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('employee_requests.index') }}"
               class="nav-link {{ request()->is('admin/dashboard/employee_requests*')?'active':'' }}">
              <i class="nav-icon fas fa-inbox"></i>
              <p>طلبات الموظفين
                @if($pendingReqs > 0)
                  <span class="badge badge-danger badge-pill mr-1">{{ $pendingReqs }}</span>
                @endif
              </p>
            </a>
          </li>
        </ul>
      </li>

      {{-- ══════ الحضور والانصراف ══════ --}}
      <li class="nav-item has-treeview {{ request()->is('admin/dashboard/attendance*','admin/dashboard/fingerprint*') ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ request()->is('admin/dashboard/attendance*','admin/dashboard/fingerprint*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-fingerprint"></i>
          <p>الحضور والانصراف <i class="right fas fa-angle-left"></i></p>
        </a>
        <ul class="nav nav-treeview">
          <li class="nav-item">
            <a href="{{ route('attendance.create') }}" class="nav-link {{ request()->routeIs('attendance.create')?'active':'' }}">
              <i class="far fa-circle nav-icon text-success"></i><p>تسجيل فردي</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('attendance.bulk_create') }}" class="nav-link {{ request()->routeIs('attendance.bulk_create')?'active':'' }}">
              <i class="far fa-circle nav-icon text-info"></i><p>إدخال دفعي</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('attendance.excel_import_form') }}" class="nav-link {{ request()->routeIs('attendance.excel_import_form')?'active':'' }}">
              <i class="nav-icon fas fa-file-excel text-warning"></i>
              <p>استيراد Excel <small class="badge badge-warning">Finger ID</small></p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('fingerprint_devices.index') }}" class="nav-link {{ request()->is('admin/dashboard/fingerprint*')?'active':'' }}">
              <i class="nav-icon fas fa-microchip text-primary"></i><p>أجهزة البصمة</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('attendance.index') }}" class="nav-link {{ request()->routeIs('attendance.index')?'active':'' }}">
              <i class="far fa-circle nav-icon"></i><p>سجلات الحضور</p>
            </a>
          </li>
        </ul>
      </li>

      {{-- ══════ مؤشرات الأداء KPIs ══════ --}}
      <li class="nav-item has-treeview {{ request()->is('admin/dashboard/kpi*') ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ request()->is('admin/dashboard/kpi*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-chart-line"></i>
          <p>مؤشرات الأداء (KPIs) <i class="right fas fa-angle-left"></i></p>
        </a>
        <ul class="nav nav-treeview">
          <li class="nav-item">
            <a href="{{ route('kpi.definitions') }}" class="nav-link {{ request()->routeIs('kpi.definitions')?'active':'' }}">
              <i class="far fa-circle nav-icon"></i><p>تعريف المؤشرات</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('kpi.scores') }}" class="nav-link {{ request()->routeIs('kpi.scores')?'active':'' }}">
              <i class="far fa-circle nav-icon"></i><p>إدخال القراءات الشهرية</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('kpi.report') }}" class="nav-link {{ request()->routeIs('kpi.report')?'active':'' }}">
              <i class="fas fa-chart-bar nav-icon text-info"></i><p>تقرير الأداء</p>
            </a>
          </li>
        </ul>
      </li>

      {{-- ══════ الرواتب والمؤثرات ══════ --}}
      <li class="nav-item has-treeview {{ request()->is('admin/dashboard/advances*','admin/dashboard/commissions*','admin/dashboard/deductions*','admin/dashboard/payroll*') ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ request()->is('admin/dashboard/advances*','admin/dashboard/commissions*','admin/dashboard/deductions*','admin/dashboard/payroll*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-money-bill-wave"></i>
          <p>الرواتب والمؤثرات <i class="right fas fa-angle-left"></i></p>
        </a>
        <ul class="nav nav-treeview">
          <li class="nav-item">
            <a href="{{ route('advances.index') }}" class="nav-link {{ request()->is('admin/dashboard/advances*')?'active':'' }}">
              <i class="nav-icon fas fa-hand-holding-usd text-warning"></i><p>السلف</p>
            </a>
          </li>

          {{-- العمولات (مع قائمة فرعية) --}}
          <li class="nav-item has-treeview {{ request()->is('admin/dashboard/commissions*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->is('admin/dashboard/commissions*')?'active':'' }}">
              <i class="nav-icon fas fa-percentage text-success"></i>
              <p>العمولات <i class="right fas fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ route('commissions.index') }}" class="nav-link {{ request()->routeIs('commissions.index')?'active':'' }}">
                  <i class="far fa-circle nav-icon"></i><p>العمولات الفردية</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('commissions_v2.rules') }}" class="nav-link {{ request()->is('admin/dashboard/commissions_v2/rules*')?'active':'' }}">
                  <i class="far fa-circle nav-icon"></i><p>قواعد العمولات المرنة</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('commissions_v2.sales') }}" class="nav-link {{ request()->is('admin/dashboard/commissions_v2/sales*')?'active':'' }}">
                  <i class="far fa-circle nav-icon"></i><p>إدخال مبيعات الشهر</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('commissions_v2.calculate') }}" class="nav-link {{ request()->is('admin/dashboard/commissions_v2/calculate*')?'active':'' }}">
                  <i class="fas fa-calculator nav-icon text-primary"></i><p>احتساب العمولات</p>
                </a>
              </li>
            </ul>
          </li>

          <li class="nav-item">
            <a href="{{ route('deductions.index') }}" class="nav-link {{ request()->is('admin/dashboard/deductions*')?'active':'' }}">
              <i class="nav-icon fas fa-minus-circle text-danger"></i><p>الخصومات</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('payroll.index') }}" class="nav-link {{ request()->is('admin/dashboard/payroll*')?'active':'' }}">
              <i class="nav-icon fas fa-money-check-alt text-primary"></i><p>مسير الرواتب</p>
            </a>
          </li>
        </ul>
      </li>

      {{-- ══════ الإدارة (سوبر أدمن) ══════ --}}
      @if(Auth::guard('admin')->user()->is_super_admin)
      <li class="nav-item has-treeview {{ request()->is('admin/dashboard/permissions*') ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ request()->is('admin/dashboard/permissions*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-user-shield text-warning"></i>
          <p>الإدارة <i class="right fas fa-angle-left"></i></p>
        </a>
        <ul class="nav nav-treeview">
          <li class="nav-item">
            <a href="{{ route('admin.permissions.index') }}"
               class="nav-link {{ request()->is('admin/dashboard/permissions*')?'active':'' }}">
              <i class="far fa-circle nav-icon"></i><p>صلاحيات المستخدمين</p>
            </a>
          </li>
        </ul>
      </li>
      @endif

    </ul>
  </nav>
</div>
