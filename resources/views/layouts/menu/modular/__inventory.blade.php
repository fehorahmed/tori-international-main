@php
    $currentRoute = Route::currentRouteName();
@endphp

<li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.75rem;">Inventory Workspace</li>
<li class="nav-item">
    <a href="{{ route('inventory.dashboard') }}" class="nav-link {{ $currentRoute == 'inventory.dashboard' ? 'active' : '' }}">
        <i class="nav-icon fas fa-tachometer-alt"></i>
        <p>Dashboard</p>
    </a>
</li>
@if(auth()->user()->can('inventory'))
    <li class="nav-item">
        <a href="{{ route('inventory.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'inventory.') && $currentRoute !== 'inventory.dashboard' ? 'active' : '' }}">
            <i class="nav-icon fas fa-boxes"></i>
            <p>Real-time Inventory</p>
        </a>
    </li>
@endif

<!-- Stock Reports -->
@if(auth()->user()->can('inventory_reports'))
    <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px; margin-top: 15px;">
        Stock Reports
    </li>

    @if(auth()->user()->can('stock_report'))
    <li class="nav-item">
        <a href="{{ route('report.stock') }}"
           class="nav-link {{ $currentRoute == 'report.stock' ? 'active' : '' }}">
            <i class="nav-icon fas fa-chart-line"></i>
            <p>Stock Report</p>
        </a>
    </li>
    @endif

    @if(auth()->user()->can('stock_report'))
    <li class="nav-item">
        <a href="{{ route('report.product_ledger') }}"
           class="nav-link {{ $currentRoute == 'report.product_ledger' ? 'active' : '' }}">
            <i class="nav-icon fas fa-book-open"></i>
            <p>Product Ledger</p>
        </a>
    </li>
    @endif

    @if(auth()->user()->can('batch_stock_report'))
    <li class="nav-item">
        <a href="{{ route('report.batch_stock') }}"
           class="nav-link {{ $currentRoute == 'report.batch_stock' ? 'active' : '' }}">
            <i class="nav-icon fas fa-layer-group"></i>
            <p>Batch Stock Report</p>
        </a>
    </li>
    @endif
    @if(auth()->user()->can('stock_valuation_report'))
    <li class="nav-item">
        <a href="{{ route('report.stock_valuation') }}"
           class="nav-link {{ $currentRoute == 'report.stock_valuation' ? 'active' : '' }}">
            <i class="nav-icon fas fa-calculator"></i>
            <p>Stock Cash Pur. Report</p>
        </a>
    </li>
    @endif

    @if(auth()->user()->can('batch_stock_valuation_report'))
    <li class="nav-item">
        <a href="{{ route('report.batch_stock_valuation') }}"
           class="nav-link {{ $currentRoute == 'report.batch_stock_valuation' ? 'active' : '' }}">
            <i class="nav-icon fas fa-list-ol"></i>
            <p>Batch Cash Pur. Report</p>
        </a>
    </li>
    @endif
@endif
