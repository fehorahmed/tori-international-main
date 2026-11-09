<li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.75rem;">Branches Workspace</li>
<li class="nav-item">
    <a href="{{ route('branch_management.dashboard') }}" class="nav-link {{ Route::currentRouteName() == 'branch_management.dashboard' ? 'active' : '' }}">
        <i class="nav-icon fas fa-tachometer-alt"></i>
        <p>Dashboard</p>
    </a>
</li>
    @if(auth()->user()->can('branch_view'))
        <li class="nav-item">
            <a href="{{ route('sister-concerns.index') }}" class="nav-link {{ in_array($currentRoute, ['sister-concerns.index', 'sister-concerns.create', 'sister-concerns.edit']) ? 'active' : '' }}">
                <i class="nav-icon fas fa-store"></i>
                <p>Branches</p>
            </a>
        </li>
    @endif
    @if(auth()->user()->can('branch_report'))
        <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-top: 10px;">Branch Reports</li>

        <li class="nav-item">
            <a href="{{ route('branch_report.daily_income_statement') }}" class="nav-link {{ $currentRoute == 'branch_report.daily_income_statement' ? 'active' : '' }}">
                <i class="nav-icon fas fa-calendar-day"></i>
                <p>Daily Income Statement</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('sister-concerns.report') }}" class="nav-link {{ $currentRoute == 'sister-concerns.report' ? 'active' : '' }}">
                <i class="nav-icon fas fa-file-invoice"></i>
                <p>Branch Report</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('branch.employee_report') }}" class="nav-link {{ $currentRoute == 'branch.employee_report' ? 'active' : '' }}">
                <i class="nav-icon fas fa-users"></i>
                <p>Employee Report</p>
            </a>
        </li>
        
        <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-top: 10px;">Financial Reports</li>
        <li class="nav-item">
            <a href="{{ route('branch_report.cash_or_bank_book') }}" class="nav-link {{ $currentRoute == 'branch_report.cash_or_bank_book' ? 'active' : '' }}">
                <i class="nav-icon fas fa-book"></i>
                <p>Cash/Bank Book</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('branch_report.receipt_and_payment') }}" class="nav-link {{ $currentRoute == 'branch_report.receipt_and_payment' ? 'active' : '' }}">
                <i class="nav-icon fas fa-cash-register"></i>
                <p>Receipt & Payment</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('branch_report.ledger') }}" class="nav-link {{ $currentRoute == 'branch_report.ledger' ? 'active' : '' }}">
                <i class="nav-icon fas fa-journal-whills"></i>
                <p>Ledger</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('branch_report.trial_balance') }}" class="nav-link {{ $currentRoute == 'branch_report.trial_balance' ? 'active' : '' }}">
                <i class="nav-icon fas fa-balance-scale"></i>
                <p>Trial Balance</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('branch_report.income_statement') }}" class="nav-link {{ $currentRoute == 'branch_report.income_statement' ? 'active' : '' }}">
                <i class="nav-icon fas fa-file-signature"></i>
                <p>Income Statement</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('branch_report.balance_sheet') }}" class="nav-link {{ $currentRoute == 'branch_report.balance_sheet' ? 'active' : '' }}">
                <i class="nav-icon fas fa-file-invoice-dollar"></i>
                <p>Balance Sheet</p>
            </a>
        </li>
    @endif
