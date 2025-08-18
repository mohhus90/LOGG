<div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="{{asset('/assets/admin/dist/img/user2-160x160.jpg')}}" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <ul class="navbar-nav ms-auto">

                <li class="nav-item dropdown">
                    <a id="navbarDropdown" class="nav-link" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        {{ Auth::guard('admin')->user()->name }}
                    </a>
    
                </li>
           
        </ul>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
          <li class="nav-item has-treeview menu-open">
            <a href="#" class="nav-link active">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>
                قائمة الضبط
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ route('generalsetting.index') }}" class="nav-link {{ request()->is('admin/dashboard/generalsetting*')?'active':'' }}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>الضبط العام</p>
                </a>
              </li><li class="nav-item">
                <a href="{{ route('finance_calender.index') }}" class="nav-link {{ request()->is('admin/dashboard/finance_calender*')?'active':'' }}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>السنوات المالية</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('branches.index') }}" class="nav-link {{ request()->is('admin/dashboard/branches*')?'active':'' }}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>الفروع</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('shifts.index') }}" class="nav-link {{ request()->is('admin/dashboard/shifts*')?'active':'' }}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>الشيفتات</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('departs.index') }}" class="nav-link {{ request()->is('admin/dashboard/departs*')?'active':'' }}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>الادارات</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('jobs_categories.index') }}" class="nav-link {{ request()->is('admin/dashboard/jobs_categories*')?'active':'' }}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>الوظائف</p>
                </a>
              </li>
              
            </ul>
          </li>
          <li class="nav-item has-treeview menu-open">
            <a href="#" class="nav-link active">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>
                قائمة شئون الموظفين
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ route('employees.index') }}" class="nav-link {{ request()->is('admin/dashboard/employees*')?'active':'' }}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>الموظفين</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item has-treeview menu-open">
            <a href="#" class="nav-link active">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>
                قائمة الرصيد السنوى
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ route('Main_vacations_balance.index') }}" class="nav-link {{ request()->is('admin/dashboard/Main_vacations_balance*')?'active':'' }}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>الرصيد السنوى</p>
                </a>
              </li>
            </ul>
          </li>
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>