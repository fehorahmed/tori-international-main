@php
    $currentRoute = Route::currentRouteName();
@endphp

<li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.75rem;">Microfinance Setup</li>

<li class="nav-item">
    <a href="{{ route('microfinance.dashboard') }}"
       class="nav-link {{ $currentRoute == 'microfinance.dashboard' ? 'active' : '' }}">
        <i class="nav-icon fas fa-chart-line"></i>
        <p class="font-weight-bold">Dashboard</p>
    </a>
</li>

@can('mf_field_officer')
<li class="nav-item">
    <a href="{{ route('microfinance.field-officer.index') }}"
       class="nav-link {{ in_array($currentRoute, ['microfinance.field-officer.index', 'microfinance.field-officer.create', 'microfinance.field-officer.edit', 'microfinance.field-officer.show']) ? 'active' : '' }}">
        <i class="nav-icon fas fa-user-tie"></i>
        <p>Field Officers </p>
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('microfinance.field-officer-assignment.index') }}"
       class="nav-link {{ $currentRoute == 'microfinance.field-officer-assignment.index' ? 'active' : '' }}">
        <i class="nav-icon fas fa-exchange-alt"></i>
        <p>FO Reassignments</p>
    </a>
</li>
@endcan
@can('mf_somiti')
<li class="nav-item">
    <a href="{{ route('microfinance.somiti.index') }}"
       class="nav-link {{ in_array($currentRoute, ['microfinance.somiti.index', 'microfinance.somiti.create', 'microfinance.somiti.edit', 'microfinance.somiti.show']) ? 'active' : '' }}">
        <i class="nav-icon fas fa-users"></i>
        <p>Somitis (Groups)</p>
    </a>
</li>
@endcan
@can('mf_member')
<li class="nav-item">
    <a href="{{ route('microfinance.member.index') }}"
       class="nav-link {{ in_array($currentRoute, ['microfinance.member.index', 'microfinance.member.create', 'microfinance.member.edit', 'microfinance.member.show']) ? 'active' : '' }}">
        <i class="nav-icon fas fa-user-friends"></i>
        <p>Members</p>
    </a>
</li>
@endcan

<li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.75rem; margin-top: 10px;">Savings & Loans</li>

@can('mf_savings_product')
<li class="nav-item">
    <a href="{{ route('microfinance.savings-product.index') }}"
       class="nav-link {{ in_array($currentRoute, ['microfinance.savings-product.index', 'microfinance.savings-product.create', 'microfinance.savings-product.edit', 'microfinance.savings-product.show']) ? 'active' : '' }}">
        <i class="nav-icon fas fa-piggy-bank"></i>
        <p>Savings Products</p>
    </a>
</li>
@endcan
@can('mf_savings_account')
<li class="nav-item">
    <a href="{{ route('microfinance.savings-account.index') }}"
       class="nav-link {{ in_array($currentRoute, ['microfinance.savings-account.index', 'microfinance.savings-account.create', 'microfinance.savings-account.show']) ? 'active' : '' }}">
        <i class="nav-icon fas fa-wallet"></i>
        <p>Member Accounts</p>
    </a>
</li>
@endcan
@can('mf_loan_product')
<li class="nav-item">
    <a href="{{ route('microfinance.loan-product.index') }}"
       class="nav-link {{ in_array($currentRoute, ['microfinance.loan-product.index', 'microfinance.loan-product.create', 'microfinance.loan-product.edit', 'microfinance.loan-product.show']) ? 'active' : '' }}">
        <i class="nav-icon fas fa-box"></i>
        <p>Loan Products</p>
    </a>
</li>
@endcan
@can('mf_loan')
<li class="nav-item">
    <a href="{{ route('microfinance.loan.index') }}"
       class="nav-link {{ in_array($currentRoute, ['microfinance.loan.index', 'microfinance.loan.create', 'microfinance.loan.show']) ? 'active' : '' }}">
        <i class="nav-icon fas fa-hand-holding-usd"></i>
        <p>Loans</p>
    </a>
