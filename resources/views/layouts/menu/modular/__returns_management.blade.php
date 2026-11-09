@php
    $currentRoute = Route::currentRouteName();
@endphp

@if(auth()->user()->can('returns_management'))
    <!-- Category 1: Returns Operations -->
    <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px;">
        Returns Operations
    </li>

    <li class="nav-item">
        <a href="{{ route('returns-management.dashboard') }}"
            class="nav-link {{ $currentRoute == 'returns-management.dashboard' ? 'active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
        </a>
    </li>

    @if(auth()->user()->can('sales_return'))
        <li class="nav-item">
            <a href="{{ route('returns-management.sales-returns.index') }}"
                class="nav-link {{ request()->is('*sales-returns*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-undo-alt"></i>
                <p>Sales Return</p>
            </a>
        </li>
    @endif

    @if(auth()->user()->can('purchase_return'))
        <li class="nav-item">
            <a href="{{ route('returns-management.purchase-returns.index') }}"
                class="nav-link {{ request()->is('*purchase-returns*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-exchange-alt"></i>
                <p>Purchase Return</p>
            </a>
        </li>
    @endif

    <li class="nav-item">
        <a href="{{ route('returns-management.defect-stock.index') }}"
            class="nav-link {{ request()->is('*defect-stock*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-boxes"></i>
            <p>Defect / Damage Stock</p>
        </a>
    </li>

    <!-- Category 2: Returns Reports -->
    <li class="nav-header text-uppercase font-weight-bold text-muted"
        style="font-size: 0.7rem; padding-left: 10px; margin-top: 15px;">
        Returns Reports
    </li>

    <li class="nav-item">
        <a href="{{ route('returns-management.report.sales_returns') }}"
            class="nav-link {{ $currentRoute == 'returns-management.report.sales_returns' ? 'active' : '' }}">
            <i class="nav-icon fas fa-file-invoice"></i>
            <p>Sales Return Report</p>
        </a>
    </li>

    <li class="nav-item">
        <a href="{{ route('returns-management.report.purchase_returns') }}"
            class="nav-link {{ $currentRoute == 'returns-management.report.purchase_returns' ? 'active' : '' }}">
            <i class="nav-icon fas fa-chart-line"></i>
            <p>Purchase Return Report</p>
        </a>
    </li>

    <li class="nav-item">
        <a href="{{ route('returns-management.report.defect_stock') }}"
            class="nav-link {{ $currentRoute == 'returns-management.report.defect_stock' ? 'active' : '' }}">
            <i class="nav-icon fas fa-boxes"></i>
            <p>Defect Stock Report</p>
        </a>
    </li>

    <li class="nav-item">
        <a href="{{ route('returns-management.report.return_summary') }}"
            class="nav-link {{ $currentRoute == 'returns-management.report.return_summary' ? 'active' : '' }}">
            <i class="nav-icon fas fa-file-invoice-dollar"></i>
            <p>Return Summary Report</p>
        </a>
    </li>
@endif