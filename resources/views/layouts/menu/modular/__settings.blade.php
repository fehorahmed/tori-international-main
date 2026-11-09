<li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.75rem;">System Settings</li>

@if(auth()->user()->can('settings'))
    <li class="nav-item">
        <a href="{{ route('settings.dashboard') }}" class="nav-link {{ Route::currentRouteName() == 'settings.dashboard' ? 'active' : '' }}">
            <i class="nav-icon fas fa-cogs"></i>
            <p>Settings Dashboard</p>
        </a>
    </li>
@endif
@if(auth()->user()->can('warehouse') && auth()->user()->role != 'Admin')
    <li class="nav-item d-none">
        <a href="{{ route('warehouses.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'warehouses.') ? 'active' : '' }}">
            <i class="nav-icon fas fa-warehouse"></i>
            <p>Warehouses</p>
        </a>
    </li>
@endif

<li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-top: 10px;">Purchase Settings</li>


@if(auth()->user()->can('product_unit'))
    <li class="nav-item">
        <a href="{{ route('unit.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'unit.') ? 'active' : '' }}">
            <i class="nav-icon fas fa-balance-scale"></i>
            <p>Units</p>
        </a>
    </li>
@endif
@if(auth()->user()->can('brand'))
    <li class="nav-item">
        <a href="{{ route('brand.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'brand.') ? 'active' : '' }}">
            <i class="nav-icon fas fa-copyright"></i>
            <p>Brands</p>
        </a>
    </li>
@endif
@if(auth()->user()->can('product_category'))
    <li class="nav-item">
        <a href="{{ route('product-categories.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'product-categories.') ? 'active' : '' }}">
            <i class="nav-icon fas fa-folder"></i>
            <p>Product Category</p>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('sub-category.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'sub-category.') ? 'active' : '' }}">
            <i class="nav-icon fas fa-folder-open"></i>
            <p>Product Sub Category</p>
        </a>
    </li>
@endif
@if(auth()->user()->can('product'))
    <li class="nav-item">
        <a href="{{ route('product.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'product.') ? 'active' : '' }}">
            <i class="nav-icon fas fa-box"></i>
            <p>Products List</p>
        </a>
    </li>
@endif
@if(auth()->user()->can('price_book'))
    <li class="nav-item">
        <a href="{{ route('price_book.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'price_book.') ? 'active' : '' }}">
            <i class="nav-icon fas fa-book"></i>
            <p>Price Book</p>
        </a>
    </li>
@endif

<li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-top: 10px;">Location Settings</li>

@if(auth()->user()->can('location_setup'))
<li class="nav-item">
    <a href="{{ route('locations.index', 'divisions') }}" class="nav-link {{ request()->is('admin/locations*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-map-marked-alt"></i>
        <p>Location Setup</p>
    </a>
</li>
@endif

@if(auth()->user()->can('settings_report'))
    <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-top: 15px;">Settings Reports</li>
    
    @if(auth()->user()->can('report_brand'))
    <li class="nav-item">
        <a href="{{ route('report.brand') }}" class="nav-link {{ $currentRoute == 'report.brand' ? 'active' : '' }}">
            <i class="nav-icon fas fa-copyright"></i>
            <p>Brand Report</p>
        </a>
    </li>
    @endif
    
    @if(auth()->user()->can('report_category'))
    <li class="nav-item">
        <a href="{{ route('report.category') }}" class="nav-link {{ $currentRoute == 'report.category' ? 'active' : '' }}">
            <i class="nav-icon fas fa-folder"></i>
            <p>Category Report</p>
        </a>
    </li>
    @endif
    
    @if(auth()->user()->can('report_sub_category'))
    <li class="nav-item">
        <a href="{{ route('report.sub_category') }}" class="nav-link {{ $currentRoute == 'report.sub_category' ? 'active' : '' }}">
            <i class="nav-icon fas fa-folder-open"></i>
            <p>Sub-Category Report</p>
        </a>
    </li>
    @endif
    
    @if(auth()->user()->can('report_product'))
    <li class="nav-item">
        <a href="{{ route('report.product') }}" class="nav-link {{ $currentRoute == 'report.product' ? 'active' : '' }}">
            <i class="nav-icon fas fa-box"></i>
            <p>Product Report</p>
        </a>
    </li>
    @endif

    @if(auth()->user()->can('report_price_book_history'))
    <li class="nav-item">
        <a href="{{ route('report.price_book') }}" class="nav-link {{ $currentRoute == 'report.price_book' ? 'active' : '' }}">
            <i class="nav-icon fas fa-book-open"></i>
            <p>Price Book Report</p>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('report.price_book_history') }}" class="nav-link {{ $currentRoute == 'report.price_book_history' ? 'active' : '' }}">
            <i class="nav-icon fas fa-history"></i>
            <p>Price Book History</p>
        </a>
    </li>
    @endif
@endif
