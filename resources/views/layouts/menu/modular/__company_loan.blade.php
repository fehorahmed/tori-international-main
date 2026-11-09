@php
    $currentRoute = Route::currentRouteName();
@endphp

<li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px;">
    Company Loan
</li>
<li class="nav-item">
    <a href="{{ route('company_loan.dashboard') }}"
       class="nav-link {{ $currentRoute == 'company_loan.dashboard' ? 'active' : '' }}">
        <i class="nav-icon fas fa-tachometer-alt"></i>
        <p>Dashboard</p>
    </a>
</li>

<li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px; margin-top: 10px;">
    Loan Operations
</li>

@can('manage_company_loan')
<li class="nav-item">
    <a href="{{ route('company_loan.index') }}"
       class="nav-link {{ in_array($currentRoute, ['company_loan.index', 'company_loan.create', 'company_loan.show', 'company_loan.edit', 'company_loan.approval_page']) ? 'active' : '' }}">
        <i class="nav-icon fas fa-hand-holding-usd"></i>
        <p>Loan List</p>
    </a>
</li>
@endcan

@if(auth()->user()->can('company_loan_report_statement') || auth()->user()->can('company_loan_report_receivable') || auth()->user()->can('company_loan_report_payable') || auth()->user()->can('company_loan_report_due_installments'))
<li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px; margin-top: 15px;">
    Loan Reports
</li>
@endif

<!-- 2. Loan Statement -->
@can('company_loan_report_statement')
<li class="nav-item">
    <a href="{{ route('company_loan.reports.statement') }}"
       class="nav-link {{ $currentRoute == 'company_loan.reports.statement' ? 'active' : '' }}">
        <i class="nav-icon fas fa-file-invoice"></i>
        <p>Loan Statement</p>
    </a>
</li>
@endcan

<!-- 3. Receivable Summary -->
@can('company_loan_report_receivable')
<li class="nav-item">
    <a href="{{ route('company_loan.reports.receivable_summary') }}"
       class="nav-link {{ $currentRoute == 'company_loan.reports.receivable_summary' ? 'active' : '' }}">
        <i class="nav-icon fas fa-file-download"></i>
        <p>Receivable Summary</p>
    </a>
</li>
@endcan

<!-- 4. Payable Summary -->
@can('company_loan_report_payable')
<li class="nav-item">
    <a href="{{ route('company_loan.reports.payable_summary') }}"
       class="nav-link {{ $currentRoute == 'company_loan.reports.payable_summary' ? 'active' : '' }}">
        <i class="nav-icon fas fa-file-upload"></i>
        <p>Payable Summary</p>
    </a>
</li>
@endcan

<!-- 5. Due Installments -->
@can('company_loan_report_due_installments')
<li class="nav-item">
    <a href="{{ route('company_loan.reports.due_installments') }}"
       class="nav-link {{ $currentRoute == 'company_loan.reports.due_installments' ? 'active' : '' }}">
        <i class="nav-icon fas fa-calendar-times"></i>
        <p>Due Installments</p>
    </a>
</li>
@endcan
