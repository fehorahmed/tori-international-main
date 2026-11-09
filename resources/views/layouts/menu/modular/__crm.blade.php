<li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.75rem;">CRM Module</li>
<li class="nav-item">
    <a href="{{ route('crm.dashboard') }}" class="nav-link {{ Route::currentRouteName() == 'crm.dashboard' ? 'active' : '' }}">
        <i class="nav-icon fas fa-chart-pie"></i>
        <p>Dashboard</p>
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('leads.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'leads.') ? 'active' : '' }}">
        <i class="nav-icon fas fa-funnel-dollar"></i>
        <p>Outlet Setup List</p>
    </a>
</li>

@if(auth()->user()->can('customer_type'))
<li class="nav-item">
    <a href="{{ route('customer-types.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'customer-types.') ? 'active' : '' }}">
        <i class="nav-icon fas fa-tags"></i>
        <p>Outlet Type</p>
    </a>
</li>
@endif
@if(auth()->user()->can('manage_markets'))
<li class="nav-item">
    <a href="{{ route('markets.index') }}"
       class="nav-link {{ in_array($currentRoute, ['markets.index', 'markets.create', 'markets.edit']) ? 'active' : '' }}">
        <i class="nav-icon fas fa-store"></i>
        <p>Market / Area</p>
    </a>
</li>
@endif
@if(auth()->user()->can('leads_report'))
    <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px; margin-top: 15px;">
        CRM Reports
    </li>
    <li class="nav-item">
        <a href="{{ route('report.leads') }}" class="nav-link {{ $currentRoute == 'report.leads' ? 'active' : '' }}">
            <i class="nav-icon fas fa-filter"></i>
            <p>Outlet Setup Report</p>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('report.outlet_types') }}" class="nav-link {{ $currentRoute == 'report.outlet_types' ? 'active' : '' }}">
            <i class="nav-icon fas fa-tags"></i>
            <p>Outlet Type Report</p>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('report.markets') }}" class="nav-link {{ $currentRoute == 'report.markets' ? 'active' : '' }}">
            <i class="nav-icon fas fa-store"></i>
            <p>Market Report</p>
        </a>
    </li>
@endif