</li>
@endcan

<li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.75rem; margin-top: 10px;">Operations</li>

@if(auth()->user()->hasPermissionTo('mf_member') || auth()->user()->hasPermissionTo('mf_loan') || auth()->user()->hasPermissionTo('mf_savings_account'))
<li class="nav-item has-treeview">
    <a href="#" class="nav-link">
        <i class="nav-icon fas fa-print"></i>
        <p>Print Blank Forms <i class="right fas fa-angle-left"></i></p>
    </a>
    <ul class="nav nav-treeview">
        @can('mf_member')
        <li class="nav-item">
            <a href="{{ route('microfinance.member.print_empty_form') }}" target="_blank" class="nav-link">
                <i class="fas fa-user-plus nav-icon"></i>
                <p>Member Admission</p>
            </a>
        </li>
        @endcan
        @can('mf_loan')
        <li class="nav-item">
            <a href="{{ route('microfinance.loan.print_empty_form') }}" target="_blank" class="nav-link">
                <i class="fas fa-hand-holding-usd nav-icon"></i>
                <p>Loan Application</p>
            </a>
        </li>
        @endcan
        @can('mf_savings_account')
        <li class="nav-item">
            <a href="{{ route('microfinance.savings-account.print_empty_dps_form') }}" target="_blank" class="nav-link">
                <i class="fas fa-piggy-bank nav-icon"></i>
                <p>DPS Application</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('microfinance.savings-account.print_empty_fdr_form') }}" target="_blank" class="nav-link">
                <i class="fas fa-file-invoice-dollar nav-icon"></i>
                <p>FDR Application</p>
            </a>
        </li>
        @endcan
    </ul>
</li>
@endif

@can('mf_collection_sheet')
<li class="nav-item">
    <a href="{{ route('microfinance.collection.sheet') }}"
       class="nav-link {{ $currentRoute == 'microfinance.collection.sheet' ? 'active' : '' }}">
        <i class="nav-icon fas fa-clipboard-list"></i>
        <p>Collection Sheet</p>
    </a>
</li>
@endcan
@if(auth()->user()->hasPermissionTo('mf_collection') || auth()->user()->hasPermissionTo('mf_collection_view'))
<li class="nav-item">
    <a href="{{ route('microfinance.collection.index') }}"
       class="nav-link {{ in_array($currentRoute, ['microfinance.collection.index', 'microfinance.collection.datatable']) ? 'active' : '' }}">
        <i class="nav-icon fas fa-money-bill-wave"></i>
        <p>Loan Collections</p>
    </a>
</li>
@endif
@can('mf_day_closing')
<li class="nav-item">
    <a href="{{ route('microfinance.day-closing.index') }}"
       class="nav-link {{ $currentRoute == 'microfinance.day-closing.index' ? 'active' : '' }}">
        <i class="nav-icon fas fa-calendar-check text-warning"></i>
        <p class="text-warning">Day Closing (EOD)</p>
    </a>
</li>
@endcan

