<li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.75rem;">Access Management</li>
<li class="nav-item">
    <a href="{{ route('user_management.dashboard') }}" class="nav-link {{ Route::currentRouteName() == 'user_management.dashboard' ? 'active' : '' }}">
        <i class="nav-icon fas fa-tachometer-alt"></i>
        <p>Dashboard</p>
    </a>
</li>
@can('users')
    <li class="nav-item">
        <a href="{{ route('user.index') }}" class="nav-link {{ in_array($currentRoute, ['user.index', 'user.create', 'user.edit', 'user.permission']) ? 'active' : '' }}">
            <i class="nav-icon fas fa-users-cog"></i>
            <p>Users List</p>
        </a>
    </li>
@endcan

@can('role_management')
    <li class="nav-item">
        <a href="{{ route('role.index') }}" class="nav-link {{ in_array($currentRoute, ['role.index', 'role.create', 'role.edit']) ? 'active' : '' }}">
            <i class="nav-icon fas fa-user-shield"></i>
            <p>Role List</p>
        </a>
    </li>
@endcan

@can('users')
    <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-top: 10px;">User Reports</li>

    <li class="nav-item">
        <a href="{{ route('user.report') }}" class="nav-link {{ $currentRoute == 'user.report' ? 'active' : '' }}">
            <i class="nav-icon fas fa-file-invoice"></i>
            <p>Users Report</p>
        </a>
    </li>
@endcan
