<?php
$subMenu = [
    'product-utilizes.index',
    'product-utilizes.create',
    'product-utilizes.edit',
    'product-utilizes.details',
];

?>
@if(auth()->user()->can('product_utilize'))
    <li class="nav-item {{ in_array(Route::currentRouteName(), $subMenu) ? 'menu-open' : '' }}">
        <a href="#"
           class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
            <i class="nav-icon fas fa-tasks"></i>
            <p>
                Product Utilize
                <i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">

            <?php
                $subSubMenu = ['product-utilizes.create'];
            ?>
            @if(auth()->user()->can('product_utilize_create'))
                <li class="nav-item">
                    <a href="{{ route('product-utilizes.create') }}"
                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                        <i class="fa fa-plus nav-icon"></i>
                        <p>Add Product Utilize</p>
                    </a>
                </li>
            @endif
                <?php
                $subSubMenu = [
                    'product-utilizes.index',
                    'product-utilizes.edit',
                    'product-utilizes.details',
                ];
                ?>
            @if(auth()->user()->can('product_utilize_list'))
                <li class="nav-item">
                    <a href="{{ route('product-utilizes.index') }}"
                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                        <i class="fa fa-history nav-icon"></i>
                        <p>Utilize List</p>
                    </a>
                </li>
            @endif
        </ul>
    </li>

@endif
