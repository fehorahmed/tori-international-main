<?php
$subMenu = [
    'company_loan.index', 'company_loan.create', 'company_loan.show', 'company_loan.edit', 'company_loan.approval_page',
    'company_loan.reports.statement', 'company_loan.reports.receivable_summary', 'company_loan.reports.payable_summary', 'company_loan.reports.due_installments'
];
?>
<li class="nav-item {{ in_array(Route::currentRouteName(), $subMenu) ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
        <i class="nav-icon fas fa-hand-holding-usd"></i>
        <p>
            Company Loan
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('company_loan.index') }}"
               class="nav-link {{ Route::currentRouteName() == 'company_loan.index' ? 'active' : '' }}">
                <i class="far {{ Route::currentRouteName() == 'company_loan.index' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                <p>Loan List</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('company_loan.reports.statement') }}"
               class="nav-link {{ Route::currentRouteName() == 'company_loan.reports.statement' ? 'active' : '' }}">
                <i class="far {{ Route::currentRouteName() == 'company_loan.reports.statement' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                <p>Loan Statement</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('company_loan.reports.receivable_summary') }}"
               class="nav-link {{ Route::currentRouteName() == 'company_loan.reports.receivable_summary' ? 'active' : '' }}">
                <i class="far {{ Route::currentRouteName() == 'company_loan.reports.receivable_summary' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                <p>Receivable Summary</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('company_loan.reports.payable_summary') }}"
               class="nav-link {{ Route::currentRouteName() == 'company_loan.reports.payable_summary' ? 'active' : '' }}">
                <i class="far {{ Route::currentRouteName() == 'company_loan.reports.payable_summary' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                <p>Payable Summary</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('company_loan.reports.due_installments') }}"
               class="nav-link {{ Route::currentRouteName() == 'company_loan.reports.due_installments' ? 'active' : '' }}">
                <i class="far {{ Route::currentRouteName() == 'company_loan.reports.due_installments' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                <p>Due Installments</p>
            </a>
        </li>
    </ul>
</li>
