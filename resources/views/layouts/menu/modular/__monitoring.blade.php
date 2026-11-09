@php
    $currentRoute = Route::currentRouteName();
@endphp

@if(auth()->user()->can('live_location') || auth()->user()->can('location_history'))
    <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px;">
        Live Tracking
    </li>

    <!-- 0. Dashboard -->
    <li class="nav-item">
        <a href="{{ route('tracking.dashboard') }}"
           class="nav-link {{ $currentRoute == 'tracking.dashboard' ? 'active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
        </a>
    </li>
@endif

<!-- 1. Live Location -->
@if(auth()->user()->can('live_location'))
    <li class="nav-item">
        <a href="{{ route('tracking.live_location') }}"
           class="nav-link {{ $currentRoute == 'tracking.live_location' ? 'active' : '' }}">
            <i class="nav-icon fas fa-map-marker-alt"></i>
            <p>Live Location</p>
        </a>
    </li>
@endif

<!-- 2. Location History -->
@if(auth()->user()->can('location_history'))
    <li class="nav-item">
        <a href="{{ route('tracking.location_history') }}"
           class="nav-link {{ $currentRoute == 'tracking.location_history' ? 'active' : '' }}">
            <i class="nav-icon fas fa-history"></i>
            <p>Location History</p>
        </a>
    </li>
@endif
