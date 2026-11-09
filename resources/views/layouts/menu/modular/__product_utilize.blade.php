@php
    $currentRoute = Route::currentRouteName();
@endphp

@if(auth()->user()->can('product_utilize'))
    <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px;">
        Product Utilize
    </li>

    <!-- 0. Dashboard -->
    <li class="nav-item">
        <a href="{{ route('product-utilizes.dashboard') }}"
           class="nav-link {{ $currentRoute == 'product-utilizes.dashboard' ? 'active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
        </a>
    </li>

    <!-- 1. Add Product Utilize -->
    @if(auth()->user()->can('product_utilize_create'))
        <li class="nav-item">
            <a href="{{ route('product-utilizes.create') }}"
               class="nav-link {{ $currentRoute == 'product-utilizes.create' ? 'active' : '' }}">
                <i class="nav-icon fas fa-plus-circle"></i>
                <p>Add Product Utilize</p>
            </a>
        </li>
    @endif

    <!-- 2. Utilize List -->
    @if(auth()->user()->can('product_utilize_list'))
        <li class="nav-item">
            <a href="{{ route('product-utilizes.index') }}"
               class="nav-link {{ in_array($currentRoute, ['product-utilizes.index', 'product-utilizes.edit', 'product-utilizes.details']) ? 'active' : '' }}">
                <i class="nav-icon fas fa-history"></i>
                <p>Utilize List</p>
            </a>
        </li>
    @endif

    <!-- 3. Reports -->
    @if(auth()->user()->can('product_utilize_reports'))
        <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px; margin-top: 10px;">
            Reports
        </li>
        <li class="nav-item">
            <a href="{{ route('product-utilizes.report') }}"
               class="nav-link {{ $currentRoute == 'product-utilizes.report' ? 'active' : '' }}">
                <i class="nav-icon fas fa-chart-pie"></i>
                <p>Product Utilize Report</p>
            </a>
        </li>
    @endif
@endif
