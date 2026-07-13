{{-- FILE: resources/views/admin/includes/sidebar.blade.php --}}

<div class="sidebar">
  <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center"
       style="border-bottom:1px solid rgba(255,255,255,.1)">
    <div class="info w-100">
      <div class="d-flex align-items-center justify-content-between">
        <span class="text-white font-weight-bold" style="font-size:.9rem">
          <i class="fas fa-users ml-1" style="color:#818cf8"></i>
          موديول HR
        </span>
        <a href="{{ route('admin.dashboard.home.page') }}"
           class="btn btn-xs" title="تبديل الموديول"
           style="background:rgba(255,255,255,.1);color:#aaa;font-size:.75rem;padding:3px 8px;border-radius:4px">
          <i class="fas fa-th-large"></i>
        </a>
      </div>
      <small class="text-muted d-block mt-1">{{ Auth::guard('admin')->user()->name }}
        @if(Auth::guard('admin')->user()->is_super_admin)
          <span class="badge badge-warning badge-sm" style="font-size:.65em">{{ __('admin.super_admin') }}</span>
        @endif
      </small>
    </div>
  </div>

  @php
    $pendingReqs = \App\Models\EmployeeRequest::where('com_code', Auth::guard('admin')->user()->com_code)->where('status',0)->count();
    $onEmployees     = request()->is('admin/dashboard/employees*');
    $onVacations     = request()->routeIs('vacations*');
    $onMainVacations = request()->is('admin/dashboard/Main_vacations*');
    $onEmpRequests   = request()->is('admin/dashboard/employee_requests*');
    $onSms           = request()->is('admin/dashboard/sms*');
    $hrMenuOpen      = $onEmployees || $onVacations || $onMainVacations || $onEmpRequests;
    $isRtl           = app()->getLocale() === 'ar';
  @endphp

  <nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column {{ $isRtl ? 'nav-sidebar-rtl' : '' }}"
        data-widget="treeview" role="menu" data-accordion="false">

      {{-- Home --}}
      <li class="nav-item">
        <a href="{{ route('admin.dashboard.home.page') }}"
           class="nav-link {{ request()->routeIs('admin.dashboard.home.page') ? 'active' : '' }}">
          <i class="nav-icon fas fa-home"></i>
          <p>{{ __('admin.home') }}</p>
        </a>
      </li>

      {{-- Settings --}}
      @php
        $settingsOpen = request()->is(
          'admin/dashboard/generalsetting*','admin/dashboard/branches*',
          'admin/dashboard/shifts*','admin/dashboard/departs*',
          'admin/dashboard/jobs*','admin/dashboard/finance*',
          'admin/dashboard/org_levels*','admin/dashboard/clients*',
          'admin/dashboard/income-tax-brackets*'
        );
      @endphp
      <li class="nav-item has-treeview {{ $settingsOpen ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $settingsOpen ? 'active' : '' }}">
          <i class="nav-icon fas fa-cog"></i>
          <p>{{ __('admin.settings_menu') }} <i class="right fas fa-angle-left"></i></p>
        </a>
        <ul class="nav nav-treeview">
          <li class="nav-item">
            <a href="{{ route('generalsetting.index') }}" class="nav-link {{ request()->is('admin/dashboard/generalsetting*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-sliders-h"></i><p>إعدادات الموارد البشرية</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('income_tax_brackets.index') }}" class="nav-link {{ request()->is('admin/dashboard/income-tax-brackets*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-percentage"></i><p>شرائح ضريبة كسب العمل</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('finance_calender.index') }}" class="nav-link {{ request()->is('admin/dashboard/finance_calender*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-calendar-alt"></i><p>{{ __('admin.fiscal_years') }}</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('clients.index') }}" class="nav-link {{ request()->is('admin/dashboard/clients*') ? 'active' : '' }}"
               style="{{ request()->is('admin/dashboard/clients*') ? '' : 'color:#c9a227' }}">
              <i class="nav-icon fas fa-handshake"></i><p>العملاء</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('branches.index') }}" class="nav-link {{ request()->is('admin/dashboard/branches*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-code-branch"></i><p>{{ __('admin.branches') }}</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('shifts.index') }}" class="nav-link {{ request()->is('admin/dashboard/shifts*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-clock"></i><p>{{ __('admin.shifts') }}</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('departs.index') }}" class="nav-link {{ request()->is('admin/dashboard/departs*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-sitemap"></i><p>{{ __('admin.departments') }}</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('jobs_categories.index') }}" class="nav-link {{ request()->is('admin/dashboard/jobs_categories*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-briefcase"></i><p>{{ __('admin.jobs') }}</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('org_levels.index') }}" class="nav-link {{ request()->is('admin/dashboard/org_levels*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-project-diagram"></i><p>{{ __('admin.org_structure') }}</p>
            </a>
          </li>
        </ul>
      </li>

      {{-- HR Management --}}
      <li class="nav-item has-treeview {{ $hrMenuOpen ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $hrMenuOpen ? 'active' : '' }}">
          <i class="nav-icon fas fa-users"></i>
          <p>{{ __('admin.hr_management') }}
            @if($pendingReqs > 0)
              <span class="badge badge-danger badge-pill mr-2">{{ $pendingReqs }}</span>
            @endif
            <i class="right fas fa-angle-left"></i>
          </p>
        </a>
        <ul class="nav nav-treeview">
          <li class="nav-item">
            <a href="{{ route('employees.index') }}" class="nav-link {{ $onEmployees ? 'active' : '' }}">
              <i class="nav-icon fas fa-user-tie"></i><p>{{ __('admin.employees') }}</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('vacations.index') }}" class="nav-link {{ $onVacations ? 'active' : '' }}">
              <i class="nav-icon fas fa-umbrella-beach"></i><p>{{ __('admin.leave_balances') }}</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('employee_requests.index') }}" class="nav-link {{ $onEmpRequests ? 'active' : '' }}">
              <i class="nav-icon fas fa-inbox"></i>
              <p>{{ __('admin.employee_requests') }}
                @if($pendingReqs > 0)
                  <span class="badge badge-danger badge-pill mr-1">{{ $pendingReqs }}</span>
                @endif
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('sms.compose') }}" class="nav-link {{ request()->routeIs('sms.compose') ? 'active' : '' }}">
              <i class="nav-icon fas fa-sms"></i>
              <p>رسائل SMS</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('sms.credentials.compose') }}" class="nav-link {{ request()->routeIs('sms.credentials.compose') ? 'active' : '' }}">
              <i class="nav-icon fas fa-key"></i>
              <p>إرسال بيانات الدخول (SMS)</p>
            </a>
          </li>
        </ul>
      </li>

      {{-- Attendance --}}
      @php $attendOpen = request()->is('admin/dashboard/attendance*','admin/dashboard/fingerprint*'); @endphp
      <li class="nav-item has-treeview {{ $attendOpen ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $attendOpen ? 'active' : '' }}">
          <i class="nav-icon fas fa-fingerprint"></i>
          <p>{{ __('admin.attendance') }} <i class="right fas fa-angle-left"></i></p>
        </a>
        <ul class="nav nav-treeview">
          <li class="nav-item">
            <a href="{{ route('attendance.create') }}" class="nav-link {{ request()->routeIs('attendance.create') ? 'active' : '' }}">
              <i class="nav-icon fas fa-user-check"></i><p>{{ __('admin.individual_entry') }}</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('attendance.bulk_create') }}" class="nav-link {{ request()->routeIs('attendance.bulk_create') ? 'active' : '' }}">
              <i class="nav-icon fas fa-users-cog"></i><p>{{ __('admin.bulk_entry') }}</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('attendance.range_batch_create') }}" class="nav-link {{ request()->routeIs('attendance.range_batch_create') ? 'active' : '' }}">
              <i class="nav-icon fas fa-calendar-alt"></i><p>{{ __('admin.att_range_batch_entry') }}</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('attendance.excel_import_form') }}" class="nav-link {{ request()->routeIs('attendance.excel_import_form') ? 'active' : '' }}">
              <i class="nav-icon fas fa-file-excel"></i>
              <p>{{ __('admin.excel_import') }} <small class="badge badge-warning mr-1">Finger ID</small></p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('fingerprint_devices.index') }}" class="nav-link {{ request()->is('admin/dashboard/fingerprint*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-microchip"></i><p>{{ __('admin.fingerprint_devices') }}</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('attendance.index') }}" class="nav-link {{ request()->routeIs('attendance.index') ? 'active' : '' }}">
              <i class="nav-icon fas fa-calendar-check"></i><p>{{ __('admin.attendance_records') }}</p>
            </a>
          </li>
        </ul>
      </li>

      {{-- KPI --}}
      @php $kpiOpen = request()->is('admin/dashboard/kpi*'); @endphp
      <li class="nav-item has-treeview {{ $kpiOpen ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $kpiOpen ? 'active' : '' }}">
          <i class="nav-icon fas fa-chart-line"></i>
          <p>{{ __('admin.kpi') }} <i class="right fas fa-angle-left"></i></p>
        </a>
        <ul class="nav nav-treeview">
          <li class="nav-item">
            <a href="{{ route('kpi.definitions') }}" class="nav-link {{ request()->routeIs('kpi.definitions') ? 'active' : '' }}">
              <i class="nav-icon fas fa-bullseye"></i><p>{{ __('admin.kpi_definitions') }}</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('kpi.scores') }}" class="nav-link {{ request()->routeIs('kpi.scores') ? 'active' : '' }}">
              <i class="nav-icon fas fa-edit"></i><p>{{ __('admin.monthly_readings') }}</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('kpi.report') }}" class="nav-link {{ request()->routeIs('kpi.report') ? 'active' : '' }}">
              <i class="nav-icon fas fa-chart-bar"></i><p>{{ __('admin.performance_report') }}</p>
            </a>
          </li>
        </ul>
      </li>

      {{-- Leave Compensation --}}
      <li class="nav-item">
        <a href="{{ route('leave_compensation.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/leave-compensation*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-umbrella-beach" style="color:#28a745"></i>
          <p>{{ __('admin.leave_compensation') }}</p>
        </a>
      </li>

      {{-- Sanctions --}}
      <li class="nav-item">
        <a href="{{ route('sanctions.index') }}"
           class="nav-link {{ request()->is('admin/dashboard/sanctions*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-gavel" style="color:#dc3545"></i>
          <p>{{ __('admin.sanctions') }}</p>
        </a>
      </li>

      {{-- Payroll --}}
      @php
        $payrollOpen = request()->is('admin/dashboard/advances*','admin/dashboard/commissions*','admin/dashboard/branch_commissions*','admin/dashboard/deductions*','admin/dashboard/bonuses*','admin/dashboard/payroll*');
      @endphp
      <li class="nav-item has-treeview {{ $payrollOpen ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $payrollOpen ? 'active' : '' }}">
          <i class="nav-icon fas fa-money-bill-wave"></i>
          <p>{{ __('admin.payroll_menu') }} <i class="right fas fa-angle-left"></i></p>
        </a>
        <ul class="nav nav-treeview">
          <li class="nav-item">
            <a href="{{ route('advances.index') }}" class="nav-link {{ request()->is('admin/dashboard/advances*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-hand-holding-usd"></i><p>{{ __('admin.advances') }}</p>
            </a>
          </li>

          @php $commOpen = request()->is('admin/dashboard/commissions*','admin/dashboard/branch_commissions*'); @endphp
          <li class="nav-item has-treeview {{ $commOpen ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ $commOpen ? 'active' : '' }}">
              <i class="nav-icon fas fa-percentage"></i>
              <p>{{ __('admin.commissions') }} <i class="right fas fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ route('commissions.index') }}" class="nav-link {{ request()->routeIs('commissions.index') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-coins"></i><p>{{ __('admin.individual_commissions') }}</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('commissions_v2.rules') }}" class="nav-link {{ request()->is('admin/dashboard/commissions_v2/rules*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-layer-group"></i><p>{{ __('admin.flexible_commission_rules') }}</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('commissions_v2.sales') }}" class="nav-link {{ request()->is('admin/dashboard/commissions_v2/sales*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-shopping-cart"></i><p>{{ __('admin.monthly_sales') }}</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('commissions_v2.calculate') }}" class="nav-link {{ request()->is('admin/dashboard/commissions_v2/calculate*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-calculator"></i><p>{{ __('admin.calculate_commissions') }}</p>
                </a>
              </li>
              @php $bcOpen = request()->is('admin/dashboard/branch_commissions*'); @endphp
              <li class="nav-item has-treeview {{ $bcOpen ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ $bcOpen ? 'active' : '' }}">
                  <i class="nav-icon fas fa-store"></i>
                  <p>عمولات الفروع (التارجت) <i class="right fas fa-angle-left"></i></p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{ route('branch_commissions.index') }}" class="nav-link {{ request()->routeIs('branch_commissions.index') ? 'active' : '' }}">
                      <i class="nav-icon fas fa-list"></i><p>الخطط</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('branch_commissions.targets') }}" class="nav-link {{ request()->routeIs('branch_commissions.targets') ? 'active' : '' }}">
                      <i class="nav-icon fas fa-bullseye"></i><p>تارجت الفروع</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('branch_commissions.employee_targets') }}" class="nav-link {{ request()->routeIs('branch_commissions.employee_targets') ? 'active' : '' }}">
                      <i class="nav-icon fas fa-user-tag"></i><p>تارجت الموظفين</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('branch_commissions.events') }}" class="nav-link {{ request()->routeIs('branch_commissions.events') ? 'active' : '' }}">
                      <i class="nav-icon fas fa-exchange-alt"></i><p>أحداث منتصف الشهر</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{ route('branch_commissions.calculate') }}" class="nav-link {{ request()->routeIs('branch_commissions.calculate') ? 'active' : '' }}">
                      <i class="nav-icon fas fa-calculator"></i><p>احتساب العمولات</p>
                    </a>
                  </li>
                </ul>
              </li>
            </ul>
          </li>

          <li class="nav-item">
            <a href="{{ route('deductions.index') }}" class="nav-link {{ request()->is('admin/dashboard/deductions*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-minus-circle"></i><p>{{ __('admin.deductions') }}</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('bonuses.index') }}" class="nav-link {{ request()->is('admin/dashboard/bonuses*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-gift"></i><p>المكافآت</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('payroll.index') }}" class="nav-link {{ request()->is('admin/dashboard/payroll*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-money-check-alt"></i><p>{{ __('admin.payroll') }}</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('salary_increases.index') }}" class="nav-link {{ request()->is('admin/dashboard/salary-increases*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-arrow-trend-up"></i><p>زيادات الرواتب</p>
            </a>
          </li>
        </ul>
      </li>

      {{-- Reports --}}
      @php $reportsMenuOpen = request()->is('admin/dashboard/reports*'); @endphp
      <li class="nav-item has-treeview {{ $reportsMenuOpen ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $reportsMenuOpen ? 'active' : '' }}">
          <i class="nav-icon fas fa-chart-bar"></i>
          <p>{{ __('admin.reports') }} <i class="right fas fa-angle-left"></i></p>
        </a>
        <ul class="nav nav-treeview">
          <li class="nav-item">
            <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.index') ? 'active' : '' }}">
              <i class="nav-icon fas fa-chart-bar"></i><p>كل التقارير</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('reports.contracts_expiring') }}" class="nav-link {{ request()->routeIs('reports.contracts_expiring') ? 'active' : '' }}">
              <i class="nav-icon fas fa-file-signature text-danger"></i><p>تنبيهات العقود/الاختبار</p>
            </a>
          </li>
        </ul>
      </li>

      {{-- Maintenance --}}
      @if(Auth::guard('admin')->user()->is_super_admin)
      <li class="nav-item">
        <a href="{{ route('maintenance.index') }}" class="nav-link {{ request()->is('admin/dashboard/maintenance*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-tools"></i>
          <p>{{ __('admin.maintenance') }}</p>
        </a>
      </li>
      @endif

      {{-- Management --}}
      @if(Auth::guard('admin')->user()->is_super_admin)
      <li class="nav-item has-treeview {{ request()->is('admin/dashboard/permissions*') ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ request()->is('admin/dashboard/permissions*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-user-shield"></i>
          <p>{{ __('admin.management') }} <i class="right fas fa-angle-left"></i></p>
        </a>
        <ul class="nav nav-treeview">
          <li class="nav-item">
            <a href="{{ route('admin.permissions.index') }}" class="nav-link {{ request()->is('admin/dashboard/permissions*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-key"></i><p>{{ __('admin.user_permissions') }}</p>
            </a>
          </li>
        </ul>
      </li>
      @endif

    </ul>
  </nav>
</div>
