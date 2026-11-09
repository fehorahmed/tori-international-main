@php
    $currentRoute = Route::currentRouteName();
@endphp

@if(auth()->user()->can('accounts'))
    <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px;">
        Accounts
    </li>
    <!-- Dashboard -->
    <li class="nav-item">
        <a href="{{ route('accounts.dashboard') }}"
           class="nav-link {{ $currentRoute == 'accounts.dashboard' ? 'active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
        </a>
    </li>

    <!-- Category 1: Account Configurations -->
    <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px; margin-top: 10px;">
        Account Configurations
    </li>

    <!-- 0. Account Groups -->
    @if(auth()->user()->can('account_group'))
        <li class="nav-item">
            <a href="{{ route('account-group.index') }}"
               class="nav-link {{ $currentRoute == 'account-group.index' ? 'active' : '' }}">
                <i class="nav-icon fas fa-layer-group"></i>
                <p>Account Groups</p>
            </a>
        </li>
    @endif

    <!-- 1. Payment Modes -->
    @if(auth()->user()->can('payment_modes'))
        <li class="nav-item">
            <a href="{{ route('account-head.index', ['payment_mode' => 1]) }}"
               class="nav-link {{ in_array($currentRoute, ['account-head.index', 'account-head.create', 'account-head.edit']) && request('payment_mode') != 0 ? 'active' : '' }}">
                <i class="nav-icon fas fa-list-alt"></i>
                <p>Payment Modes</p>
            </a>
        </li>
    @endif

    <!-- 2. Chart of Accounts -->
    @if(auth()->user()->can('account_head'))
        <li class="nav-item">
            <a href="{{ route('account-head.index', ['payment_mode' => 0]) }}"
               class="nav-link {{ in_array($currentRoute, ['account-head.index', 'account-head.create', 'account-head.edit']) && request('payment_mode') == 0 ? 'active' : '' }}">
                <i class="nav-icon fas fa-list-ol"></i>
                <p>Chart of Accounts</p>
            </a>
        </li>
    @endif

    <!-- Category 2: Voucher Bookkeeping -->
    <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px; margin-top: 15px;">
        Voucher Bookkeeping
    </li>

    <!-- 3. Payment Voucher -->
    @if(auth()->user()->can('payment_voucher'))
        <li class="nav-item">
            <a href="{{ route('voucher.index', ['voucher_type' => \App\Enumeration\VoucherType::$PAYMENT_VOUCHER]) }}"
               class="nav-link {{ in_array($currentRoute, ['voucher.index', 'voucher.create', 'voucher.edit', 'voucher.details']) && request('voucher_type') == \App\Enumeration\VoucherType::$PAYMENT_VOUCHER ? 'active' : '' }}">
                <i class="nav-icon fas fa-file-invoice-dollar"></i>
                <p>Payment Voucher</p>
            </a>
        </li>
    @endif

    <!-- 4. Receipt Voucher -->
    @if(auth()->user()->can('receipt_voucher'))
        <li class="nav-item">
            <a href="{{ route('voucher.index', ['voucher_type' => \App\Enumeration\VoucherType::$COLLECTION_VOUCHER]) }}"
               class="nav-link {{ in_array($currentRoute, ['voucher.index', 'voucher.create', 'voucher.edit', 'voucher.details']) && request('voucher_type') == \App\Enumeration\VoucherType::$COLLECTION_VOUCHER ? 'active' : '' }}">
                <i class="nav-icon fas fa-cash-register"></i>
                <p>Receipt Voucher</p>
            </a>
        </li>
    @endif

    <!-- 5. Contra Voucher -->
    @if(auth()->user()->can('contra_voucher'))
        <li class="nav-item">
            <a href="{{ route('voucher.index', ['voucher_type' => \App\Enumeration\VoucherType::$CONTRA_VOUCHER]) }}"
               class="nav-link {{ in_array($currentRoute, ['voucher.index', 'voucher.create', 'voucher.edit', 'voucher.details']) && request('voucher_type') == \App\Enumeration\VoucherType::$CONTRA_VOUCHER ? 'active' : '' }}">
                <i class="nav-icon fas fa-exchange-alt"></i>
                <p>Contra Voucher</p>
            </a>
        </li>
    @endif

    <!-- 6. Journal Voucher -->
    @if(auth()->user()->can('journal_voucher'))
        <li class="nav-item">
            <a href="{{ route('voucher.index', ['voucher_type' => \App\Enumeration\VoucherType::$JOURNAL_VOUCHER]) }}"
               class="nav-link {{ in_array($currentRoute, ['voucher.index', 'voucher.create', 'voucher.edit', 'voucher.details']) && request('voucher_type') == \App\Enumeration\VoucherType::$JOURNAL_VOUCHER ? 'active' : '' }}">
                <i class="nav-icon fas fa-journal-whills"></i>
                <p>Journal Voucher</p>
            </a>
        </li>
    @endif

    <!-- Send Branch Payment -->
    <li class="nav-item">
        <a href="{{ route('inter_company_settlements.index') }}"
           class="nav-link {{ in_array(Route::currentRouteName(), ['inter_company_settlements.index','inter_company_settlements.create']) ? 'active' : '' }}">
            <i class="nav-icon fa fa-paper-plane"></i>
            <p>Send Branch Payment</p>
        </a>
    </li>
    
    <!-- Receive Branch Payment -->
    <li class="nav-item">
        <a href="{{ route('inter_company_settlements.receive_list') }}"
           class="nav-link {{ Route::currentRouteName() == 'inter_company_settlements.receive_list' ? 'active' : '' }}">
            <i class="nav-icon fa fa-download"></i>
            <p>Receive Branch Payment</p>
        </a>
    </li>

    <!-- Category 3: Financial Reports -->
    @if(auth()->user()->can('accounts_reports'))
        <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px; margin-top: 15px;">
            Financial Reports
        </li>

        <!-- Cash/Bank Book -->
        @if(auth()->user()->can('cash_book'))
            <li class="nav-item">
                <a href="{{ route('report.cash_or_bank_book') }}"
                   class="nav-link {{ $currentRoute == 'report.cash_or_bank_book' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-book"></i>
                    <p>Cash/Bank Book</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('report.cash_or_bank_transaction_details') }}"
                   class="nav-link {{ $currentRoute == 'report.cash_or_bank_transaction_details' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-list-alt"></i>
                    <p>Cash Transaction Details</p>
                </a>
            </li>
        @endif

        <!-- Receipt & Payment -->
        @if(auth()->user()->can('receipt_and_payment'))
            <li class="nav-item">
                <a href="{{ route('report.receipt_and_payment') }}"
                   class="nav-link {{ $currentRoute == 'report.receipt_and_payment' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-cash-register"></i>
                    <p>Receipt & Payment</p>
                </a>
            </li>
        @endif

        <!-- General Ledger -->
        @if(auth()->user()->can('ledger'))
            <li class="nav-item">
                <a href="{{ route('report.ledger') }}"
                   class="nav-link {{ $currentRoute == 'report.ledger' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-journal-whills"></i>
                    <p>General Ledger</p>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="{{ route('report.inter_company_ledger') }}"
                   class="nav-link {{ Route::currentRouteName() == 'report.inter_company_ledger' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-project-diagram"></i>
                    <p>Inter-Company Ledger</p>
                </a>
            </li>
        @endif



        <!-- Trial Balance -->
        @if(auth()->user()->can('trial_balance'))
            <li class="nav-item">
                <a href="{{ route('report.trial_balance') }}"
                   class="nav-link {{ $currentRoute == 'report.trial_balance' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-balance-scale"></i>
                    <p>Trial Balance</p>
                </a>
            </li>
        @endif

        <!-- Income Statement -->
        @if(auth()->user()->can('income_statement'))
            <li class="nav-item">
                <a href="{{ route('report.income_statement') }}"
                   class="nav-link {{ $currentRoute == 'report.income_statement' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-file-signature"></i>
                    <p>Income Statement</p>
                </a>
            </li>
        @endif

        <!-- Balance Sheet -->
        @if(auth()->user()->can('balance_sheet'))
            <li class="nav-item">
                <a href="{{ route('report.balance_sheet') }}"
                   class="nav-link {{ $currentRoute == 'report.balance_sheet' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-file-invoice-dollar"></i>
                    <p>Balance Sheet</p>
                </a>
            </li>
        @endif

        <!-- Daily Transactions -->
        @if(auth()->user()->can('daily_transactions_report'))
            <li class="nav-item">
                <a href="{{ route('report.daily_transactions') }}"
                   class="nav-link {{ $currentRoute == 'report.daily_transactions' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-chart-line"></i>
                    <p>Daily Transactions</p>
                </a>
            </li>
        @endif
    @endif
@endif
