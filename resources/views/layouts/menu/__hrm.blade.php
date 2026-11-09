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
];
$settingsMenu = [
    'departments.index', 'departments.create', 'departments.edit',
    'designations.index', 'designations.create', 'designations.edit',
    'employee-statuses.index', 'employee-statuses.create','employee-statuses.edit',
    'holidays.index', 'holidays.create', 'holidays.edit',
    'shifts.index', 'shifts.create', 'shifts.edit',
    'leave-types.index', 'leave-types.create', 'leave-types.edit',
];
?>
@if(auth()->user()->can('hrm'))
    <li class="nav-item {{ in_array(Route::currentRouteName(), $subMenu) ? 'menu-open' : '' }}">
        <a href="#"
           class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
            <i class="nav-icon fas fa-user-cog"></i>
            <p>
                HRM
                <i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">

            @if(auth()->user()->role == 'Admin' )
                <li class="nav-item {{ in_array(Route::currentRouteName(), $settingsMenu) ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ in_array(Route::currentRouteName(), $settingsMenu) ? 'active custom-third-menu-bg' : '' }}">
                        <i class="nav-icon fas fa-cog"></i>
                        <p>
                            Settings
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview custom-third-layer">
                        <?php
                        $subSubMenu = ['departments.index', 'departments.create', 'departments.edit'];
                        ?>
                        @if(auth()->user()->can('department'))
                            <li class="nav-item">
                                <a href="{{ route('departments.index') }}"
                                   class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                    <i class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                    <p>Departments</p>
                                </a>
                            </li>
                        @endif

                        <?php
                        $subSubMenu = ['designations.index', 'designations.create', 'designations.edit'];
                        ?>
                        @if(auth()->user()->can('designation'))
                            <li class="nav-item">
                                <a href="{{ route('designations.index') }}"
                                   class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                    <i class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                    <p>Designations</p>
                                </a>
                            </li>
                        @endif

                        <?php
                        $subSubMenu = ['employee-statuses.index', 'employee-statuses.create', 'employee-statuses.edit'];
                        ?>
                        @if(auth()->user()->can('employee_status'))
                            <li class="nav-item">
                                <a href="{{ route('employee-statuses.index') }}"
                                   class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                    <i class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                    <p>Employee Status</p>
                                </a>
                            </li>
                        @endif

                        <?php
                        $subSubMenu = ['holidays.index', 'holidays.create', 'holidays.edit'];
                        ?>
                        @if(auth()->user()->can('holiday'))
                            <li class="nav-item">
                                <a href="{{ route('holidays.index') }}"
                                   class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                    <i class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                    <p>Holidays</p>
                                </a>
                            </li>
                        @endif

                        <?php
                        $subSubMenu = ['shifts.index', 'shifts.create', 'shifts.edit'];
                        ?>
                        @if(auth()->user()->can('shift'))
                            <li class="nav-item">
                                <a href="{{ route('shifts.index') }}"
                                   class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                    <i class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                    <p>Shifts</p>
                                </a>
                            </li>
                        @endif

                        <?php
                        $subSubMenu = ['leave-types.index', 'leave-types.create', 'leave-types.edit'];
                        ?>
                        @if(auth()->user()->can('leave_type'))
                            <li class="nav-item">
                                <a href="{{ route('leave-types.index') }}"
                                   class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                    <i class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                    <p>Leave Types</p>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            <?php
            $subSubMenu = [
                'employees.index', 'employees.create',
                'employees.edit',
                'employees.show'
            ];
            ?>
            @if(auth()->user()->can('employee'))
                <li class="nav-item">
                    <a href="{{ route('employees.index') }}"
                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                        <i class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                        <p>Employees</p>
                    </a>
                </li>
            @endif

            <?php
            $subSubMenu = ['leave.index', 'leave.create', 'leave.edit'];
            ?>
            @if(auth()->user()->can('leave'))
                <li class="nav-item">
                    <a href="{{ route('leave.index') }}"
                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                        <i class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                        <p>Leaves</p>
                    </a>
                </li>
            @endif
            <?php
            $subSubMenu = ['employee-movements.index', 'employee-movements.create', 'employee-movements.edit'];
            ?>
            @if(auth()->user()->can('employee_movement'))
                <li class="nav-item d-none">
                    <a href="{{ route('employee-movements.index') }}"
                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                        <i class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                        <p>Employee Movements</p>
                    </a>
                </li>
            @endif
            <?php
            $subSubMenu = ['employees.attendance'];
            ?>
            <li class="nav-item">
                <a href="{{ route('employees.attendance') }}"
                   class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                    <i class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>Employee Attendance</p>
                </a>
            </li>

            <?php
            $subSubMenu = [
                'report.attendance-sheet',
                'report.attendance-summary',
                'report.attendance-reminder',
                'report.daily-attendance-sheet',
            ];
            ?>

            @if(auth()->user()->can('hrm_reports'))
                <li class="nav-item {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'menu-open' : '' }}">
                    <a href="#"
                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active custom-third-menu-bg' : '' }}">
                        <i class="nav-icon fa fa-list"></i>
                        <p>
                            Reports
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview custom-third-layer">

                        @if(auth()->user()->can('attendance_sheet'))
                            <li class="nav-item">
                                <a href="{{ route('report.attendance-sheet') }}"
                                   class="nav-link {{ Route::currentRouteName() == 'report.attendance-sheet' ? 'active' : '' }}">
                                    <i class="far  {{ Route::currentRouteName() == 'report.attendance-sheet' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                    <p>Attendance Sheet</p>
                                </a>
                            </li>
                        @endif
                        @if(auth()->user()->can('attendance_summary'))
                            <li class="nav-item">
                                <a href="{{ route('report.attendance-summary') }}"
                                   class="nav-link {{ Route::currentRouteName() == 'report.attendance-summary' ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-chart-bar"></i>
                                    <p>Attendance Summary</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('report.attendance-reminder') }}"
                                   class="nav-link {{ Route::currentRouteName() == 'report.attendance-reminder' ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-bell"></i>
                                    <p>Attendance Reminder</p>
                                </a>
                            </li>
                        @endif

                    </ul>
                </li>
            @endif

            <li class="nav-item">
                <a href="{{ route('hrm-settings.index') }}"
                   class="nav-link {{ Route::currentRouteName() == 'hrm-settings.index' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-cogs"></i>
                    <p>HRM Settings</p>
                </a>
            </li>

        </ul>
    </li>
@endif
