<aside
    class="main-sidebar elevation-4 text-sm  {{ auth()->user()->theme_mode == 1 ? 'sidebar-dark-info' : 'sidebar-dark-info' }}">
    <!-- Brand Logo -->
    <a href="{{ route('dashboard') }}"
       class="brand-link text-sm {{ auth()->user()->theme_mode == 1 ? 'bg-white' : ' bg-dark' }} ">
        <img src="{{ asset('img/logo.png') }}" alt="Logo"
             class="brand-image img-circle elevation-3"
             style="opacity: .8">
        <span class="brand-text text-info text-sm"><b>Admin</b>Panel</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column text-sm nav-child-indent nav-flat " data-widget="treeview"
                role="menu"
                data-accordion="false">
                @if(session('dashboard_layout', 'modular') == 'modular')
                    @include('layouts.partial.__sidebar_modular')
                @else
                    @if(auth()->user()->can('dashboard'))
                        <li class="nav-item">
                            <a href="{{ route('dashboard') }}"
                               class="nav-link {{ Route::currentRouteName() == 'dashboard' ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                    @endif
                    @include('layouts.menu.__user_management')
                    @if(auth()->user()->can('branch_view'))
                        <?php
                        $subMenu = ['sister-concerns.index', 'sister-concerns.create', 'sister-concerns.edit'];
                        ?>
                        <li class="nav-item {{ in_array(Route::currentRouteName(), $subMenu) ? 'menu-open' : '' }}">
                            <a href="{{ route('sister-concerns.index') }}"
                               class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Branches</p>
                            </a>
                        </li>
                    @endif    
                    @if(auth()->user()->role == 'Admin')
                    <?php
                    $subMenu = [
                        'area-manager.index', 'area-manager.create', 'area-manager.edit', 'area-manager.show',
                         'branch-manager.index', 'branch-manager.create', 'branch-manager.edit', 'branch-manager.show',
                         'area-manager-assignments.index', 'area-manager-assignments.create', 'area-manager-assignments.edit', 'area-manager-assignments.show',
                         'branch-manager-assignments.index', 'branch-manager-assignments.create', 'branch-manager-assignments.edit', 'branch-manager-assignments.show',
                         'tasks.index','tasks.distribute','tasks.show',
                    ];
                    ?>
                        <li class="nav-item {{ in_array(Route::currentRouteName(), $subMenu) ? 'menu-open' : '' }}">
                            <a href="#"
                            class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
                                <i class="nav-icon fas fa-exchange"></i>
                                <p>
                                    Manager Management
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <?php
                                $subSubMenu = ['area-manager.index', 'area-manager.create', 'area-manager.edit', 'area-manager.show'];
                                ?>
                                <li class="nav-item">
                                    <a href="{{ route('area-manager.index') }}"
                                    class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-user-tie"></i>
                                        <p>Area Manager</p>
                                    </a>
                                </li>
                                <?php
                                $subSubMenu = ['branch-manager.index', 'branch-manager.create', 'branch-manager.edit', 'branch-manager.show'];
                                ?>
                                <li class="nav-item">
                                    <a href="{{ route('branch-manager.index') }}"
                                    class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-user-friends"></i>
                                        <p>Branch Manager</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('area-manager-assignments.index') }}"
                                    class="nav-link {{ request()->is('admin/area-manager-assignments*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-user-tie"></i>
                                        <p>AM Assignments</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('branch-manager-assignments.index') }}"
                                    class="nav-link {{ request()->is('admin/branch-manager-assignments*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-user-friends"></i>
                                        <p>BM Assignments</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('tasks.index', 'area_manager') }}"
                                    class="nav-link {{ request()->is('admin/tasks/area_manager*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-bullseye"></i>
                                        <p>AM Targets</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif

                    @if(auth()->user()->role == 'Area_Manager')
                        <li class="nav-item">
                            <a href="{{ route('tasks.index', 'branch_manager') }}"
                               class="nav-link {{ request()->is('admin/tasks/branch_manager*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-bullseye"></i>
                                <p>BM Targets</p>
                            </a>
                        </li>
                    @endif

                    <?php
                    $subMenu = [
                        'notices.index', 'notices.create', 'notices.edit',
                        'meetings.index', 'meetings.create', 'meetings.edit', 'meetings.show',
                        'crm-statuses.create','crm-statuses.edit','crm-statuses.index','leads.index',
                        'leads.create','leads.edit','leads.update_history',
                    ];
                    ?>
                    <li class="nav-item {{ in_array(Route::currentRouteName(), $subMenu) ? 'menu-open' : '' }}">
                        <a href="#"
                        class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
                            <i class="nav-icon fas fa-exchange"></i>
                            <p>
                                Office Management
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @include('layouts.menu.__notice')
                            @include('layouts.menu.__meetings')
                            @include('layouts.menu.__crm')
                        </ul>
                    </li>

                    <?php
                    $subMenu = [
                        'sister-concerns.index', 'sister-concerns.create', 'sister-concerns.edit',
                        'warehouses.index','warehouses.create','warehouses.edit',
                    ];
                    ?>
                    {{-- @if(auth()->user()->can('settings')) --}}
                        <li class="nav-item d-none {{ in_array(Route::currentRouteName(), $subMenu) ? 'menu-open' : '' }}">
                            <a href="#"
                                class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
                                <i class="nav-icon fas fa-cogs"></i>
                                <p>Settings <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <?php
                                $subSubMenu = ['warehouses.index', 'warehouses.create', 'warehouses.edit'];
                                ?>
                                @if(auth()->user()->can('warehouse') && auth()->user()->role != 'Admin')
                                    <li class="nav-item">
                                        <a href="{{ route('warehouses.index') }}"
                                            class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                            <i class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                            <p>Warehouses</p>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    {{-- @endif --}}
                    
                    <?php
                    $subMenu = [
                        'departments.index', 'departments.create', 'departments.edit',
                        'designations.index', 'designations.create', 'designations.edit',
                        'employee-statuses.index', 'employee-statuses.create','employee-statuses.edit',
                        'holidays.index', 'holidays.create', 'holidays.edit',
                        'shifts.index', 'shifts.create', 'shifts.edit',
                        'employees.index', 'employees.create',
                        'employees.edit',
                        'employees.show',
                        'leave-types.index', 'leave-types.create', 'leave-types.edit',
                        'leave.index', 'leave.create', 'leave.edit',
                        'employee-movements.index', 'employee-movements.create', 'employee-movements.edit',
                        'employees.attendance',
                        'report.attendance-sheet',
                        'report.attendance-summary',
                        'report.attendance-reminder',
                        'report.daily-attendance-sheet',
                        'hrm-settings.index',
                        'salary-processes.index',
                        'salary-processes.create',
                        'salary-processes.item_edit',
                        'salary-processes.edit',
                        'salary-processes.details',
                        'report.salary-sheet',
                    ];
                    ?>
                    <li class="nav-item {{ in_array(Route::currentRouteName(), $subMenu) ? 'menu-open' : '' }}">
                        <a href="#"
                        class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-cog"></i>
                            <p>
                                HRM & Payroll
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                             @include('layouts.menu.__hrm') 
                            @include('layouts.menu.__payroll')
                        </ul>
                    </li>
                    @include('layouts.menu.__monitoring')
                    @include('layouts.menu.__sr_management')
                    @include('layouts.menu.__trainee_management')
                    @include('layouts.menu.__purchase')
                    @include('layouts.menu.__stock_transfer')
                    @include('layouts.menu.__stock_conversion')
                    
                    @include('layouts.menu.__sales')
                    @include('layouts.partial.__ecommerce')
                    
                    @include('layouts.menu.__company_loan')
                    @include('layouts.menu.__product_utilize')
     
                    @include('layouts.menu.__microfinance')
                    @include('layouts.menu.__accounts')
                    @include('layouts.menu.__reports')
                    @include('layouts.menu.__card_management')
                    @include('layouts.menu.__shomriddhi_advance')
                @endif
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
