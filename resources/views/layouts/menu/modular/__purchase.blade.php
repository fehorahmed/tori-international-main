@php
    $currentRoute = Route::currentRouteName();
@endphp

@if(auth()->user()->can('purchase'))
    <!-- Category 1: Purchase Workspace -->
    <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px;">
        Purchase Operations
    </li>

    <li class="nav-item">
        <a href="{{ route('purchase.dashboard') }}"
            class="nav-link {{ $currentRoute == 'purchase.dashboard' ? 'active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
        </a>
    </li>

    <!-- 0. Suppliers -->
    @if(auth()->user()->can('supplier'))
        <li class="nav-item">
            <a href="{{ route('supplier.index') }}"
                class="nav-link {{ str_starts_with($currentRoute, 'supplier.') ? 'active' : '' }}">
                <i class="nav-icon fas fa-truck"></i>
                <p>Suppliers</p>
            </a>
        </li>
    @endif

    <!-- 1. Purchase Create -->
    @if(auth()->user()->can('purchase_create'))
        <li class="nav-item">
            <a href="{{ route('purchase.create') }}" class="nav-link {{ $currentRoute == 'purchase.create' ? 'active' : '' }}">
                <i class="nav-icon fas fa-cart-plus"></i>
                <p>Purchase Create</p>
            </a>
        </li>
    @endif

    <!-- 2. Purchase List -->
    @if(auth()->user()->can('purchase_list'))
        <li class="nav-item">
            <a href="{{ route('purchase.index') }}"
                class="nav-link {{ in_array($currentRoute, ['purchase.index', 'purchase.edit', 'purchase.details', 'purchase.mrr_details', 'purchase.received']) ? 'active' : '' }}">
                <i class="nav-icon fas fa-history"></i>
                <p>Purchase List</p>
            </a>
        </li>
    @endif

    <!-- Category 2: Payments -->
    @if(auth()->user()->can('supplier_payment'))
        <li class="nav-header text-uppercase font-weight-bold text-muted"
            style="font-size: 0.7rem; padding-left: 10px; margin-top: 15px;">
            Supplier Ledger
        </li>

        <!-- 3. Supplier Payments -->
        <li class="nav-item">
            <a href="{{ route('purchase.supplier_payments_list') }}"
                class="nav-link {{ in_array($currentRoute, ['purchase.supplier_payments_list', 'purchase.supplier_payment_history']) ? 'active' : '' }}">
                <i class="nav-icon fas fa-money-bill-wave"></i>
                <p>Supplier Payment</p>
            </a>
        </li>
    @endif

    <!-- Category 3: Purchase Reports -->
    @if(auth()->user()->can('purchase_reports'))
        <li class="nav-header text-uppercase font-weight-bold text-muted"
            style="font-size: 0.7rem; padding-left: 10px; margin-top: 15px;">
            Purchase Reports
        </li>

        @if(auth()->user()->can('supplier_report'))
            <li class="nav-item">
                <a href="{{ route('supplier.ledger') }}" class="nav-link {{ $currentRoute == 'supplier.ledger' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-book"></i>
                    <p>Supplier Ledger</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('supplier.report') }}" class="nav-link {{ $currentRoute == 'supplier.report' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-truck"></i>
                    <p>Supplier Report</p>
                </a>
            </li>
        @endif

        @if(auth()->user()->can('purchase_report'))
            <li class="nav-item">
                <a href="{{ route('purchase.report') }}" class="nav-link {{ $currentRoute == 'purchase.report' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-chart-line"></i>
                    <p>Purchase Report</p>
                </a>
            </li>
        @endif

        @if(auth()->user()->can('purchase_details_report'))
            <li class="nav-item">
                <a href="{{ route('purchase_details.report') }}"
                    class="nav-link {{ $currentRoute == 'purchase_details.report' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-file-invoice"></i>
                    <p>Purchase Details Report</p>
                </a>
            </li>
        @endif
    @endif
@endif