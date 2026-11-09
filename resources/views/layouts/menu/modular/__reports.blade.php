@php
    $currentRoute = Route::currentRouteName();
@endphp

@if(auth()->user()->can('accounts_reports'))
    <!-- Category 1: Financial & Accounts -->
    <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px; margin-top: 10px;">
        Financial & Accounts
    </li>

    <!-- 1. Cash/Bank Book -->
    @if(auth()->user()->can('cash_book'))
        <li class="nav-item">
            <a href="{{ route('report.cash_or_bank_book') }}"
               class="nav-link {{ $currentRoute == 'report.cash_or_bank_book' ? 'active' : '' }}">
                <i class="nav-icon fas fa-book"></i>
                <p>Cash/Bank Book</p>
            </a>
        </li>
    @endif

    <!-- 2. Receipt & Payment -->
    @if(auth()->user()->can('receipt_and_payment'))
        <li class="nav-item">
            <a href="{{ route('report.receipt_and_payment') }}"
               class="nav-link {{ $currentRoute == 'report.receipt_and_payment' ? 'active' : '' }}">
                <i class="nav-icon fas fa-cash-register"></i>
                <p>Receipt & Payment</p>
            </a>
        </li>
    @endif

    <!-- 3. Ledger -->
    @if(auth()->user()->can('ledger'))
        <li class="nav-item">
            <a href="{{ route('report.ledger') }}"
               class="nav-link {{ $currentRoute == 'report.ledger' ? 'active' : '' }}">
                <i class="nav-icon fas fa-journal-whills"></i>
                <p>Ledger</p>
            </a>
        </li>
    @endif

    <!-- 4. Trial Balance -->
    @if(auth()->user()->can('trial_balance'))
        <li class="nav-item">
            <a href="{{ route('report.trial_balance') }}"
               class="nav-link {{ $currentRoute == 'report.trial_balance' ? 'active' : '' }}">
                <i class="nav-icon fas fa-balance-scale"></i>
                <p>Trial Balance</p>
            </a>
        </li>
    @endif

    <!-- 5. Income Statement -->
    @if(auth()->user()->can('income_statement'))
        <li class="nav-item">
            <a href="{{ route('report.income_statement') }}"
               class="nav-link {{ $currentRoute == 'report.income_statement' ? 'active' : '' }}">
                <i class="nav-icon fas fa-file-signature"></i>
                <p>Income Statement</p>
            </a>
        </li>
    @endif

    <!-- 6. Balance Sheet -->
    @if(auth()->user()->can('balance_sheet'))
        <li class="nav-item">
            <a href="{{ route('report.balance_sheet') }}"
               class="nav-link {{ $currentRoute == 'report.balance_sheet' ? 'active' : '' }}">
                <i class="nav-icon fas fa-file-invoice-dollar"></i>
                <p>Balance Sheet</p>
            </a>
        </li>
    @endif
@endif
