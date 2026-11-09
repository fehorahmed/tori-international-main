<?php
$subMenu = [
    'dealer.index', 'dealer.create', 'dealer.edit', 'dealer.show',
    'dealer-sales.index', 'dealer-sales.create', 'dealer-sales.show', 'dealer-sales.edit',
];
?>
<li class="nav-item {{ in_array(Route::currentRouteName(), $subMenu) ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
        <i class="nav-icon fas fa-handshake"></i>
        <p>Dealer Invoices <i class="right fas fa-angle-left"></i></p>
    </a>
    <ul class="nav nav-treeview">
        @if(auth()->user()->can('customer'))
            <?php 
                $subSubMenu = [
                    'dealer.index', 'dealer.create', 'dealer.edit', 'dealer.show',
                ];
            ?>
            <li class="nav-item">
                <a href="{{ route('dealer.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                    <i class="far {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>Dealer Clients</p>
                </a>
            </li>
        @endif
        @if(auth()->user()->can('sales_list'))
            <?php 
                $subSubMenu = [
                    'dealer-sales.index', 'dealer-sales.create', 'dealer-sales.show', 'dealer-sales.edit',
                ];
            ?>
            <li class="nav-item">
                <a href="{{ route('dealer-sales.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                    <i class="far {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>Dealer Invoice</p>
                </a>
            </li>
        @endif
    </ul>
</li>
