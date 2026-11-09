<?php
$conversionMenu = [
    'stock-conversions.index',
    'stock-conversions.create',
    'stock-conversions.edit',
    'stock-conversions.show',
];
?>
@if(auth()->user()->can('stock_transfer'))
    <li class="nav-item {{ in_array(Route::currentRouteName(), $conversionMenu) ? 'menu-open' : '' }}">
        <a href="#"
           class="nav-link {{ in_array(Route::currentRouteName(), $conversionMenu) ? 'active' : '' }}">
            <i class="nav-icon fas fa-recycle"></i>
            <p>
                Stock Conversion
                <i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            @if(auth()->user()->can('stock_transfer_create'))
                <li class="nav-item">
                    <a href="{{ route('stock-conversions.create') }}"
                       class="nav-link {{ Route::currentRouteName() == 'stock-conversions.create' ? 'active' : '' }}">
                        <i class="fas fa-plus nav-icon"></i>
                        <p>Create Conversion</p>
                    </a>
                </li>
            @endif
            <li class="nav-item">
                <a href="{{ route('stock-conversions.index') }}"
                   class="nav-link {{ in_array(Route::currentRouteName(), ['stock-conversions.index', 'stock-conversions.edit', 'stock-conversions.show']) ? 'active' : '' }}">
                    <i class="fas fa-list nav-icon"></i>
                    <p>Conversion List</p>
                </a>
            </li>
        </ul>
    </li>
@endif
