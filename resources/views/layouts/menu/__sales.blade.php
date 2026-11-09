<?php
$subMenu = [
    'customer.index','customer.create','customer.edit','customer.show','sales.create',
    'pos','sales.index','sales.edit','sales.details','customer.advance',
    'advance-sales.index','advance-sales.create','advance-sales.show','advance-sales.edit', 'advance-sales.delivery',
    'emi-sales.index','emi-sales.create','emi-sales.show','emi-sales.edit',

    'corporate-customer.index', 'corporate-customer.create', 'corporate-customer.edit', 'corporate-customer.show',
    'corporate-sales.index', 'corporate-sales.create', 'corporate-sales.show', 'corporate-sales.edit',

    'dealer.index', 'dealer.create', 'dealer.edit', 'dealer.show',
    'dealer-sales.index', 'dealer-sales.create', 'dealer-sales.show', 'dealer-sales.edit',

    'service.request.index', 'service.request.create', 'service.request.edit', 'service.request.details',
    'service.technician.index', 'service.technician.create', 'service.technician.edit',
    'service.panel.index', 'service.panel.update_form'
];
?>
@if(auth()->user()->can('sales'))
    <li class="nav-item {{ in_array(Route::currentRouteName(), $subMenu) ? 'menu-open' : '' }}">
        <a href="#"
           class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
            <i class="nav-icon fas fa-cart-plus"></i>
            <p style="font-size: 13.5px;">
                Sales
                <i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            
            <?php
            $subSubMenu = ['customer.index','customer.create','customer.edit','customer.show'];
            ?>
            @if(auth()->user()->can('customer'))
                <li class="nav-item">
                    <a href="{{ route('customer.index') }}"
                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                        <i class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                        <p>Customers</p>
                    </a>
                </li>
            @endif
            @if(auth()->user()->can('advance_from_customer'))
                {{-- <li class="nav-item">
                    <a href="{{ route('customer.advance') }}"
                       class="nav-link {{ Route::currentRouteName() == 'customer.advance' ? 'active' : '' }}">
                        <i class="far  {{ Route::currentRouteName() == 'customer.advance' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                        <p>Advance from Customer</p>
                    </a>
                </li> --}}
            @endif
            <?php
            $subSubMenu = ['pos'];
            ?>
            @if(auth()->user()->can('pos'))
                {{-- <li class="nav-item">
                    <a href="{{ route('pos') }}"
                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                        <i class="fa fa-plus nav-icon"></i>
                        <p>POS</p>
                    </a>
                </li> --}}
            @endif
            <?php
            $subSubMenu = ['advance-sales.index','advance-sales.create','advance-sales.show','advance-sales.edit', 'advance-sales.delivery'];
            ?>
            @if(auth()->user()->can('sales_list'))
                <li class="nav-item">
                    <a href="{{ route('advance-sales.index') }}"
                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                        <i class="fa fa-hand-holding-usd nav-icon"></i>
                        <p>Pre-Orders</p>
                    </a>
                </li>
            @endif
            <?php
            $subSubMenu = ['sales.index','sales.edit','sales.details'];
            ?>
            @if(auth()->user()->can('sales_list'))
                <li class="nav-item">
                    <a href="{{ route('sales.index') }}"
                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                        <i class="fa fa-history nav-icon"></i>
                        <p>Sales Invoice</p>
                    </a>
                </li>
            @endif
            <?php
            $subSubMenu = ['emi-sales.index','emi-sales.create','emi-sales.show','emi-sales.edit'];
            ?>
            @if(auth()->user()->can('sales_list'))
                <li class="nav-item">
                    <a href="{{ route('emi-sales.index') }}"
                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                        <i class="fa fa-calendar-alt nav-icon"></i>
                        <p>EMI Invoice</p>
                    </a>
                </li>
            @endif

            @include('layouts.menu.__corporate_management')
            @include('layouts.menu.__dealer_management')
            @include('layouts.menu.__service')
        </ul>
    </li>
@endif
<?php
$subMenu = [
    'sales-return.create','sales-return.index','sales-return.details',
];
?>
@if(auth()->user()->can('sales'))
    <li class="nav-item d-none {{ in_array(Route::currentRouteName(), $subMenu) ? 'menu-open' : '' }}">
        <a href="#"
           class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
            <i class="nav-icon fas fa-cart-plus"></i>
            <p style="font-size: 13.5px;">
                Sales Return
                <i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <?php
            $subSubMenu = ['sales-return.create'];
            ?>
            @if(auth()->user()->can('sales_create'))
                <li class="nav-item">
                    <a href="{{ route('sales-return.create') }}"
                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                        <i class="fa fa-plus nav-icon"></i>
                        <p>Sales Return Create</p>
                    </a>
                </li>
            @endif
            <?php
            $subSubMenu = ['sales-return.index','sales-return.details'];
            ?>
            @if(auth()->user()->can('sales_list'))
                <li class="nav-item">
                    <a href="{{ route('sales-return.index') }}"
                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                        <i class="fa fa-history nav-icon"></i>
                        <p>Sales Return Lists</p>
                    </a>
                </li>
            @endif
        </ul>
    </li>
@endif
