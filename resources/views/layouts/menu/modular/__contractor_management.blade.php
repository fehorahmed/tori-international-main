@can('contractor_management')
    <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.75rem;">Contractor Mgmt</li>

    <li class="nav-item">
        <a href="{{ route('contractor_management.dashboard') }}"
            class="nav-link {{ Route::currentRouteName() == 'contractor_management.dashboard' ? 'active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
        </a>
    </li>

    @can('contractor_type_view')
        <li class="nav-item">
            <a href="{{ route('contractor_management.types.index') }}"
                class="nav-link {{ str_starts_with(Route::currentRouteName() ?? '', 'contractor_management.types.') ? 'active' : '' }}">
                <i class="nav-icon fas fa-cogs"></i>
                <p>Contractor Types</p>
            </a>
        </li>
    @endcan

    @can('contractor_view')
        <li class="nav-item">
            <a href="{{ route('contractor_management.contractors.index') }}"
                class="nav-link {{ str_starts_with(Route::currentRouteName() ?? '', 'contractor_management.contractors.') ? 'active' : '' }}">
                <i class="nav-icon fas fa-hard-hat"></i>
                <p>Contractors List</p>
            </a>
        </li>
    @endcan
    @can('contractor_expense_head')
        <li class="nav-item">
            <a href="{{ route('contractor_management.expense-heads.index') }}"
                class="nav-link {{ str_starts_with(Route::currentRouteName() ?? '', 'contractor_management.expense-heads.') ? 'active' : '' }}">
                <i class="nav-icon fas fa-list-alt"></i>
                <p>Expenses Head</p>
            </a>
        </li>
    @endcan
    @can('contractor_income_head')
        <li class="nav-item">
            <a href="{{ route('contractor_management.income-heads.index') }}"
                class="nav-link {{ str_starts_with(Route::currentRouteName() ?? '', 'contractor_management.income-heads.') ? 'active' : '' }}">
                <i class="nav-icon fas fa-file-invoice-dollar"></i>
                <p>Income Head</p>
            </a>
        </li>
    @endcan
    @can('contractor_bill_view')
        <li class="nav-item">
            <a href="{{ route('contractor_management.bills.index') }}"
                class="nav-link {{ str_starts_with(Route::currentRouteName() ?? '', 'contractor_management.bills.') ? 'active' : '' }}">
                <i class="nav-icon fas fa-file-invoice-dollar"></i>
                <p>Contractor Bills</p>
            </a>
        </li>
    @endcan
    @can('contractor_payment_view')
        <li class="nav-item">
            <a href="{{ route('contractor_management.payments.index') }}"
                class="nav-link {{ str_starts_with(Route::currentRouteName() ?? '', 'contractor_management.payments.') ? 'active' : '' }}">
                <i class="nav-icon fas fa-money-check-alt"></i>
                <p>Contractor Payments</p>
            </a>
        </li>
    @endcan

    @can('contractor_report')
        <li class="nav-header text-uppercase font-weight-bold text-muted"
            style="font-size: 0.7rem; padding-left: 10px; margin-top: 15px;">
            Reports
        </li>
        @can('contractor_income_report')
            <li class="nav-item">
                <a target="_blank" href="{{ route('contractor_management.reports.income_heads_list') }}"
                    class="nav-link {{ Route::currentRouteName() == 'contractor_management.reports.income_heads_list' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-list"></i>
                    <p>Income Heads List</p>
                </a>
            </li>
        @endcan
        @can('contractor_expense_report')
            <li class="nav-item">
                <a target="_blank" href="{{ route('contractor_management.reports.expense_heads_list') }}"
                    class="nav-link {{ Route::currentRouteName() == 'contractor_management.reports.expense_heads_list' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-list"></i>
                    <p>Expense Heads List</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('contractor_management.reports.expense_heads') }}"
                    class="nav-link {{ Route::currentRouteName() == 'contractor_management.reports.expense_heads' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-list-alt"></i>
                    <p>Head wise Expense</p>
                </a>
            </li>
        @endcan
        @can('contractor_outstanding_report')
            <li class="nav-item">
                <a href="{{ route('contractor_management.reports.outstanding') }}"
                    class="nav-link {{ Route::currentRouteName() == 'contractor_management.reports.outstanding' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-balance-scale"></i>
                    <p>Outstanding Report</p>
                </a>
            </li>
        @endcan
        @can('contractor_bill_summary_report')
            <li class="nav-item">
                <a href="{{ route('contractor_management.reports.bill_summary') }}"
                    class="nav-link {{ Route::currentRouteName() == 'contractor_management.reports.bill_summary' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-file-invoice"></i>
                    <p>Bill Summary Report</p>
                </a>
            </li>
        @endcan
        @can('contractor_ledger')
            <li class="nav-item">
                <a href="{{ route('contractor_management.reports.ledger') }}"
                    class="nav-link {{ Route::currentRouteName() == 'contractor_management.reports.ledger' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-book"></i>
                    <p>Contractor Ledger</p>
                </a>
            </li>
        @endcan
    @endcan
@endcan