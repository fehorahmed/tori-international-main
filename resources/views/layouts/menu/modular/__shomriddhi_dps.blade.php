@if(auth()->user()->can('shomriddhi_dps'))
<li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.75rem;">Shomriddhi DPS</li>

@if(auth()->user()->can('shomriddhi_dps_dashboard'))
<li class="nav-item">
    <a href="{{ route('shomriddhi-dps.dashboard') }}" class="nav-link {{ request()->routeIs('shomriddhi-dps.dashboard') ? 'active' : '' }}">
        <i class="fas fa-tachometer-alt nav-icon"></i>
        <p>Dashboard</p>
    </a>
</li>
@endif

@if(auth()->user()->can('shomriddhi_dps_accounts'))
<li class="nav-item">
    <a href="{{ route('shomriddhi-dps.accounts.index') }}" class="nav-link {{ request()->routeIs('shomriddhi-dps.accounts.*') ? 'active' : '' }}">
        <i class="fas fa-piggy-bank nav-icon"></i>
        <p>DPS Accounts</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('shomriddhi-dps.transactions.create') }}" class="nav-link {{ request()->routeIs('shomriddhi-dps.transactions.create') ? 'active' : '' }}">
        <i class="fas fa-hand-holding-usd nav-icon"></i>
        <p>Collect Installment</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('shomriddhi-dps.transactions.index') }}" class="nav-link {{ request()->routeIs('shomriddhi-dps.transactions.index') ? 'active' : '' }}">
        <i class="fas fa-exchange-alt nav-icon"></i>
        <p>Collection History</p>
    </a>
</li>
@endif

@if(auth()->user()->can('shomriddhi_dps_reports'))
<li class="nav-header text-uppercase font-weight-bold text-muted mt-2" style="font-size: 0.75rem;">Reports</li>

<li class="nav-item">
    <a href="{{ route('shomriddhi-dps.reports.active-accounts') }}" class="nav-link {{ request()->routeIs('shomriddhi-dps.reports.active-accounts') ? 'active' : '' }}">
        <i class="fas fa-check-circle nav-icon"></i>
        <p>Active Accounts</p>
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('shomriddhi-dps.reports.matured-accounts') }}" class="nav-link {{ request()->routeIs('shomriddhi-dps.reports.matured-accounts') ? 'active' : '' }}">
        <i class="fas fa-calendar-check nav-icon"></i>
        <p>Matured Accounts</p>
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('shomriddhi-dps.reports.defaulted-accounts') }}" class="nav-link {{ request()->routeIs('shomriddhi-dps.reports.defaulted-accounts') ? 'active' : '' }}">
        <i class="fas fa-exclamation-triangle nav-icon"></i>
        <p>Defaulted Accounts</p>
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('shomriddhi-dps.reports.transaction') }}" class="nav-link {{ request()->routeIs('shomriddhi-dps.reports.transaction') ? 'active' : '' }}">
        <i class="fas fa-list-alt nav-icon"></i>
        <p>Transaction Report</p>
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('shomriddhi-dps.reports.ledger') }}" class="nav-link {{ request()->routeIs('shomriddhi-dps.reports.ledger') ? 'active' : '' }}">
        <i class="fas fa-file-invoice nav-icon"></i>
        <p>Account Ledger</p>
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('shomriddhi-dps.reports.scheme-summary') }}" class="nav-link {{ request()->routeIs('shomriddhi-dps.reports.scheme-summary') ? 'active' : '' }}">
        <i class="fas fa-chart-pie nav-icon"></i>
        <p>Scheme Summary</p>
    </a>
</li>
@endif

@if(auth()->user()->can('shomriddhi_dps_schemes'))
<li class="nav-header text-uppercase font-weight-bold text-muted mt-2" style="font-size: 0.75rem;">Settings</li>
<li class="nav-item">
    <a href="{{ route('shomriddhi-dps.schemes.index') }}" class="nav-link {{ request()->routeIs('shomriddhi-dps.schemes.*') ? 'active' : '' }}">
        <i class="fas fa-layer-group nav-icon"></i>
        <p>DPS Schemes</p>
    </a>
</li>
@endif

@endif
