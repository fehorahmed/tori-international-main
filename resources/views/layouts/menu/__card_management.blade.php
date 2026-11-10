@can('card_management')
<li class="nav-item {{ request()->is('admin/card-management/*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ request()->is('admin/card-management/*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-address-card"></i>
        <p>
            Card Management
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">

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
                <i class="far fa-circle nav-icon text-info"></i>
                <p>Member Cards</p>
            </a>
        </li>
        @endcan
        @can('member_card_logs')
        <li class="nav-item">
            <a href="{{ route('card-management.member.logs') }}" class="nav-link {{ request()->is('admin/card-management/member/logs') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon text-info-gradient text-xs"></i>
                <p>Member Print Logs</p>
            </a>
        </li>
        @endcan
        @can('employee_card')
        <li class="nav-item">
            <a href="{{ route('card-management.employee.index') }}" class="nav-link {{ request()->is('admin/card-management/employee') || request()->is('admin/card-management/employee/create') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon text-primary"></i>
                <p>Employee Cards</p>
            </a>
        </li>
        @endcan
        @can('employee_card_logs')
        <li class="nav-item">
            <a href="{{ route('card-management.employee.logs') }}" class="nav-link {{ request()->is('admin/card-management/employee/logs') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon text-primary-gradient text-xs"></i>
                <p>Employee Print Logs</p>
            </a>
        </li>
        @endcan
        <li class="nav-item mt-2">
            <a href="{{ route('card-management.collection.index') }}" class="nav-link {{ request()->is('admin/card-management/collection*') ? 'active' : '' }}">
                <i class="fas fa-money-check-alt nav-icon text-success"></i>
                <p>Payment Collection</p>
            </a>
        </li>
        <li class="nav-item mt-1">
            <a href="{{ route('card-management.settings.index') }}" class="nav-link {{ request()->is('admin/card-management/settings*') ? 'active' : '' }}">
                <i class="fas fa-cogs nav-icon text-secondary"></i>
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
    </ul>
</li>

@canany(['member_card_report', 'employee_card_report'])
<li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.75rem;">Card Reports</li>
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
