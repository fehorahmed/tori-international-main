<?php
    $subMenu = [
        'ecom_product','ecom_product.add','ecom_product.edit','ecom_product.images','ecom_product.filters',
        'ecom_banner','ecom_banner.add','ecom_banner.edit','ecom_gallery','ecom_gallery.add','ecom_gallery.edit',
        'ecom_order','ecom_order.add','ecom_order.edit','ecom_order.details','ecom_order.confirm',
        'ecom_blog', 'ecom_blog.add', 'ecom_blog.edit', 'ecom_settings.update',
        'ecom_contact', 'ecom_contact.add', 'ecom_contact.edit',
        'ecom_page', 'ecom_page.add', 'ecom_page.edit', 'ecom_coupon', 'ecom_coupon.add', 'ecom_coupon.edit',
        'filters.index', 'filters.create', 'filters.edit',
    ];
    ?>
        
        <li class="nav-item {{ in_array(Route::currentRouteName(), $subMenu) ? 'menu-open' : '' }}">
            <a href="#"
                class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
                <i class="nav-icon fas fa-desktop"></i>
                <p>
                    Ecommerce
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <?php
                $subSubMenu = ['ecom_settings.update'];
                ?>
                
                    <li class="nav-item">
                        <a href="{{ route('ecom_settings.update',['setting'=>1]) }}"
                            class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                            <i
                                class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                            <p>Settings</p>
                        </a>
                    </li>
                
                
                <?php
                $subSubMenu = ['ecom_banner','ecom_banner.add','ecom_banner.edit'];
                ?>
                
                    <li class="nav-item">
                        <a href="{{ route('ecom_banner') }}"
                            class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                            <i
                                class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                            <p>Banners</p>
                        </a>
                    </li>
                
                <?php
                $subSubMenu = ['ecom_gallery','ecom_gallery.add','ecom_gallery.edit'];
                ?>
                
                    <li class="nav-item">
                        <a href="{{ route('ecom_gallery') }}"
                            class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                            <i
                                class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                            <p>Gallery</p>
                        </a>
                    </li>
                
                <?php
                $subSubMenu = ['ecom_page','ecom_page.add','ecom_page.edit'];
                ?>
                
                    <li class="nav-item">
                        <a href="{{ route('ecom_page') }}"
                            class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                            <i
                                class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                            <p>Pages</p>
                        </a>
                    </li>
                
                <?php
                $subSubMenu = ['ecom_coupon','ecom_coupon.add','ecom_coupon.edit'];
                ?>
                
                    <li class="nav-item">
                        <a href="{{ route('ecom_coupon') }}"
                            class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                            <i
                                class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                            <p>Coupons</p>
                        </a>
                    </li>
                
                <?php
                $subSubMenu = ['ecom_blog','ecom_blog.add','ecom_blog.edit'];
                ?>
                
                    <li class="nav-item">
                        <a href="{{ route('ecom_blog') }}"
                            class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                            <i
                                class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                            <p>Blogs</p>
                        </a>
                    </li>
                
                <?php
                $subSubMenu = ['ecom_product','ecom_product.add','ecom_product.edit','ecom_product.images','ecom_product.filters'];
                ?>
                
                    <li class="nav-item">
                        <a href="{{ route('ecom_product') }}"
                            class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('offer') != 1 ? 'active' : '' }}">
                            <i
                                class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) && request('offer') != 1 ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                            <p>Products</p>
                        </a>
                    </li>
                
                <?php
                $subSubMenu = ['ecom_product','ecom_product.add','ecom_product.edit','ecom_product.images'];
                ?>
                
                    <li class="nav-item">
                        <a href="{{ route('ecom_product',['offer'=>1]) }}"
                            class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('offer') == 1 ? 'active' : '' }}">
                            <i
                                class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) && request('offer') == 1 ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                            <p>Offer Products</p>
                        </a>
                    </li>

                <?php
                $subSubMenu = ['filters.index', 'filters.create', 'filters.edit'];
                ?>

                <li class="nav-item">
                    <a href="{{ route('filters.index') }}"
                        class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                        <i
                            class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                        <p>Filters</p>
                    </a>
                </li>
                
                <?php
                $subSubMenu = ['ecom_order','ecom_order.add','ecom_order.edit','ecom_order.details','ecom_order.confirm'];
                ?>
                
                    <li class="nav-item">
                        <a href="{{ route('ecom_order') }}"
                            class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                            <i
                                class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                            <p>Orders</p>
                        </a>
                    </li>
                
                <?php
                $subSubMenu = ['ecom_contact','ecom_contact.add','ecom_contact.edit'];
                ?>
                
                    <li class="nav-item">
                        <a href="{{ route('ecom_contact') }}"
                            class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                            <i
                                class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                            <p>Contacts</p>
                        </a>
                    </li>
                
            </ul>
        </li>
                

