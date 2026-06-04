{{--
  FILE: resources/views/admin/includes/sidebar.blade.php
--}}

<div class="sidebar">
  {{-- معلومات المستخدم بدون صورة --}}
  <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
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

    // تحديد الصفحة الحالية بالـ route name لحل مشكلة menu-open
    $onEmployees     = request()->is('admin/dashboard/employees*');
    $onVacations     = request()->routeIs('vacations*');
    $onMainVacations = request()->is('admin/dashboard/Main_vacations*');
    $onEmpRequests   = request()->is('admin/dashboard/employee_requests*');
    $hrMenuOpen      = $onEmployees || $onVacations || $onMainVacations || $onEmpRequests;
  @endphp

  <nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column nav-sidebar-rtl"
        data-widget="treeview" role="menu" data-accordion="false">

      {{-- الصفحة الرئيسية --}}
      <li class="nav-item">
        <a href="{{ route('admin.dashboard.home.page') }}"
           class="nav-link {{ request()->routeIs('admin.dashboard.home.page')?'active':'' }}">
          <i class="nav-icon fas fa-home"></i><p>الرئيسية</p>
        </a>
      </li>

      {{-- ══════ قائمة الضبط ══════ --}}
      @php
        $settingsOpen = request()->is(
          'admin/dashboard/generalsetting*','admin/dashboard/branches*',
          'admin/dashboard/shifts*','admin/dashboard/departs*',
          'admin/dashboard/jobs*','admin/dashboard/finance*',
          'admin/dashboard/org_levels*'
        );
      @endphp
      <li class="nav-item has-treeview {{ $settingsOpen ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $settingsOpen ? 'active' : '' }}">
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
          <li class="nav-item">
            <a href="{{ route('org_levels.index') }}" class="nav-link {{ request()->is('admin/dashboard/org_levels*')?'active':'' }}">
              <i class="fas fa-sitemap nav-icon" style="font-size:.9em"></i><p>الهيكل الوظيفي</p>
            </a>
          </li>
        </ul>
      </li>

      {{-- ══════ شئون الموظفين ══════ --}}
      <li class="nav-item has-treeview {{ $hrMenuOpen ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $hrMenuOpen ? 'active' : '' }}">
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
            <a href="{{ route('employees.index') }}" class="nav-link {{ $onEmployees?'active':'' }}">
              <i class="far fa-circle nav-icon"></i><p>الموظفين</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('vacations.index') }}" class="nav-link {{ $onVacations?'active':'' }}">
              <i class="far fa-circle nav-icon"></i><p>أرصدة الإجازات</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('employee_requests.index') }}"
               class="nav-link {{ $onEmpRequests?'active':'' }}">
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
      @php
        $attendOpen = request()->is('admin/dashboard/attendance*','admin/dashboard/fingerprint*');
      @endphp
      <li class="nav-item has-treeview {{ $attendOpen ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $attendOpen ? 'active' : '' }}">
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
      @php $kpiOpen = request()->is('admin/dashboard/kpi*'); @endphp
      <li class="nav-item has-treeview {{ $kpiOpen ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $kpiOpen ? 'active' : '' }}">
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
      @php
        $payrollOpen = request()->is('admin/dashboard/advances*','admin/dashboard/commissions*','admin/dashboard/deductions*','admin/dashboard/payroll*');
      @endphp
      <li class="nav-item has-treeview {{ $payrollOpen ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $payrollOpen ? 'active' : '' }}">
          <i class="nav-icon fas fa-money-bill-wave"></i>
          <p>الرواتب والمؤثرات <i class="right fas fa-angle-left"></i></p>
        </a>
        <ul class="nav nav-treeview">
          <li class="nav-item">
            <a href="{{ route('advances.index') }}" class="nav-link {{ request()->is('admin/dashboard/advances*')?'active':'' }}">
              <i class="nav-icon fas fa-hand-holding-usd text-warning"></i><p>السلف</p>
            </a>
          </li>

          {{-- العمولات --}}
          @php $commOpen = request()->is('admin/dashboard/commissions*'); @endphp
          <li class="nav-item has-treeview {{ $commOpen ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ $commOpen?'active':'' }}">
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

      {{-- ══════ التقارير ══════ --}}
      <li class="nav-item {{ request()->is('admin/dashboard/reports*') ? 'active' : '' }}">
        <a href="{{ route('reports.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/reports*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-chart-bar text-info"></i>
          <p>التقارير والتصدير</p>
        </a>
      </li>

      {{-- ══════ الصيانة والنسخ الاحتياطي ══════ --}}
      @if(Auth::guard('admin')->user()->is_super_admin)
      <li class="nav-item {{ request()->is('admin/dashboard/maintenance*') ? 'active' : '' }}">
        <a href="{{ route('maintenance.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/maintenance*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-tools text-warning"></i>
          <p>الصيانة والنسخ الاحتياطي</p>
        </a>
      </li>
      @endif

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
