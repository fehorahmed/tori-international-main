@php
    $currentRoute = Route::currentRouteName();
@endphp

@if(auth()->user()->can('trainee_management'))
<li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px;">
    Trainee Operations
</li>

<!-- Dashboard -->
<li class="nav-item">
    <a href="{{ route('trainee_management.dashboard') }}"
       class="nav-link {{ $currentRoute == 'trainee_management.dashboard' ? 'active' : '' }}">
        <i class="nav-icon fas fa-chart-line"></i>
        <p class="font-weight-bold">Dashboard</p>
    </a>
</li>

<!-- 1. Trainee List -->
@if(auth()->user()->can('trainee'))
<li class="nav-item">
    <a href="{{ route('trainee.index') }}"
       class="nav-link {{ request()->is('admin/trainee') || request()->is('admin/trainee/*/edit') || request()->is('admin/trainee/create') ? 'active' : '' }}">
        <i class="nav-icon fas fa-graduation-cap"></i>
        <p>Trainee List</p>
    </a>
</li>
@endif

<!-- 2. Trainee Tasks -->
@if(auth()->user()->can('trainee_tasks'))
<li class="nav-item">
    <a href="{{ route('trainee.tasks.index') }}"
       class="nav-link {{ request()->is('admin/trainee/tasks*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-tasks"></i>
        <p>Trainee Tasks</p>
    </a>
</li>
@endif

<!-- 3. Visit Types -->
@if(auth()->user()->can('trainee_visits'))
<li class="nav-item">
    <a href="{{ route('trainee.visit_type.index') }}"
       class="nav-link {{ request()->is('admin/trainee/visit-type*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-cogs"></i>
        <p>Visit Types</p>
    </a>
</li>
@endif

<!-- 4. Visit Logs -->
@if(auth()->user()->can('trainee_visits'))
<li class="nav-item">
    <a href="{{ route('trainee.visits.index') }}"
       class="nav-link {{ request()->is('admin/trainee/visits*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-clipboard-list"></i>
        <p>Visit Logs</p>
    </a>
</li>
@endif

<!-- Category 2: Trainee Reports -->
@if(auth()->user()->can('trainee_reports'))
    <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px; margin-top: 15px;">
        Trainee Reports
    </li>

    <li class="nav-item">
        <a href="{{ route('report.trainee_visits') }}"
           class="nav-link {{ $currentRoute == 'report.trainee_visits' ? 'active' : '' }}">
            <i class="nav-icon fas fa-clipboard-list"></i>
            <p>Trainee Visits Report</p>
        </a>
    </li>

    <li class="nav-item">
        <a href="{{ route('report.trainee_performance') }}"
           class="nav-link {{ $currentRoute == 'report.trainee_performance' ? 'active' : '' }}">
            <i class="nav-icon fas fa-graduation-cap"></i>
            <p>Trainee Performance</p>
        </a>
    </li>

    <li class="nav-item">
        <a href="{{ route('report.trainee_performance_summary') }}"
           class="nav-link {{ $currentRoute == 'report.trainee_performance_summary' ? 'active' : '' }}">
            <i class="nav-icon fas fa-user-check"></i>
            <p>Trainee Per. Summary</p>
        </a>
    </li>
@endif
@endif