@can('mf_opening_balance')
<li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.75rem; margin-top: 10px;">Migration / Opening</li>
<li class="nav-item has-treeview {{ in_array($currentRoute, ['microfinance.opening-balance.general-savings','microfinance.opening-balance.dps','microfinance.opening-balance.fdr','microfinance.opening-balance.loan']) ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ in_array($currentRoute, ['microfinance.opening-balance.general-savings','microfinance.opening-balance.dps','microfinance.opening-balance.fdr','microfinance.opening-balance.loan']) ? 'active' : '' }}">
        <i class="nav-icon fas fa-database text-orange"></i>
        <p>Opening Balance <i class="right fas fa-angle-left"></i></p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('microfinance.opening-balance.general-savings') }}"
               class="nav-link {{ $currentRoute == 'microfinance.opening-balance.general-savings' ? 'active' : '' }}">
                <i class="fas fa-piggy-bank nav-icon text-success"></i>
                <p>General Savings</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('microfinance.opening-balance.dps') }}"
               class="nav-link {{ $currentRoute == 'microfinance.opening-balance.dps' ? 'active' : '' }}">
                <i class="fas fa-calendar-alt nav-icon text-primary"></i>
                <p>DPS</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('microfinance.opening-balance.fdr') }}"
               class="nav-link {{ $currentRoute == 'microfinance.opening-balance.fdr' ? 'active' : '' }}">
                <i class="fas fa-file-invoice-dollar nav-icon text-warning"></i>
                <p>FDR</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('microfinance.opening-balance.loan') }}"
               class="nav-link {{ $currentRoute == 'microfinance.opening-balance.loan' ? 'active' : '' }}">
                <i class="fas fa-hand-holding-usd nav-icon text-danger"></i>
                <p>Loan</p>
            </a>
        </li>
    </ul>
</li>
@endcan


@if(auth()->user()->hasPermissionTo('mf_reports') || auth()->user()->hasPermissionTo('mf_report_member_ledger') || auth()->user()->hasPermissionTo('mf_report_collection_sheet') || auth()->user()->hasPermissionTo('mf_report_loan_portfolio') || auth()->user()->hasPermissionTo('mf_report_savings_portfolio') || auth()->user()->hasPermissionTo('mf_report_due_overdue') || auth()->user()->hasPermissionTo('mf_report_loan_disbursement'))
<li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.75rem; margin-top: 10px;">Reports</li>

@can('mf_report_member_ledger')
<li class="nav-item">
    <a href="{{ route('microfinance.report.member-ledger') }}"
       class="nav-link {{ $currentRoute == 'microfinance.report.member-ledger' ? 'active' : '' }}">
        <i class="nav-icon fas fa-book"></i>
        <p>Member Ledger</p>
    </a>
</li>
@endcan
@can('mf_report_collection_sheet')
<li class="nav-item">
    <a href="{{ route('microfinance.report.collection-sheet') }}"
       class="nav-link {{ $currentRoute == 'microfinance.report.collection-sheet' ? 'active' : '' }}">
        <i class="nav-icon fas fa-file-invoice"></i>
        <p>Collection Report</p>
    </a>
</li>
@endcan
@can('mf_report_loan_portfolio')
<li class="nav-item">
    <a href="{{ route('microfinance.report.loan-portfolio') }}"
       class="nav-link {{ $currentRoute == 'microfinance.report.loan-portfolio' ? 'active' : '' }}">
        <i class="nav-icon fas fa-chart-pie"></i>
        <p>Loan Portfolio</p>
    </a>
</li>
@endcan
@can('mf_report_savings_portfolio')
<li class="nav-item">
    <a href="{{ route('microfinance.report.savings-portfolio') }}"
       class="nav-link {{ $currentRoute == 'microfinance.report.savings-portfolio' ? 'active' : '' }}">
        <i class="nav-icon fas fa-chart-line"></i>
        <p>Savings Portfolio</p>
    </a>
</li>
@endcan
@can('mf_report_due_overdue')
<li class="nav-item">
    <a href="{{ route('microfinance.report.due-overdue') }}"
       class="nav-link {{ $currentRoute == 'microfinance.report.due-overdue' ? 'active' : '' }}">
        <i class="nav-icon fas fa-exclamation-triangle text-danger"></i>
        <p>Due / Overdue (NPL)</p>
    </a>
</li>
@endcan
@can('mf_report_loan_disbursement')
<li class="nav-item">
    <a href="{{ route('microfinance.report.loan-disbursement') }}"
       class="nav-link {{ $currentRoute == 'microfinance.report.loan-disbursement' ? 'active' : '' }}">
        <i class="nav-icon fas fa-hand-holding-usd text-success"></i>
        <p>Loan Disbursement</p>
    </a>
</li>
@endcan
@endif
