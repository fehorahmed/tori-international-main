@php
    $currentRoute = Route::currentRouteName();
@endphp

<!-- Dashboard -->
 <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px;">
    Ecommerce Management
</li>
<li class="nav-item">
    <a href="{{ route('ecom_dashboard') }}"
       class="nav-link {{ $currentRoute == 'ecom_dashboard' ? 'active' : '' }}">
        <i class="nav-icon fas fa-tachometer-alt"></i>
        <p>Dashboard</p>
    </a>
</li>

<!-- Category 1: Ecom Configurations -->
<li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px; margin-top: 10px;">
    Ecom Configurations
</li>

<!-- 1. Settings -->
@if(auth()->user()->can('ecommerce_settings'))
<li class="nav-item">
    <a href="{{ route('ecom_settings.update', ['setting' => 1]) }}"
       class="nav-link {{ $currentRoute == 'ecom_settings.update' ? 'active' : '' }}">
        <i class="nav-icon fas fa-cogs"></i>
        <p>Settings</p>
    </a>
</li>
@endif

<!-- 2. Banners -->
@if(auth()->user()->can('ecommerce_banner'))
<li class="nav-item">
    <a href="{{ route('ecom_banner') }}"
       class="nav-link {{ in_array($currentRoute, ['ecom_banner', 'ecom_banner.add', 'ecom_banner.edit']) ? 'active' : '' }}">
        <i class="nav-icon fas fa-images"></i>
        <p>Banners</p>
    </a>
</li>
@endif

<!-- 3. Gallery -->
@if(auth()->user()->can('ecommerce_gallery'))
<li class="nav-item">
    <a href="{{ route('ecom_gallery') }}"
       class="nav-link {{ in_array($currentRoute, ['ecom_gallery', 'ecom_gallery.add', 'ecom_gallery.edit']) ? 'active' : '' }}">
        <i class="nav-icon fas fa-photo-video"></i>
        <p>Gallery</p>
    </a>
</li>
@endif

<!-- 4. Pages -->
@if(auth()->user()->can('ecommerce_page'))
<li class="nav-item">
    <a href="{{ route('ecom_page') }}"
       class="nav-link {{ in_array($currentRoute, ['ecom_page', 'ecom_page.add', 'ecom_page.edit']) ? 'active' : '' }}">
        <i class="nav-icon fas fa-file-alt"></i>
        <p>Pages</p>
    </a>
</li>
@endif

<!-- 5. Coupons -->
@if(auth()->user()->can('ecommerce_coupon'))
<li class="nav-item">
    <a href="{{ route('ecom_coupon') }}"
       class="nav-link {{ in_array($currentRoute, ['ecom_coupon', 'ecom_coupon.add', 'ecom_coupon.edit']) ? 'active' : '' }}">
        <i class="nav-icon fas fa-ticket-alt"></i>
        <p>Coupons</p>
    </a>
</li>
@endif

<!-- Category 2: Catalog & Products -->
<li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px; margin-top: 15px;">
    Catalog & Products
</li>

<!-- 6. Products -->
@if(auth()->user()->can('ecommerce_product'))
<li class="nav-item">
    <a href="{{ route('ecom_product') }}"
       class="nav-link {{ in_array($currentRoute, ['ecom_product', 'ecom_product.add', 'ecom_product.edit', 'ecom_product.images', 'ecom_product.filters']) && request('offer') != 1 ? 'active' : '' }}">
        <i class="nav-icon fas fa-box-open"></i>
        <p>Products</p>
    </a>
</li>
@endif

<!-- 7. Offer Products -->
@if(auth()->user()->can('ecommerce_product'))
<li class="nav-item">
    <a href="{{ route('ecom_product', ['offer' => 1]) }}"
       class="nav-link {{ in_array($currentRoute, ['ecom_product', 'ecom_product.add', 'ecom_product.edit', 'ecom_product.images']) && request('offer') == 1 ? 'active' : '' }}">
        <i class="nav-icon fas fa-tags"></i>
        <p>Offer Products</p>
    </a>
</li>
@endif

<!-- 8. Filters -->
@if(auth()->user()->can('ecommerce_filter'))
<li class="nav-item">
    <a href="{{ route('filters.index') }}"
       class="nav-link {{ in_array($currentRoute, ['filters.index', 'filters.create', 'filters.edit']) ? 'active' : '' }}">
        <i class="nav-icon fas fa-filter"></i>
        <p>Filters</p>
    </a>
</li>
@endif

<!-- Category 3: Sales & Interactions -->
<li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px; margin-top: 15px;">
    Sales & Interactions
</li>

<!-- 9. Orders -->
@if(auth()->user()->can('ecommerce_order'))
<li class="nav-item">
    <a href="{{ route('ecom_order') }}"
       class="nav-link {{ in_array($currentRoute, ['ecom_order', 'ecom_order.add', 'ecom_order.edit', 'ecom_order.details', 'ecom_order.confirm']) ? 'active' : '' }}">
        <i class="nav-icon fas fa-shopping-bag"></i>
        <p>Orders</p>
    </a>
</li>
@endif

<!-- 10. Blogs -->
@if(auth()->user()->can('ecommerce_blog'))
<li class="nav-item">
    <a href="{{ route('ecom_blog') }}"
       class="nav-link {{ in_array($currentRoute, ['ecom_blog', 'ecom_blog.add', 'ecom_blog.edit']) ? 'active' : '' }}">
        <i class="nav-icon fas fa-blog"></i>
        <p>Blogs</p>
    </a>
</li>
@endif

<!-- 11. Contacts -->
@if(auth()->user()->can('ecommerce_contact'))
<li class="nav-item">
    <a href="{{ route('ecom_contact') }}"
       class="nav-link {{ in_array($currentRoute, ['ecom_contact', 'ecom_contact.add', 'ecom_contact.edit']) ? 'active' : '' }}">
        <i class="nav-icon fas fa-address-book"></i>
        <p>Contacts</p>
    </a>
</li>
@endif

@can('ecommerce_product')
<li class="nav-item"><a class="nav-link {{ request()->routeIs('product-packages.*') ? 'active' : '' }}" href="{{ route('product-packages.index') }}"><i class="nav-icon fas fa-box-open"></i><p>Product Packages</p></a></li>
@endcan

@can('ecommerce_order')<li class="nav-item"><a class="nav-link {{ request()->routeIs('package-orders.*') ? 'active' : '' }}" href="{{ route('package-orders.index') }}"><i class="nav-icon fas fa-boxes"></i><p>Package Orders</p></a></li>@endcan
