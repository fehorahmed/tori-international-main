@if(auth()->user()->can('fixed_asset'))
<li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.75rem;">Fixed Asset Management</li>

<!-- Dashboard -->
<li class="nav-item">
    <a href="{{ route('fixed-assets.dashboard') }}" class="nav-link {{ request()->routeIs('fixed-assets.dashboard') ? 'active' : '' }}">
        <i class="nav-icon fas fa-tachometer-alt"></i>
        <p>Dashboard</p>
    </a>
</li>


@if(auth()->user()->can('fixed_asset_category_view'))
<li class="nav-item">
    <a href="{{ route('fixed-assets.categories.index') }}" class="nav-link {{ request()->routeIs('fixed-assets.categories.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-tags"></i>
        <p>Categories</p>
    </a>
</li>
@endif

@if(auth()->user()->can('fixed_asset_create'))
<li class="nav-item">
    <a href="{{ route('fixed-assets.create') }}" class="nav-link {{ request()->routeIs('fixed-assets.create') ? 'active' : '' }}">
        <i class="nav-icon fas fa-plus-circle"></i>
        <p>Add Asset</p>
    </a>
</li>
@endif

@if(auth()->user()->can('fixed_asset_view'))
<li class="nav-item">
    <a href="{{ route('fixed-assets.index') }}" class="nav-link {{ request()->routeIs('fixed-assets.index', 'fixed-assets.show', 'fixed-assets.edit', 'fixed-assets.sell-form', 'fixed-assets.scrap-form') ? 'active' : '' }}">
        <i class="nav-icon fas fa-list"></i>
        <p>Asset Register</p>
    </a>
</li>
@endif

@if(auth()->user()->can('fixed_asset_depreciation_run'))
<li class="nav-item">
    <a href="{{ route('fixed-assets.depreciation.index') }}" class="nav-link {{ request()->routeIs('fixed-assets.depreciation.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-calculator"></i>
        <p>Run Depreciation</p>
    </a>
</li>
@endif

@if(auth()->user()->can('fixed_asset_view'))
<li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-top: 10px;">Reports</li>

<li class="nav-item">
    <a href="{{ route('fixed-assets.reports.register') }}" class="nav-link {{ request()->routeIs('fixed-assets.reports.register') ? 'active' : '' }}">
        <i class="nav-icon fas fa-file-invoice"></i>
        <p>Asset Register (FAR)</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('fixed-assets.reports.depreciation') }}" class="nav-link {{ request()->routeIs('fixed-assets.reports.depreciation') ? 'active' : '' }}">
        <i class="nav-icon fas fa-chart-line"></i>
        <p>Depreciation Schedule</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('fixed-assets.reports.addition_disposal') }}" class="nav-link {{ request()->routeIs('fixed-assets.reports.addition_disposal') ? 'active' : '' }}">
        <i class="nav-icon fas fa-exchange-alt"></i>
        <p>Addition & Disposal</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('fixed-assets.reports.category_summary') }}" class="nav-link {{ request()->routeIs('fixed-assets.reports.category_summary') ? 'active' : '' }}">
        <i class="nav-icon fas fa-chart-pie"></i>
        <p>Category Summary</p>
    </a>
</li>
@endif

@endif
