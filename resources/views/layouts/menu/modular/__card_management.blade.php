@can('card_management')
<li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.75rem;">Card Management</li>

<li class="nav-item">
    <a href="{{ route('card-management.dashboard') }}"
       class="nav-link {{ request()->routeIs('card-management.dashboard') ? 'active' : '' }}">
        <i class="nav-icon fas fa-tachometer-alt"></i>
        <p>Dashboard</p>
    </a>
</li>


@if(auth()->user()->role === 'Admin' || auth()->user()->can('shomriddhi_advance_transaction_approve'))
<li class="nav-item">
    <a href="{{ route('card-management.cashout.index') }}" class="nav-link {{ request()->routeIs('card-management.cashout.index') ? 'active' : '' }}">
        <i class="nav-icon fas fa-money-bill-wave"></i><p>Membership Cash-out</p>
    </a>
</li>
@endif

@can('member_card')
<li class="nav-item">
    <a href="{{ route('card-management.member.index') }}" class="nav-link {{ request()->is('admin/card-management/member') || request()->is('admin/card-management/member/create') ? 'active' : '' }}">
        <i class="nav-icon fas fa-users"></i>
        <p>Member Cards</p>
    </a>
</li>
@endcan

@can('member_card_logs')
<li class="nav-item">
    <a href="{{ route('card-management.member.logs') }}" class="nav-link {{ request()->is('admin/card-management/member/logs') ? 'active' : '' }}">
        <i class="nav-icon fas fa-history"></i>
        <p>Member Print Logs</p>
    </a>
</li>
@endcan

@can('employee_card')
<li class="nav-item">
    <a href="{{ route('card-management.employee.index') }}" class="nav-link {{ request()->is('admin/card-management/employee') || request()->is('admin/card-management/employee/create') ? 'active' : '' }}">
        <i class="nav-icon fas fa-user-tie"></i>
        <p>Employee Cards</p>
    </a>
</li>
@endcan

@can('employee_card_logs')
<li class="nav-item">
    <a href="{{ route('card-management.employee.logs') }}" class="nav-link {{ request()->is('admin/card-management/employee/logs') ? 'active' : '' }}">
        <i class="nav-icon fas fa-history"></i>
        <p>Employee Print Logs</p>
    </a>
</li>
@endcan

<li class="nav-item">
    <a href="{{ route('card-management.collection.index') }}" class="nav-link {{ request()->is('admin/card-management/collection*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-money-check-alt"></i>
        <p>Pending Payments</p>
    </a>
</li>

<li class="nav-item mt-1">
    <a href="{{ route('card-management.settings.index') }}" class="nav-link {{ request()->is('admin/card-management/settings*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-cogs"></i>
        <p>Card Settings</p>
    </a>
</li>
@if(auth()->user()->role === 'Admin')
<li class="nav-item">
    <a href="{{ route('card-management.cashout.settings') }}" class="nav-link {{ request()->routeIs('card-management.cashout.settings*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-cogs"></i><p>Cash-out Settings</p>
    </a>
</li>
@endif

@canany(['member_card_report', 'employee_card_report'])
<li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.75rem;">Card Reports</li>

<li class="nav-item">
    <a href="{{ route('card-management.reports.collection') }}" class="nav-link {{ request()->routeIs('card-management.reports.collection') ? 'active' : '' }}">
        <i class="nav-icon fas fa-file-invoice-dollar"></i>
        <p>Collection Report</p>
    </a>
</li>
@endcanany

@can('member_card_report')
<li class="nav-item has-treeview {{ request()->is('admin/card-management/reports/member*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ request()->is('admin/card-management/reports/member*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-chart-bar"></i>
        <p>
            Member Reports
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('card-management.reports.member.status_summary') }}" class="nav-link {{ request()->routeIs('card-management.reports.member.status_summary') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Status Summary</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('card-management.reports.member.print_history') }}" class="nav-link {{ request()->routeIs('card-management.reports.member.print_history') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Print History</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('card-management.reports.member.lost_damaged') }}" class="nav-link {{ request()->routeIs('card-management.reports.member.lost_damaged') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Lost / Damaged</p>
            </a>
        </li>
    </ul>
</li>
@endcan

@can('employee_card_report')
<li class="nav-item has-treeview {{ request()->is('admin/card-management/reports/employee*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ request()->is('admin/card-management/reports/employee*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-chart-line"></i>
        <p>
            Employee Reports
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('card-management.reports.employee.status_summary') }}" class="nav-link {{ request()->routeIs('card-management.reports.employee.status_summary') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Status Summary</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('card-management.reports.employee.print_history') }}" class="nav-link {{ request()->routeIs('card-management.reports.employee.print_history') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Print History</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('card-management.reports.employee.lost_damaged') }}" class="nav-link {{ request()->routeIs('card-management.reports.employee.lost_damaged') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Lost / Damaged</p>
            </a>
        </li>
    </ul>
</li>
@endcan

@endcan
