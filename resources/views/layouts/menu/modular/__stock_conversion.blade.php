@php
    $currentRoute = Route::currentRouteName();
@endphp

@if(auth()->user()->can('stock_conversion_management'))
    <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px; margin-top: 10px;">
        Stock Conversion
    </li>

    <!-- Dashboard -->
    <li class="nav-item">
        <a href="{{ route('stock-conversions.dashboard') }}"
           class="nav-link {{ $currentRoute == 'stock-conversions.dashboard' ? 'active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
        </a>
    </li>

    <!-- 1. Create Conversion -->
    @if(auth()->user()->can('stock_conversion_create'))
        <li class="nav-item">
            <a href="{{ route('stock-conversions.create') }}"
               class="nav-link {{ $currentRoute == 'stock-conversions.create' ? 'active' : '' }}">
                <i class="nav-icon fas fa-plus-circle"></i>
                <p>Create Conversion</p>
            </a>
        </li>
    @endif

    <!-- 2. Conversion List -->
    @if(auth()->user()->can('stock_conversion'))
        <li class="nav-item">
            <a href="{{ route('stock-conversions.index') }}"
               class="nav-link {{ in_array($currentRoute, ['stock-conversions.index', 'stock-conversions.edit', 'stock-conversions.show']) ? 'active' : '' }}">
                <i class="nav-icon fas fa-list-ul"></i>
                <p>Conversion List</p>
            </a>
        </li>
    @endif

    <!-- Reports Section -->
    @if(auth()->user()->can('stock_conversion_reports'))
        <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px; margin-top: 15px;">
            Conversion Reports
        </li>

        @if(auth()->user()->can('stock_conversion_report'))
            <li class="nav-item">
                <a href="{{ route('stock-conversions.report') }}"
                   class="nav-link {{ $currentRoute == 'stock-conversions.report' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-file-invoice text-danger"></i>
                    <p>Stock Conversion Report</p>
                </a>
            </li>
        @endif
    @endif
@endif
