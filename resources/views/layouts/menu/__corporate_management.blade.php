<?php
$subMenu = [
    'corporate-customer.index', 'corporate-customer.create', 'corporate-customer.edit', 'corporate-customer.show',
    'corporate-sales.index', 'corporate-sales.create', 'corporate-sales.show', 'corporate-sales.edit',
];
?>
<li class="nav-item {{ in_array(Route::currentRouteName(), $subMenu) ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
        <i class="nav-icon fas fa-building"></i>
        <p>Corporate Invoices <i class="right fas fa-angle-left"></i></p>
    </a>
    <ul class="nav nav-treeview">
        @if(auth()->user()->can('customer'))
            <?php 
                $subSubMenu = [
                    'corporate-customer.index', 'corporate-customer.create', 'corporate-customer.edit', 'corporate-customer.show',
                ];
            ?>
            <li class="nav-item">
                <a href="{{ route('corporate-customer.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                    <i class="far {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>Corporate Clients</p>
                </a>
            </li>
        @endif
        @if(auth()->user()->can('sales_list'))
            <?php 
                $subSubMenu = [
                    'corporate-sales.index', 'corporate-sales.create', 'corporate-sales.show', 'corporate-sales.edit',
                ];
            ?>
            <li class="nav-item">
                <a href="{{ route('corporate-sales.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                    <i class="far {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>Corporate Invoice</p>
                </a>
            </li>
        @endif
    </ul>
</li>
