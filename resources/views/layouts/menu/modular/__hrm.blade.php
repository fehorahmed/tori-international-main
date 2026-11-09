@php
    $currentRoute = Route::currentRouteName();
@endphp

@if(auth()->user()->can('hrm'))
    <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px;">
        HRM Module
    </li>
    <!-- Dashboard -->
    <li class="nav-item">
        <a href="{{ route('hrm.dashboard') }}" class="nav-link {{ Route::currentRouteName() == 'hrm.dashboard' ? 'active' : '' }}">
            <i class="nav-icon fas fa-chart-pie"></i>
            <p>Dashboard</p>
        </a>
    </li>

    <!-- Category 1: HRM Configurations -->
        <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px; margin-top: 10px;">
            HRM Configurations
        </li>

        <!-- 1. Departments -->
        @if(auth()->user()->can('department'))
            <li class="nav-item">
                <a href="{{ route('departments.index') }}" 
                   class="nav-link {{ in_array($currentRoute, ['departments.index', 'departments.create', 'departments.edit']) ? 'active' : '' }}">
                    <i class="nav-icon fas fa-building"></i>
                    <p>Departments</p>
                </a>
            </li>
        @endif

        <!-- 2. Designations -->
        @if(auth()->user()->can('designation'))
            <li class="nav-item">
                <a href="{{ route('designations.index') }}" 
                   class="nav-link {{ in_array($currentRoute, ['designations.index', 'designations.create', 'designations.edit']) ? 'active' : '' }}">
                    <i class="nav-icon fas fa-id-badge"></i>
                    <p>Designations</p>
                </a>
            </li>
        @endif

        <!-- 3. Employee Status -->
        @if(auth()->user()->can('employee_status'))
            <li class="nav-item">
                <a href="{{ route('employee-statuses.index') }}" 
                   class="nav-link {{ in_array($currentRoute, ['employee-statuses.index', 'employee-statuses.create', 'employee-statuses.edit']) ? 'active' : '' }}">
                    <i class="nav-icon fas fa-toggle-on"></i>
                    <p>Employee Status</p>
                </a>
            </li>
        @endif

        <!-- 4. Holidays -->
        @if(auth()->user()->can('holiday'))
            <li class="nav-item">
                <a href="{{ route('holidays.index') }}" 
                   class="nav-link {{ in_array($currentRoute, ['holidays.index', 'holidays.create', 'holidays.edit']) ? 'active' : '' }}">
                    <i class="nav-icon fas fa-calendar-day"></i>
                    <p>Holidays</p>
                </a>
            </li>
        @endif

        <!-- 5. Shifts -->
        @if(auth()->user()->can('shift'))
            <li class="nav-item">
                <a href="{{ route('shifts.index') }}" 
                   class="nav-link {{ in_array($currentRoute, ['shifts.index', 'shifts.create', 'shifts.edit']) ? 'active' : '' }}">
                    <i class="nav-icon fas fa-clock"></i>
                    <p>Shifts</p>
                </a>
            </li>
        @endif

        <!-- 6. Leave Types -->
        @if(auth()->user()->can('leave_type'))
            <li class="nav-item">
                <a href="{{ route('leave-types.index') }}" 
                   class="nav-link {{ in_array($currentRoute, ['leave-types.index', 'leave-types.create', 'leave-types.edit']) ? 'active' : '' }}">
                    <i class="nav-icon fas fa-envelope-open-text"></i>
                    <p>Leave Types</p>
                </a>
            </li>
        @endif

    <!-- Category 2: Operations -->
    <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px; margin-top: 15px;">
        Operations
    </li>

    <!-- 7. Employees -->
    @if(auth()->user()->can('employee'))
        <li class="nav-item">
            <a href="{{ route('employees.index') }}" 
               class="nav-link {{ in_array($currentRoute, ['employees.index', 'employees.create', 'employees.edit', 'employees.show']) ? 'active' : '' }}">
                <i class="nav-icon fas fa-user-friends"></i>
                <p>Employees List</p>
            </a>
        </li>
    @endif

    <!-- 8. Leaves -->
    @if(auth()->user()->can('leave'))
        <li class="nav-item">
            <a href="{{ route('leave.index') }}" 
               class="nav-link {{ in_array($currentRoute, ['leave.index', 'leave.create', 'leave.edit']) ? 'active' : '' }}">
                <i class="nav-icon fas fa-plane-departure"></i>
                <p>Leaves Management</p>
            </a>
        </li>
    @endif

    <!-- 9. Employee Attendance -->
    @can('employee_attendance')
    <li class="nav-item">
        <a href="{{ route('employees.attendance') }}" 
           class="nav-link {{ $currentRoute == 'employees.attendance' ? 'active' : '' }}">
            <i class="nav-icon fas fa-fingerprint"></i>
            <p>Employee Attendance</p>
        </a>
    </li>
    @endcan

    @can('hrm_settings')
    <!-- Category 3: Settings -->
    <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px; margin-top: 15px;">
        Settings
    </li>
        <li class="nav-item">
            <a href="{{ route('hrm-settings.index') }}" 
                class="nav-link {{ $currentRoute == 'hrm-settings.index' ? 'active' : '' }}">
                <i class="nav-icon fas fa-cogs"></i>
                <p>HRM Settings</p>
            </a>
        </li>
    @endcan

    <!-- Category 4: Attendance Reports -->
    @if(auth()->user()->can('hrm_reports'))
        <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px; margin-top: 15px;">
            Reports
        </li>

        @can('department_report')
            <li class="nav-item">
                <a href="{{ route('report.department') }}" 
                   class="nav-link {{ $currentRoute == 'report.department' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-building"></i>
                    <p>Department Report</p>
                </a>
            </li>
        @endcan

        @can('designation_report')
            <li class="nav-item">
                <a href="{{ route('report.designation') }}" 
                   class="nav-link {{ $currentRoute == 'report.designation' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-id-badge"></i>
                    <p>Designation Report</p>
                </a>
            </li>
        @endcan

        @can('holiday_report')
            <li class="nav-item">
                <a href="{{ route('report.holiday') }}" 
                   class="nav-link {{ $currentRoute == 'report.holiday' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-calendar-day"></i>
                    <p>Holiday Report</p>
                </a>
            </li>
        @endcan

        @can('employee_report')
            <li class="nav-item">
                <a href="{{ route('report.employee') }}" 
                   class="nav-link {{ $currentRoute == 'report.employee' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-user-friends"></i>
                    <p>Employee Report</p>
                </a>
            </li>
        @endcan

        <!-- 10. Attendance Sheet -->
        @if(auth()->user()->can('attendance_sheet'))
            <li class="nav-item">
                <a href="{{ route('report.attendance-sheet') }}" 
                   class="nav-link {{ $currentRoute == 'report.attendance-sheet' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-file-invoice"></i>
                    <p>Attendance Sheet</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('report.daily-attendance-sheet') }}" 
                   class="nav-link {{ $currentRoute == 'report.daily-attendance-sheet' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-calendar-day"></i>
                    <p>Daily Attendance Sheet</p>
                </a>
            </li>
        @endif

        <!-- 11. Attendance Summary -->
        @if(auth()->user()->can('attendance_summary'))
            <li class="nav-item">
                <a href="{{ route('report.attendance-summary') }}" 
                   class="nav-link {{ $currentRoute == 'report.attendance-summary' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-chart-bar"></i>
                    <p>Attendance Summary</p>
                </a>
            </li>
            @can('attendance_reminder')
                <li class="nav-item">
                    <a href="{{ route('report.attendance-reminder') }}" 
                       class="nav-link {{ $currentRoute == 'report.attendance-reminder' ? 'active' : '' }}">
                        <i class="nav-icon fas fa-bell"></i>
                        <p>Attendance Reminder</p>
                    </a>
                </li>
            @endcan
        @endif



        <!-- Live Attendance Report -->
        @can('live_attendance')
            <li class="nav-item">
                <a href="{{ route('report.live_attendance') }}"
                   class="nav-link {{ $currentRoute == 'report.live_attendance' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-fingerprint"></i>
                    <p>Attendance Live Report</p>
                </a>
            </li>
        @endcan


    @endif
@endif
