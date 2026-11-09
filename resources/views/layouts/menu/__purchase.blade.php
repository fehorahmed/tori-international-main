
<?php
$subMenu = [
    'unit.index', 'unit.create', 'unit.edit',
    'brand.index', 'brand.create', 'brand.edit',
    'supplier.index', 'supplier.create', 'supplier.edit', 'supplier.show',
    'product-categories.index', 'product-categories.create', 'product-categories.edit', 'product-categories.add_edit_filter',
    'sub-category.index', 'sub-category.create', 'sub-category.edit',
    'product.index', 'product.create', 'product.edit',
    'purchase.index', 'purchase.create',
    'purchase.edit', 'purchase.details',
    'purchase.mrr_details','purchase.received',
    'purchase.supplier_payments_list',
    'supplier.advance','purchase.supplier_payment_history',
];
?>
@if(auth()->user()->can('purchase'))
    
    <li class="nav-item {{ in_array(Route::currentRouteName(), $subMenu) ? 'menu-open' : '' }}">
        <a href="#"
           class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
            <i class="nav-icon fa fa-shopping-bag"></i>
            <p>
                Purchase
                <i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <?php
                $subMenu = [
                    'unit.index', 'unit.create', 'unit.edit',
                    'brand.index', 'brand.create', 'brand.edit',
                    'supplier.index', 'supplier.create', 'supplier.edit', 'supplier.show',
                    'product-categories.index', 'product-categories.create', 'product-categories.edit', 'product-categories.add_edit_filter',
                    'sub-category.index', 'sub-category.create', 'sub-category.edit',
                    'product.index', 'product.create', 'product.edit'
                ];
            ?>
            @if(auth()->user()->can('purchase_settings') && auth()->user()->role == \App\Enumeration\RoleType::$ADMIN && auth()->user()->sisterConcern && auth()->user()->sisterConcern->is_head_office == 1)
                <li class="nav-item {{ in_array(Route::currentRouteName(), $subMenu) ? 'menu-open' : '' }}">
                    <a href="#"
                    class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
                        <i class="nav-icon fas fa-cogs"></i>
                        <p>
                            Purchase Settings
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">

                        <?php
                        $subSubMenu = ['supplier.index', 'supplier.create', 'supplier.edit', 'supplier.show'];
                        ?>
                        @if(auth()->user()->can('supplier'))
                            <li class="nav-item">
                                <a href="{{ route('supplier.index') }}"
                                class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                    <i class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                    <p>Suppliers</p>
                                </a>
                            </li>
                        @endif
                        <?php
                        $subSubMenu = ['unit.index', 'unit.create', 'unit.edit'];
                        ?>
                        @if(auth()->user()->can('product_unit'))
                            <li class="nav-item">
                                <a href="{{ route('unit.index') }}"
                                class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                    <i class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                    <p>Units</p>
                                </a>
                            </li>
                        @endif
                        <?php
                        $subSubMenu = ['brand.index', 'brand.create', 'brand.edit'];
                        ?>
                        @if(auth()->user()->can('brand'))
                            <li class="nav-item">
                                <a href="{{ route('brand.index') }}"
                                class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                    <i class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                    <p>Brands</p>
                                </a>
                            </li>
                        @endif
                        <?php
                        $subSubMenu = ['product-categories.index', 'product-categories.create', 'product-categories.edit', 'product-categories.add_edit_filter'];
                        ?>
                        @if(auth()->user()->can('product_category'))
                            <li class="nav-item">
                                <a href="{{ route('product-categories.index') }}"
                                class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                    <i class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                    <p>Product Category</p>
                                </a>
                            </li>
                        @endif
                        <?php
                        $subSubMenu = ['sub-category.index', 'sub-category.create', 'sub-category.edit'];
                        ?>
                        @if(auth()->user()->can('product_category'))
                            <li class="nav-item">
                                <a href="{{ route('sub-category.index') }}"
                                class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                    <i class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                    <p>Product Sub Category</p>
                                </a>
                            </li>
                        @endif
                        <?php
                        $subSubMenu = ['product.index', 'product.create', 'product.edit'];
                        ?>
                        @if(auth()->user()->can('product'))
                            <li class="nav-item">
                                <a href="{{ route('product.index') }}"
                                class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                    <i class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                    <p>Products</p>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
            <?php
            $subSubMenu = ['purchase.create'];
            ?>
            @if(auth()->user()->can('purchase_create'))
                <li class="nav-item">
                    <a href="{{ route('purchase.create') }}"
                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                        <i class="fa fa-plus nav-icon"></i>
                        <p>Purchase Create</p>
                    </a>
                </li>
            @endif
            <?php
            $subSubMenu = [
                'purchase.index', 'purchase.edit', 'purchase.details',
                'purchase.mrr_details','purchase.received'
            ];
            ?>
            @if(auth()->user()->can('purchase_list'))
                <li class="nav-item">
                    <a href="{{ route('purchase.index') }}"
                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                        <i class="fa fa-history nav-icon"></i>
                        <p>Purchase List</p>
                    </a>
                </li>
            @endif
            <?php
            $subSubMenu = [
                'purchase.supplier_payments_list','purchase.supplier_payment_history'
            ];
            ?>
            @if(auth()->user()->can('purchase_payment'))
                <li class="nav-item">
                    <a href="{{ route('purchase.supplier_payments_list') }}"
                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                        <i class="fa fa-money-bill nav-icon"></i>
                        <p>Supplier Payments</p>
                    </a>
                </li>
            @endif
                <?php
                $subSubMenu = [
                    'supplier.advance'
                ];
                ?>
            @if(auth()->user()->can('supplier_payment'))
                {{-- <li class="nav-item">
                    <a href="{{ route('supplier.advance') }}"
                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                        <i class="fa fa-history nav-icon"></i>
                        <p>Advance to Supplier</p>
                    </a>
                </li> --}}
            @endif
        </ul>
    </li>
@endif
<?php
$subMenu = ['inventory.index', 'inventory.details'];
?>
@if(auth()->user()->can('inventory'))
    <li class="nav-item">
        <a href="{{ route('inventory.index') }}"
           class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
            <i class="nav-icon fa fa-warehouse"></i>
            <p>Inventory</p>
        </a>
    </li>
@endif
