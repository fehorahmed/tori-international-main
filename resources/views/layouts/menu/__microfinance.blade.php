<?php
$subMenu = [
    'microfinance.field-officer.index', 'microfinance.field-officer.create', 'microfinance.field-officer.edit', 'microfinance.field-officer.show',
    'microfinance.field-officer-assignment.index',
    'microfinance.member.index', 'microfinance.member.create', 'microfinance.member.edit', 'microfinance.member.show',
    'microfinance.somiti.index', 'microfinance.somiti.create', 'microfinance.somiti.edit','microfinance.somiti.show',
    'microfinance.savings-product.index', 'microfinance.savings-product.create', 'microfinance.savings-product.edit', 'microfinance.savings-product.show',
    'microfinance.savings-account.index', 'microfinance.savings-account.create', 'microfinance.savings-account.show',
    'microfinance.loan-product.index', 'microfinance.loan-product.create', 'microfinance.loan-product.edit', 'microfinance.loan-product.show',
    'microfinance.loan.index', 'microfinance.loan.create', 'microfinance.loan.edit', 'microfinance.loan.show',
    'microfinance.collection.index', 'microfinance.collection.sheet',
    'microfinance.report.member-ledger', 'microfinance.report.collection-sheet', 'microfinance.report.loan-portfolio', 'microfinance.report.savings-portfolio',
    'microfinance.day-closing.index',
];
?>
<li class="nav-item {{ in_array(Route::currentRouteName(), $subMenu) ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
        <i class="nav-icon fas fa-hand-holding-usd"></i>
        <p>
            Microfinance
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        @if(auth()->user()->can('microfinance'))
            <li class="nav-item">
                <a href="{{ route('microfinance.field-officer.index') }}"
                   class="nav-link {{ in_array(Route::currentRouteName(), ['microfinance.field-officer.index', 'microfinance.field-officer.create', 'microfinance.field-officer.edit', 'microfinance.field-officer.show']) ? 'active' : '' }}">
                    <i class="far {{ in_array(Route::currentRouteName(), ['microfinance.field-officer.index', 'microfinance.field-officer.create', 'microfinance.field-officer.edit', 'microfinance.field-officer.show']) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>Field Officers (মাঠকর্মী)</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('microfinance.field-officer-assignment.index') }}"
                   class="nav-link {{ in_array(Route::currentRouteName(), ['microfinance.field-officer-assignment.index']) ? 'active' : '' }}">
                    <i class="far {{ in_array(Route::currentRouteName(), ['microfinance.field-officer-assignment.index']) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>FO Reassignments</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('microfinance.somiti.index') }}"
                   class="nav-link {{ in_array(Route::currentRouteName(), ['microfinance.somiti.index', 'microfinance.somiti.create', 'microfinance.somiti.edit', 'microfinance.somiti.show']) ? 'active' : '' }}">
                    <i class="far {{ in_array(Route::currentRouteName(), ['microfinance.somiti.index', 'microfinance.somiti.create', 'microfinance.somiti.edit', 'microfinance.somiti.show']) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>Somitis (Groups)</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('microfinance.member.index') }}"
                   class="nav-link {{ in_array(Route::currentRouteName(), ['microfinance.member.index', 'microfinance.member.create', 'microfinance.member.edit', 'microfinance.member.show']) ? 'active' : '' }}">
                    <i class="far {{ in_array(Route::currentRouteName(), ['microfinance.member.index', 'microfinance.member.create', 'microfinance.member.edit', 'microfinance.member.show']) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>Members</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('microfinance.savings-product.index') }}"
                   class="nav-link {{ in_array(Route::currentRouteName(), ['microfinance.savings-product.index', 'microfinance.savings-product.create', 'microfinance.savings-product.edit', 'microfinance.savings-product.show']) ? 'active' : '' }}">
                    <i class="far {{ in_array(Route::currentRouteName(), ['microfinance.savings-product.index', 'microfinance.savings-product.create', 'microfinance.savings-product.edit', 'microfinance.savings-product.show']) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>Savings Products</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('microfinance.savings-account.index') }}"
                   class="nav-link {{ in_array(Route::currentRouteName(), ['microfinance.savings-account.index', 'microfinance.savings-account.create', 'microfinance.savings-account.show']) ? 'active' : '' }}">
                    <i class="far {{ in_array(Route::currentRouteName(), ['microfinance.savings-account.index', 'microfinance.savings-account.create', 'microfinance.savings-account.show']) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>Member Accounts</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('microfinance.loan-product.index') }}"
                   class="nav-link {{ in_array(Route::currentRouteName(), ['microfinance.loan-product.index', 'microfinance.loan-product.create', 'microfinance.loan-product.edit', 'microfinance.loan-product.show']) ? 'active' : '' }}">
                    <i class="far {{ in_array(Route::currentRouteName(), ['microfinance.loan-product.index', 'microfinance.loan-product.create', 'microfinance.loan-product.edit', 'microfinance.loan-product.show']) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>Loan Products</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('microfinance.loan.index') }}"
                   class="nav-link {{ in_array(Route::currentRouteName(), ['microfinance.loan.index', 'microfinance.loan.create', 'microfinance.loan.show']) ? 'active' : '' }}">
                    <i class="far {{ in_array(Route::currentRouteName(), ['microfinance.loan.index', 'microfinance.loan.create', 'microfinance.loan.show']) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>Loans</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('microfinance.collection.sheet') }}"
                   class="nav-link {{ in_array(Route::currentRouteName(), ['microfinance.collection.sheet']) ? 'active' : '' }}">
                    <i class="far {{ in_array(Route::currentRouteName(), ['microfinance.collection.sheet']) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>Collection Sheet</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('microfinance.collection.index') }}"
                   class="nav-link {{ in_array(Route::currentRouteName(), ['microfinance.collection.index', 'microfinance.collection.datatable']) ? 'active' : '' }}">
                    <i class="far {{ in_array(Route::currentRouteName(), ['microfinance.collection.index', 'microfinance.collection.datatable']) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>Loan Collections</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('microfinance.report.member-ledger') }}"
                   class="nav-link {{ in_array(Route::currentRouteName(), ['microfinance.report.member-ledger']) ? 'active' : '' }}">
                    <i class="far {{ in_array(Route::currentRouteName(), ['microfinance.report.member-ledger']) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>Member Ledger</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('microfinance.report.collection-sheet') }}"
                   class="nav-link {{ in_array(Route::currentRouteName(), ['microfinance.report.collection-sheet']) ? 'active' : '' }}">
                    <i class="far {{ in_array(Route::currentRouteName(), ['microfinance.report.collection-sheet']) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>Collection Report</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('microfinance.report.loan-portfolio') }}"
                   class="nav-link {{ in_array(Route::currentRouteName(), ['microfinance.report.loan-portfolio']) ? 'active' : '' }}">
                    <i class="far {{ in_array(Route::currentRouteName(), ['microfinance.report.loan-portfolio']) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>Loan Portfolio</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('microfinance.report.savings-portfolio') }}"
                   class="nav-link {{ in_array(Route::currentRouteName(), ['microfinance.report.savings-portfolio']) ? 'active' : '' }}">
                    <i class="far {{ in_array(Route::currentRouteName(), ['microfinance.report.savings-portfolio']) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>Savings Portfolio</p>
                </a>
            </li>

            <li class="nav-item" style="border-top: 1px dashed rgba(255,255,255,0.15); margin-top: 5px; padding-top: 5px;">
                <a href="{{ route('microfinance.day-closing.index') }}"
                   class="nav-link {{ in_array(Route::currentRouteName(), ['microfinance.day-closing.index']) ? 'active' : '' }}">
                    <i class="fas fa-file-invoice-dollar nav-icon text-warning"></i>
                    <p class="text-warning font-weight-bold">Day Closing (EOD)</p>
                </a>
            </li>
        @endif
    </ul>
</li>
