@php
    $currentRoute = Route::currentRouteName();
@endphp

@if(auth()->user()->hasPermissionTo('sms_dashboard') || auth()->user()->hasPermissionTo('sms_gateways') || auth()->user()->hasPermissionTo('sms_templates') || auth()->user()->hasPermissionTo('sms_campaign') || auth()->user()->hasPermissionTo('sms_logs'))
    <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px; margin-top: 10px;">
        SMS Management
    </li>

    @if(auth()->user()->hasPermissionTo('sms_dashboard'))
        <li class="nav-item">
            <a href="{{ route('sms_management.dashboard') }}"
               class="nav-link {{ $currentRoute == 'sms_management.dashboard' ? 'active' : '' }}">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>Dashboard</p>
            </a>
        </li>
    @endif

    @if(auth()->user()->hasPermissionTo('sms_campaign'))
        <li class="nav-item">
            <a href="{{ route('sms_management.campaign.index') }}"
               class="nav-link {{ $currentRoute == 'sms_management.campaign.index' ? 'active' : '' }}">
                <i class="nav-icon fas fa-bullhorn"></i>
                <p>Bulk Campaign</p>
            </a>
        </li>
    @endif

    @if(auth()->user()->hasPermissionTo('sms_logs'))
        <li class="nav-item">
            <a href="{{ route('sms_management.logs.index') }}"
               class="nav-link {{ $currentRoute == 'sms_management.logs.index' ? 'active' : '' }}">
                <i class="nav-icon fas fa-history"></i>
                <p>SMS Logs</p>
            </a>
        </li>
    @endif

    @if(auth()->user()->hasPermissionTo('sms_templates'))
        <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px; margin-top: 15px;">
            Settings
        </li>

        <li class="nav-item">
            <a href="{{ route('sms_management.templates.index') }}"
               class="nav-link {{ $currentRoute == 'sms_management.templates.index' ? 'active' : '' }}">
                <i class="nav-icon fas fa-file-alt"></i>
                <p>SMS Templates</p>
            </a>
        </li>
    @endif

    @if(auth()->user()->hasPermissionTo('sms_gateways'))
        <li class="nav-item">
            <a href="{{ route('sms_management.gateways.index') }}"
               class="nav-link {{ $currentRoute == 'sms_management.gateways.index' ? 'active' : '' }}">
                <i class="nav-icon fas fa-satellite-dish"></i>
                <p>Gateway Settings</p>
            </a>
        </li>
    @endif
@endif
