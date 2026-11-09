<li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.75rem;">Manager Management</li>

@can('manager_management')
    <li class="nav-item">
        <a href="{{ route('manager_management.dashboard') }}" class="nav-link {{ Route::currentRouteName() == 'manager_management.dashboard' ? 'active' : '' }}">
            <i class="nav-icon fas fa-chart-pie"></i>
            <p>Dashboard</p>
        </a>
    </li>

    @can('area_manager')
        <li class="nav-item">
            <a href="{{ route('area-manager.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'area-manager.') && !str_starts_with($currentRoute, 'area-manager-assignments.') ? 'active' : '' }}">
                <i class="nav-icon fas fa-user-tie"></i>
                <p>Area Manager</p>
            </a>
        </li>
    @endcan

    @can('branch_manager')
        <li class="nav-item">
            <a href="{{ route('branch-manager.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'branch-manager.') && !str_starts_with($currentRoute, 'branch-manager-assignments.') ? 'active' : '' }}">
                <i class="nav-icon fas fa-user-friends"></i>
                <p>Branch Manager</p>
            </a>
        </li>
    @endcan

    @can('area_manager_assignments')
        <li class="nav-item">
            <a href="{{ route('area-manager-assignments.index') }}" class="nav-link {{ request()->is('admin/area-manager-assignments*') || (request()->is('admin/manager-assignments*') && request('type') == 'Area_Manager') ? 'active' : '' }}">
                <i class="nav-icon fas fa-link"></i>
                <p>AM Assignments</p>
            </a>
        </li>
    @endcan

    @can('branch_manager_assignments')
        <li class="nav-item">
            <a href="{{ route('branch-manager-assignments.index') }}" class="nav-link {{ request()->is('admin/branch-manager-assignments*') || (request()->is('admin/manager-assignments*') && request('type') == 'Branch_Manager') ? 'active' : '' }}">
                <i class="nav-icon fas fa-link"></i>
                <p>BM Assignments</p>
            </a>
        </li>
    @endcan

    @can('area_manager_targets')
        <li class="nav-item">
            <a href="{{ route('tasks.index', 'area_manager') }}" class="nav-link {{ request()->is('admin/tasks/area_manager*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-bullseye"></i>
                <p>AM Targets</p>
            </a>
        </li>
    @endcan

    @can('branch_manager_targets')
        <li class="nav-item">
            <a href="{{ route('tasks.index', 'branch_manager') }}" class="nav-link {{ request()->is('admin/tasks/branch_manager*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-bullseye"></i>
                <p>BM Targets</p>
            </a>
        </li>
    @endcan

@endcan

<!-- Manager Reports -->
@can('manager_management_reports')
    <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px; margin-top: 15px;">
        Manager Reports
    </li>
    
    @can('area_manager_list_report')
    <li class="nav-item">
        <a href="{{ route('report.area_manager') }}"
            class="nav-link {{ $currentRoute == 'report.area_manager' ? 'active' : '' }}">
            <i class="nav-icon fas fa-list"></i>
            <p>Area Manager List</p>
        </a>
    </li>
    @endcan

    @can('branch_manager_list_report')
    <li class="nav-item">
        <a href="{{ route('report.branch_manager') }}"
            class="nav-link {{ $currentRoute == 'report.branch_manager' ? 'active' : '' }}">
            <i class="nav-icon fas fa-list"></i>
            <p>Branch Manager List</p>
        </a>
    </li>
    @endcan

    @can('area_manager_history_report')
    <li class="nav-item">
        <a href="{{ route('report.area_manager_history') }}"
            class="nav-link {{ $currentRoute == 'report.area_manager_history' ? 'active' : '' }}">
            <i class="nav-icon fas fa-user-tie"></i>
            <p>Area Manager History</p>
        </a>
    </li>
    @endcan

    @can('branch_manager_history_report')
    <li class="nav-item">
        <a href="{{ route('report.branch_manager_history') }}"
            class="nav-link {{ $currentRoute == 'report.branch_manager_history' ? 'active' : '' }}">
            <i class="nav-icon fas fa-history"></i>
            <p>Branch Manager History</p>
        </a>
    </li>
    @endcan
@endcan
