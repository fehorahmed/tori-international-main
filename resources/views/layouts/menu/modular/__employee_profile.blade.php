@php
    $currentRoute = Route::currentRouteName();
@endphp

@if(auth()->user()->employee_id)
    <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px;">
        My Self Service
    </li>

    <li class="nav-item">
        <a href="{{ route('employee_profile.dashboard') }}" class="nav-link {{ $currentRoute == 'employee_profile.dashboard' ? 'active' : '' }}">
            <i class="nav-icon fas fa-id-badge"></i>
            <p>Dashboard</p>
        </a>
    </li>

    <li class="nav-item">
        <a href="{{ route('employee_profile.profile.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'employee_profile.profile') ? 'active' : '' }}">
            <i class="nav-icon fas fa-user-circle"></i>
            <p>My Profile</p>
        </a>
    </li>

    <li class="nav-item">
        <a href="{{ route('employee_profile.payroll.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'employee_profile.payroll') ? 'active' : '' }}">
            <i class="nav-icon fas fa-wallet"></i>
            <p>My Financials</p>
        </a>
    </li>

    <li class="nav-item">
        <a href="{{ route('employee_profile.leave.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'employee_profile.leave') ? 'active' : '' }}">
            <i class="nav-icon fas fa-calendar-alt"></i>
            <p>Attendance & Leave</p>
        </a>
    </li>
@endif
