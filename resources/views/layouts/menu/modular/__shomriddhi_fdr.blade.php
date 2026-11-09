@if(auth()->user()->can('shomriddhi_fdr'))
<li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.75rem;">Shomriddhi FDR</li>

<li class="nav-item">
    <a href="{{ route('shomriddhi-fdr.dashboard') }}" class="nav-link {{ request()->routeIs('shomriddhi-fdr.dashboard') ? 'active' : '' }}">
        <i class="fas fa-tachometer-alt nav-icon"></i>
        <p>Dashboard</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('shomriddhi-fdr.accounts.index') }}" class="nav-link {{ request()->routeIs('shomriddhi-fdr.accounts.*') ? 'active' : '' }}">
        <i class="fas fa-file-invoice-dollar nav-icon"></i>
        <p>FDR Accounts</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('shomriddhi-fdr.transactions.index') }}" class="nav-link {{ request()->routeIs('shomriddhi-fdr.transactions.*') ? 'active' : '' }}">
        <i class="fas fa-exchange-alt nav-icon"></i>
        <p>Transactions</p>
    </a>
</li>

@if(auth()->user()->can('shomriddhi_fdr_reports'))
<li class="nav-header text-uppercase font-weight-bold text-muted mt-2" style="font-size: 0.75rem;">Reports</li>
@if(auth()->user()->can('shomriddhi_fdr_maturity_report'))
<li class="nav-item">
    <a href="{{ route('shomriddhi-fdr.reports.maturity') }}" class="nav-link {{ request()->routeIs('shomriddhi-fdr.reports.maturity') ? 'active' : '' }}">
        <i class="fas fa-calendar-alt nav-icon"></i>
        <p>Maturity Report</p>
    </a>
</li>
@endif
@if(auth()->user()->can('shomriddhi_fdr_transaction_report'))
<li class="nav-item">
    <a href="{{ route('shomriddhi-fdr.reports.transaction') }}" class="nav-link {{ request()->routeIs('shomriddhi-fdr.reports.transaction') ? 'active' : '' }}">
        <i class="fas fa-list-alt nav-icon"></i>
        <p>Transaction Report</p>
    </a>
</li>
@endif
@if(auth()->user()->can('shomriddhi_fdr_ledger_report'))
<li class="nav-item">
    <a href="{{ route('shomriddhi-fdr.reports.ledger') }}" class="nav-link {{ request()->routeIs('shomriddhi-fdr.reports.ledger') ? 'active' : '' }}">
        <i class="fas fa-file-invoice nav-icon"></i>
        <p>Account Ledger</p>
    </a>
</li>
@endif
@if(auth()->user()->can('shomriddhi_fdr_active_accounts_report'))
<li class="nav-item">
    <a href="{{ route('shomriddhi-fdr.reports.active-accounts') }}" class="nav-link {{ request()->routeIs('shomriddhi-fdr.reports.active-accounts') ? 'active' : '' }}">
        <i class="fas fa-check-circle nav-icon"></i>
        <p>Active Accounts</p>
    </a>
</li>
@endif
@if(auth()->user()->can('shomriddhi_fdr_closed_accounts_report'))
<li class="nav-item">
    <a href="{{ route('shomriddhi-fdr.reports.closed-accounts') }}" class="nav-link {{ request()->routeIs('shomriddhi-fdr.reports.closed-accounts') ? 'active' : '' }}">
        <i class="fas fa-times-circle nav-icon"></i>
        <p>Closed Accounts</p>
    </a>
</li>
@endif
@if(auth()->user()->can('shomriddhi_fdr_scheme_summary_report'))
<li class="nav-item">
    <a href="{{ route('shomriddhi-fdr.reports.scheme-summary') }}" class="nav-link {{ request()->routeIs('shomriddhi-fdr.reports.scheme-summary') ? 'active' : '' }}">
        <i class="fas fa-chart-pie nav-icon"></i>
        <p>Scheme Summary</p>
    </a>
</li>
@endif
@endif

<li class="nav-header text-uppercase font-weight-bold text-muted mt-2" style="font-size: 0.75rem;">Settings</li>
<li class="nav-item">
    <a href="{{ route('shomriddhi-fdr.schemes.index') }}" class="nav-link {{ request()->routeIs('shomriddhi-fdr.schemes.*') ? 'active' : '' }}">
        <i class="fas fa-layer-group nav-icon"></i>
        <p>FDR Schemes</p>
    </a>
</li>

@endif
