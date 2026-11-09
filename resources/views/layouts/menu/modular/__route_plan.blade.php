@php
    $currentRoute = Route::currentRouteName();
@endphp

@if(auth()->user()->can('route_plan_management'))
    <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px;">
        Route Plan
    </li>

    <!-- Dashboard -->
    <li class="nav-item">
        <a href="{{ route('route_plan.dashboard') }}"
           class="nav-link {{ $currentRoute == 'route_plan.dashboard' ? 'active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
        </a>
    </li>


    <!-- 2. Route Plans -->
    @if(auth()->user()->can('route_plans'))
    <li class="nav-item">
        <a href="{{ route('route-plans.index') }}"
           class="nav-link {{ in_array($currentRoute, ['route-plans.index', 'route-plans.create', 'route-plans.edit']) ? 'active' : '' }}">
            <i class="nav-icon fas fa-map-signs"></i>
            <p>Route Plans</p>
        </a>
    </li>
    @endif

    @if(auth()->user()->can('route_plan_reports'))
    <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px; margin-top: 15px;">
        Reports
    </li>
    <li class="nav-item">
        <a href="{{ route('report.route_plans') }}" class="nav-link {{ $currentRoute == 'report.route_plans' ? 'active' : '' }}">
            <i class="nav-icon fas fa-calendar-alt"></i>
            <p>Weekly Route Plans</p>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('report.route_leads') }}" class="nav-link {{ $currentRoute == 'report.route_leads' ? 'active' : '' }}">
            <i class="nav-icon fas fa-list-ul"></i>
            <p>Route-wise Leads</p>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('report.route_coverage') }}" class="nav-link {{ $currentRoute == 'report.route_coverage' ? 'active' : '' }}">
            <i class="nav-icon fas fa-chart-line"></i>
            <p>SR Route Coverage</p>
        </a>
    </li>
    @endif
@endif

