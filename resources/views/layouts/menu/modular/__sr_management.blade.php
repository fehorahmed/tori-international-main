@php
    $currentRoute = Route::currentRouteName();
@endphp

@if(auth()->user()->can('sr_management'))
    <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px;">
        SR Operations
    </li>

    <!-- Dashboard -->
    <li class="nav-item">
        <a href="{{ route('sr_management.dashboard') }}"
           class="nav-link {{ Route::currentRouteName() == 'sr_management.dashboard' ? 'active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
        </a>
    </li>

    <!-- 1. SRs -->
    @if(auth()->user()->can('sr'))
        <li class="nav-item">
            <a href="{{ route('sr.index') }}"
               class="nav-link {{ in_array($currentRoute, ['sr.index', 'sr.create', 'sr.edit', 'sr.show']) ? 'active' : '' }}">
                <i class="nav-icon fas fa-user-friends"></i>
                <p>SRs List</p>
            </a>
        </li>
    @endif

    <!-- 2. SR Tasks -->
    @if(auth()->user()->can('sr_tasks'))
        <li class="nav-item">
            <a href="{{ route('tasks.index', 'sr') }}"
               class="nav-link {{ request()->is('admin/tasks/sr*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-tasks"></i>
                <p>SR Tasks</p>
            </a>
        </li>
    @endif

    <!-- 2.5 SR Lead Collection -->
    @if(auth()->user()->can('sr_lead_collection'))
        <li class="nav-item d-none">
            <a href="{{ route('sr_lead_collection.index') }}"
               class="nav-link {{ in_array($currentRoute, ['sr_lead_collection.index', 'sr_lead_collection.create', 'sr_lead_collection.edit']) ? 'active' : '' }}">
                <i class="nav-icon fas fa-bullhorn"></i>
                <p>SR Lead Collection</p>
            </a>
        </li>
    @endif

    <!-- 3. SR Route Plans -->
    @if(auth()->user()->can('sr_route_plan'))
        <li class="nav-item">
            <a href="{{ route('sr_route_plans.index') }}"
               class="nav-link {{ in_array($currentRoute, ['sr_route_plans.index']) ? 'active' : '' }}">
                <i class="nav-icon fas fa-route"></i>
                <p>SR Route Plans</p>
            </a>
        </li>
    @endif

    <!-- 4. SR Visits -->
    @if(auth()->user()->can('sr_visits'))
        <li class="nav-item">
            <a href="{{ route('sr-visits.index') }}"
               class="nav-link {{ in_array($currentRoute, ['sr-visits.index', 'sr-visits.create', 'sr-visits.show']) ? 'active' : '' }}">
                <i class="nav-icon fas fa-map-marked-alt"></i>
                <p>SR Visits</p>
            </a>
        </li>
    @endif

    <!-- 5. Social Engagements -->
    @if(auth()->user()->can('sr_visits'))
        <li class="nav-item">
            <a href="{{ route('sr_social.index') }}"
               class="nav-link {{ in_array($currentRoute, ['sr_social.index', 'sr_social.create']) ? 'active' : '' }}">
                <i class="nav-icon fas fa-thumbs-up"></i>
                <p>Social Engagements</p>
            </a>
        </li>
    @endif

    <!-- Category 2: SR Reports -->
    @if(auth()->user()->can('sr_reports'))
        <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px; margin-top: 15px;">
            SR Reports
        </li>

        @if(auth()->user()->can('sr_performance_report'))
            <li class="nav-item">
                <a href="{{ route('report.sr_performance') }}"
                   class="nav-link {{ $currentRoute == 'report.sr_performance' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-medal"></i>
                    <p>SR Performance</p>
                </a>
            </li>
        @endif

        @if(auth()->user()->can('sr_performance_summary_report'))
            <li class="nav-item">
                <a href="{{ route('report.sr_performance_summary') }}"
                   class="nav-link {{ $currentRoute == 'report.sr_performance_summary' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-poll-h"></i>
                    <p>SR Per. Summary</p>
                </a>
            </li>
        @endif

        @if(auth()->user()->can('sr_tasks_report'))
            <li class="nav-item">
                <a href="{{ route('report.sr_tasks') }}"
                   class="nav-link {{ $currentRoute == 'report.sr_tasks' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-tasks"></i>
                    <p>SR Tasks Report</p>
                </a>
            </li>
        @endif

        @if(auth()->user()->can('sr_visits_report'))
            <li class="nav-item">
                <a href="{{ route('report.sr_visits') }}"
                   class="nav-link {{ $currentRoute == 'report.sr_visits' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-map-marked-alt"></i>
                    <p>SR Visits Report</p>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="{{ route('report.sr_social_engagements') }}"
                   class="nav-link {{ $currentRoute == 'report.sr_social_engagements' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-thumbs-up"></i>
                    <p>Social Engage Report</p>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="{{ route('report.sr_leads') }}"
                   class="nav-link {{ $currentRoute == 'report.sr_leads' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-bullhorn"></i>
                    <p>SR Leads Report</p>
                </a>
            </li>
        @endif
    @endif
@endif
