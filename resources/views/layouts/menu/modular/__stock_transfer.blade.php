@php
    $currentRoute = Route::currentRouteName();
@endphp

@if(auth()->user()->can('stock_transfer'))
    <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px; margin-top: 10px;">
        Stock Transfer
    </li>
    <!-- Dashboard -->
    <li class="nav-item">
        <a href="{{ route('stock-transfers.dashboard') }}"
           class="nav-link {{ $currentRoute == 'stock-transfers.dashboard' ? 'active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
        </a>
    </li>

    <!-- 1. Transfer Stock (Standard & Outbound Returns) -->
    @if(auth()->user()->can('stock_transfer_create'))
        <li class="nav-item">
            <a href="{{ route('stock-transfers.index') }}"
               class="nav-link {{ in_array($currentRoute, ['stock-transfers.index', 'stock-transfers.create', 'stock-transfers.edit']) ? 'active' : '' }}">
                <i class="nav-icon fas fa-shipping-fast"></i>
                <p>Transfer Stock</p>
            </a>
        </li>
    @endif

    <!-- 2. Receive Stock (Standard incoming) -->
    @if(auth()->user()->can('stock_transfer_receive'))
        <li class="nav-item">
            <a href="{{ route('stock-transfers.receive_list') }}"
               class="nav-link {{ in_array($currentRoute, ['stock-transfers.receive_list', 'stock-transfers.receive', 'stock-transfers.details']) ? 'active' : '' }}">
                <i class="nav-icon fas fa-boxes"></i>
                <p>Receive Stock</p>
            </a>
        </li>

        <!-- 3. Return Transfer (Dispatch shortages back) -->
        <li class="nav-item">
            <a href="{{ route('stock-transfers.pending_dispatch_returns') }}"
               class="nav-link {{ in_array($currentRoute, ['stock-transfers.pending_dispatch_returns', 'stock-transfers.pending_dispatch_returns_datatable']) ? 'active' : '' }}">
                <i class="nav-icon fas fa-reply"></i>
                <p>Return Transfer</p>
            </a>
        </li>

        <!-- 4. Receive Return (Original sender receives shortages back) -->
        <li class="nav-item">
            <a href="{{ route('stock-transfers.pending_returns') }}"
               class="nav-link {{ in_array($currentRoute, ['stock-transfers.pending_returns', 'stock-transfers.return_receive']) ? 'active' : '' }}">
                <i class="nav-icon fas fa-undo"></i>
                <p>Receive Return</p>
            </a>
        </li>

        <!-- 4. Incoming History (Standard Receipts & Return Receipts) -->
        <li class="nav-item">
            <a href="{{ route('stock-transfers.incoming_history') }}"
               class="nav-link {{ $currentRoute == 'stock-transfers.incoming_history' ? 'active' : '' }}">
                <i class="nav-icon fas fa-history"></i>
                <p>Incoming History</p>
            </a>
        </li>
    @endif

    <!-- Reports Section -->
    @if(auth()->user()->can('stock_transfer_reports'))
        <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px; margin-top: 15px;">
            Stock Transfer Reports
        </li>

        @if(auth()->user()->can('stock_transfer_report'))
            <li class="nav-item">
                <a href="{{ route('stock-transfers.report') }}"
                   class="nav-link {{ $currentRoute == 'stock-transfers.report' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-file-invoice"></i>
                    <p>Transfer Report</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('stock-transfers.return_report') }}"
                   class="nav-link {{ $currentRoute == 'stock-transfers.return_report' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-file-invoice"></i>
                    <p>Return Report</p>
                </a>
            </li>
        @endif
    @endif
@endif
