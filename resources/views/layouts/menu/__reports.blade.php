<?php
$subMenu = [
    'report.purchase',
    'report.purchase_details',
    'report.sale',
    'report.stock',
    'report.product_ledger',
    'report.batch_stock',
    'report.project_wise_report',
    'report.cash_or_bank_book',
    'report.cash_or_bank_transaction_details',
    'report.receipt_and_payment',
    'report.ledger',
    'report.trial_balance',
    'report.income_statement',
    'report.balance_sheet',
    'report.sr_performance',
    'report.sr_performance_summary',
    'report.leads',
    'report.outlet_types',
    'report.markets',
    'report.live_attendance',
    'report.sr_tasks',
    'report.sr_visits',
    'report.trainee_visits',
    'report.trainee_performance',
    'report.trainee_performance_summary',
    'report.area_manager_history',
    'report.branch_manager_history',
];
?>
@if(auth()->user()->can('accounts_reports'))
    <li class="nav-item {{ in_array(Route::currentRouteName(), $subMenu) ? 'menu-open' : '' }}">
        <a href="#"
           class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
            <i class="nav-icon fa fa-receipt"></i>
            <p>
                Reports
                <i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="{{ route('report.purchase') }}"
                   class="nav-link {{ Route::currentRouteName() == 'report.purchase' ? 'active' : '' }}">
                    <i class="far {{ Route::currentRouteName() == 'report.purchase' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>Purchase Report</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('report.purchase_details') }}"
                   class="nav-link {{ Route::currentRouteName() == 'report.purchase_details' ? 'active' : '' }}">
                    <i class="far {{ Route::currentRouteName() == 'report.purchase_details' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>Purchase Details Report</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('report.sale') }}"
                   class="nav-link {{ Route::currentRouteName() == 'report.sale' ? 'active' : '' }}">
                    <i class="far {{ Route::currentRouteName() == 'report.sale' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>Sale Report</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('report.stock') }}"
                   class="nav-link {{ Route::currentRouteName() == 'report.stock' ? 'active' : '' }}">
                    <i class="far {{ Route::currentRouteName() == 'report.stock' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>Stock Report</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('report.product_ledger') }}"
                   class="nav-link {{ Route::currentRouteName() == 'report.product_ledger' ? 'active' : '' }}">
                    <i class="far {{ Route::currentRouteName() == 'report.product_ledger' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>Product Ledger</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('report.batch_stock') }}"
                   class="nav-link {{ Route::currentRouteName() == 'report.batch_stock' ? 'active' : '' }}">
                    <i class="far {{ Route::currentRouteName() == 'report.batch_stock' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>Batch-wise Stock Report</p>
                </a>
            </li>
            @if(auth()->user()->can('cash_book'))
                <li class="nav-item">
                    <a href="{{ route('report.cash_or_bank_book') }}"
                       class="nav-link {{ Route::currentRouteName() == 'report.cash_or_bank_book' ? 'active' : '' }}">
                        <i class="far {{ Route::currentRouteName() == 'report.cash_or_bank_book' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                        <p>Cash/Bank Book</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('report.cash_or_bank_transaction_details') }}"
                       class="nav-link {{ Route::currentRouteName() == 'report.cash_or_bank_transaction_details' ? 'active' : '' }}">
                        <i class="far {{ Route::currentRouteName() == 'report.cash_or_bank_transaction_details' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                        <p>Cash Transaction Details</p>
                    </a>
                </li>
            @endif
            @if(auth()->user()->can('receipt_and_payment'))
                <li class="nav-item">
                    <a href="{{ route('report.receipt_and_payment') }}"
                       class="nav-link {{ Route::currentRouteName() == 'report.receipt_and_payment' ? 'active' : '' }}">
                        <i class="far {{ Route::currentRouteName() == 'report.receipt_and_payment' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                        <p>Receipt & Payment</p>
                    </a>
                </li>
            @endif

            @if(auth()->user()->can('ledger'))
                <li class="nav-item">
                    <a href="{{ route('report.ledger') }}"
                       class="nav-link {{ Route::currentRouteName() == 'report.ledger' ? 'active' : '' }}">
                        <i class="far {{ Route::currentRouteName() == 'report.ledger' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                        <p>Ledger</p>
                    </a>
                </li>
            @endif
            @if(auth()->user()->can('trial_balance'))
                <li class="nav-item">
                    <a href="{{ route('report.trial_balance') }}"
                       class="nav-link {{ Route::currentRouteName() == 'report.trial_balance' ? 'active' : '' }}">
                        <i class="far {{ Route::currentRouteName() == 'report.trial_balance' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                        <p>Trial Balance</p>
                    </a>
                </li>
            @endif
            @if(auth()->user()->can('income_statement'))
                <li class="nav-item">
                    <a href="{{ route('report.income_statement') }}"
                       class="nav-link {{ Route::currentRouteName() == 'report.income_statement' ? 'active' : '' }}">
                        <i class="far {{ Route::currentRouteName() == 'report.income_statement' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                        <p>Income Statement</p>
                    </a>
                </li>
            @endif
            @if(auth()->user()->can('balance_sheet'))
                <li class="nav-item">
                    <a href="{{ route('report.balance_sheet') }}"
                       class="nav-link {{ Route::currentRouteName() == 'report.balance_sheet' ? 'active' : '' }}">
                        <i class="far {{ Route::currentRouteName() == 'report.balance_sheet' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                        <p>Balance Sheet</p>
                    </a>
                </li>
            @endif
            <li class="nav-item">
                <a href="{{ route('report.sr_performance') }}"
                   class="nav-link {{ Route::currentRouteName() == 'report.sr_performance' ? 'active' : '' }}">
                    <i class="far {{ Route::currentRouteName() == 'report.sr_performance' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>SR Performance Report</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('report.sr_performance_summary') }}"
                   class="nav-link {{ Route::currentRouteName() == 'report.sr_performance_summary' ? 'active' : '' }}">
                    <i class="far {{ Route::currentRouteName() == 'report.sr_performance_summary' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>SR Per. Summary</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('report.leads') }}"
                   class="nav-link {{ Route::currentRouteName() == 'report.leads' ? 'active' : '' }}">
                    <i class="far {{ Route::currentRouteName() == 'report.leads' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>Outlet Setup Report</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('report.outlet_types') }}"
                   class="nav-link {{ Route::currentRouteName() == 'report.outlet_types' ? 'active' : '' }}">
                    <i class="far {{ Route::currentRouteName() == 'report.outlet_types' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>Outlet Type Report</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('report.markets') }}"
                   class="nav-link {{ Route::currentRouteName() == 'report.markets' ? 'active' : '' }}">
                    <i class="far {{ Route::currentRouteName() == 'report.markets' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>Market Report</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('report.sr_tasks') }}"
                   class="nav-link {{ Route::currentRouteName() == 'report.sr_tasks' ? 'active' : '' }}">
                    <i class="far {{ Route::currentRouteName() == 'report.sr_tasks' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>SR Tasks Report</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('report.sr_visits') }}"
                   class="nav-link {{ Route::currentRouteName() == 'report.sr_visits' ? 'active' : '' }}">
                    <i class="far {{ Route::currentRouteName() == 'report.sr_visits' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>SR Visits Report</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('report.trainee_visits') }}"
                   class="nav-link {{ Route::currentRouteName() == 'report.trainee_visits' ? 'active' : '' }}">
                    <i class="far {{ Route::currentRouteName() == 'report.trainee_visits' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>Trainee Visits Report</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('report.trainee_performance') }}"
                   class="nav-link {{ Route::currentRouteName() == 'report.trainee_performance' ? 'active' : '' }}">
                    <i class="far {{ Route::currentRouteName() == 'report.trainee_performance' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>Trainee Performance</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('report.trainee_performance_summary') }}"
                   class="nav-link {{ Route::currentRouteName() == 'report.trainee_performance_summary' ? 'active' : '' }}">
                    <i class="far {{ Route::currentRouteName() == 'report.trainee_performance_summary' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>Trainee Per. Summary</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('report.live_attendance') }}"
                   class="nav-link {{ Route::currentRouteName() == 'report.live_attendance' ? 'active' : '' }}">
                    <i class="far {{ Route::currentRouteName() == 'report.live_attendance' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>Attendance Report</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('report.area_manager_history') }}"
                   class="nav-link {{ Route::currentRouteName() == 'report.area_manager_history' ? 'active' : '' }}">
                    <i class="far {{ Route::currentRouteName() == 'report.area_manager_history' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>Area Manager History</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('report.branch_manager_history') }}"
                   class="nav-link {{ Route::currentRouteName() == 'report.branch_manager_history' ? 'active' : '' }}">
                    <i class="far {{ Route::currentRouteName() == 'report.branch_manager_history' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>Branch Manager History</p>
                </a>
            </li>
        </ul>
    </li>
@endif
