@if(auth()->user()->can('shomriddhi_advance'))
<li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.75rem;">Shomriddhi Advance</li>

@if(auth()->user()->can('shomriddhi_advance_dashboard'))
<li class="nav-item">
    <a href="{{ route('shomriddhi-advance.dashboard') }}" class="nav-link {{ request()->routeIs('shomriddhi-advance.dashboard') ? 'active' : '' }}">
        <i class="fas fa-tachometer-alt nav-icon"></i>
        <p>Dashboard</p>
    </a>
</li>
@endif

@if(auth()->user()->can('shomriddhi_advance_transaction_create'))
<li class="nav-item">
    <a href="{{ route('shomriddhi-advance.groups.index') }}" class="nav-link {{ request()->routeIs('shomriddhi-advance.groups.*') ? 'active' : '' }}">
        <i class="fas fa-layer-group nav-icon"></i>
        <p>Groups List</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('shomriddhi-advance.members.index') }}" class="nav-link {{ request()->routeIs('shomriddhi-advance.members.*') ? 'active' : '' }}">
        <i class="fas fa-users nav-icon"></i>
        <p>Member List</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('shomriddhi-advance.field-officers.index') }}" class="nav-link {{ request()->routeIs('shomriddhi-advance.field-officers.*') ? 'active' : '' }}">
        <i class="fas fa-user-tie nav-icon"></i>
        <p>Field Officers</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('shomriddhi-advance.field-officer-assignment.index') }}" class="nav-link {{ request()->routeIs('shomriddhi-advance.field-officer-assignment.*') ? 'active' : '' }}">
        <i class="fas fa-exchange-alt nav-icon"></i>
        <p>Officer Assignment</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('shomriddhi-advance.accounts.index') ?? '#' }}" class="nav-link {{ request()->routeIs('shomriddhi-advance.accounts.*') ? 'active' : '' }}">
        <i class="fas fa-user-circle nav-icon"></i>
        <p>Savings Accounts List</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('shomriddhi-advance.passbooks.index') }}" class="nav-link {{ request()->routeIs('shomriddhi-advance.passbooks.*') ? 'active' : '' }}">
        <i class="fas fa-book nav-icon"></i>
        <p>Manage Passbooks</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('shomriddhi-advance.create') }}" class="nav-link {{ request()->routeIs('shomriddhi-advance.create') ? 'active' : '' }}">
        <i class="fas fa-plus-circle nav-icon"></i>
        <p>Deposit / Withdraw</p>
    </a>
</li>
@endif

@if(auth()->user()->can('shomriddhi_advance_transaction_approve'))
<li class="nav-item">
    <a href="{{ route('shomriddhi-advance.index') }}" class="nav-link {{ request()->routeIs('shomriddhi-advance.index') ? 'active' : '' }}">
        <i class="fas fa-list nav-icon"></i>
        <p>Transactions</p>
    </a>
</li>
@endif

@if(auth()->user()->can('shomriddhi_advance_reports'))
<li class="nav-header text-uppercase font-weight-bold text-muted mt-2" style="font-size: 0.75rem;">Reports</li>
@if(auth()->user()->can('shomriddhi_advance_ledger_report'))
<li class="nav-item">
    <a href="{{ route('shomriddhi-advance.reports.ledger') }}" class="nav-link {{ request()->routeIs('shomriddhi-advance.reports.ledger') ? 'active' : '' }}">
        <i class="fas fa-file-invoice nav-icon"></i>
        <p>Customer Passbook</p>
    </a>
</li>
@endif
@if(auth()->user()->can('shomriddhi_advance_summary_report'))
<li class="nav-item">
    <a href="{{ route('shomriddhi-advance.reports.summary') }}" class="nav-link {{ request()->routeIs('shomriddhi-advance.reports.summary') ? 'active' : '' }}">
        <i class="fas fa-chart-bar nav-icon"></i>
        <p>Summary Report</p>
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('shomriddhi-advance.reports.collection_sheet') }}" class="nav-link {{ request()->routeIs('shomriddhi-advance.reports.collection_sheet') ? 'active' : '' }}">
        <i class="fas fa-print nav-icon"></i>
        <p>Collection Sheet</p>
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('shomriddhi-advance.reports.daily_interest') }}" class="nav-link {{ request()->routeIs('shomriddhi-advance.reports.daily_interest') ? 'active' : '' }}">
        <i class="fas fa-history nav-icon"></i>
        <p>Daily Interest Logs</p>
    </a>
</li>
@endif
@endif
@if(auth()->user()->can('shomriddhi_advance_settings') || true)
<li class="nav-header text-uppercase font-weight-bold text-muted mt-2" style="font-size: 0.75rem;">Settings</li>
<li class="nav-item">
    <a href="{{ route('shomriddhi-advance.slabs.index') ?? '#' }}" class="nav-link {{ request()->routeIs('shomriddhi-advance.slabs.*') ? 'active' : '' }}">
        <i class="fas fa-percent nav-icon"></i>
        <p>Interest Slabs</p>
    </a>
</li>
@endif
@endif
