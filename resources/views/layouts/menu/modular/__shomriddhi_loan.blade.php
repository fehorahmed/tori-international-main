@php
    $currentRoute = Route::currentRouteName();
@endphp

@if(auth()->user()->can('shomriddhi_loan'))
<li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.75rem;">Shomriddhi Loan</li>

@if(auth()->user()->can('shomriddhi_loan_dashboard'))
<li class="nav-item">
    <a href="{{ route('shomriddhi-loan.dashboard') }}" class="nav-link {{ $currentRoute == 'shomriddhi-loan.dashboard' ? 'active' : '' }}">
        <i class="nav-icon fas fa-tachometer-alt"></i>
        <p>Dashboard</p>
    </a>
</li>
@endif

@if(auth()->user()->can('shomriddhi_loan_accounts'))
<li class="nav-item">
    <a href="{{ route('shomriddhi-loan.accounts.index') }}" class="nav-link {{ in_array($currentRoute, ['shomriddhi-loan.accounts.index', 'shomriddhi-loan.accounts.create', 'shomriddhi-loan.accounts.show', 'shomriddhi-loan.accounts.edit']) ? 'active' : '' }}">
        <i class="nav-icon fas fa-file-signature"></i>
        <p>Loan Accounts</p>
    </a>
</li>
@endif

@if(auth()->user()->can('shomriddhi_loan_collections'))
<li class="nav-item">
    <a href="{{ route('shomriddhi-loan.collections.create') }}" class="nav-link {{ $currentRoute == 'shomriddhi-loan.collections.create' ? 'active' : '' }}">
        <i class="nav-icon fas fa-hand-holding-usd"></i>
        <p>Collect Installment</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('shomriddhi-loan.collections.index') }}" class="nav-link {{ in_array($currentRoute, ['shomriddhi-loan.collections.index', 'shomriddhi-loan.collections.show']) ? 'active' : '' }}">
        <i class="nav-icon fas fa-history"></i>
        <p>Collection History</p>
    </a>
</li>
@endif

@if(auth()->user()->can('product_loans'))
<li class="nav-header text-uppercase font-weight-bold text-muted mt-2" style="font-size: 0.75rem;">Product Loan (EMI)</li>
<li class="nav-item">
    <a href="{{ route('shomriddhi-loan.product-loans.create') }}" class="nav-link {{ $currentRoute == 'shomriddhi-loan.product-loans.create' ? 'active' : '' }}">
        <i class="nav-icon fas fa-cart-plus"></i>
        <p>New Product Loan</p>
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('shomriddhi-loan.product-loans.index') }}" class="nav-link {{ in_array($currentRoute, ['shomriddhi-loan.product-loans.index', 'shomriddhi-loan.product-loans.edit', 'shomriddhi-loan.product-loans.show']) ? 'active' : '' }}">
        <i class="nav-icon fas fa-list"></i>
        <p>Product Loans List</p>
    </a>
</li>
@endif

@if(auth()->user()->can('shomriddhi_loan_reschedule'))
<li class="nav-item">
    <a href="{{ route('shomriddhi-loan.reschedule.index') }}" class="nav-link {{ $currentRoute == 'shomriddhi-loan.reschedule.index' ? 'active' : '' }}">
        <i class="nav-icon fas fa-calendar-times"></i>
        <p>Holiday Reschedule</p>
    </a>
</li>
@endif

@if(auth()->user()->can('shomriddhi_loan_reports'))
<li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px; margin-top: 15px;">
    Reports
</li>

<li class="nav-item">
    <a href="{{ route('shomriddhi-loan.reports.loan_ledger') }}" class="nav-link {{ $currentRoute == 'shomriddhi-loan.reports.loan_ledger' ? 'active' : '' }}">
        <i class="nav-icon fas fa-book"></i>
        <p>Loan Ledger</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('shomriddhi-loan.reports.collection_summary') }}" class="nav-link {{ $currentRoute == 'shomriddhi-loan.reports.collection_summary' ? 'active' : '' }}">
        <i class="nav-icon fas fa-chart-bar"></i>
        <p>Collection Summary</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('shomriddhi-loan.reports.overdue_installments') }}" class="nav-link {{ $currentRoute == 'shomriddhi-loan.reports.overdue_installments' ? 'active' : '' }}">
        <i class="nav-icon fas fa-exclamation-triangle"></i>
        <p>Overdue Installments</p>
    </a>
</li>
@endif
@endif
