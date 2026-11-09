<?php
$subMenu = [
    'account-group.index', 'account-group.create', 'account-group.edit',
    'account-head.index', 'account-head.create', 'account-head.edit',
    'voucher.index', 'voucher.create', 'voucher.edit', 'voucher.details',
    'cashbook',
];
?>
@if(auth()->user()->can('accounts'))
    <li class="nav-item {{ in_array(Route::currentRouteName(), $subMenu) ? 'menu-is-opening menu-open' : '' }}">
        <a href="#"
           class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
            <i class="nav-icon fas fa-calculator"></i>
            <p>
                Accounts
                <i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <?php
            $subSubMenu = ['account-group.index', 'account-group.create', 'account-group.edit'];
            ?>

            <!-- @if(auth()->user()->can('account_group'))
                <li class="nav-item d-none {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'menu-open' : '' }}">
                    <a href="#"
                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active custom-third-menu-bg' : '' }}">
                        <i class="nav-icon fa fa-list"></i>
                        <p>
                            Account Groups
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview custom-third-layer">
                        <?php
                        $subSubMenu = ['account-group.create'];
                        ?>
                        @if(auth()->user()->role != 'Superadmin' && auth()->user()->can('account_group_create'))
                            <li class="nav-item">
                                <a href="{{ route('account-group.create') }}"
                                   class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                    <i class="fa fa-plus nav-icon"></i>
                                    <p>Add New</p>
                                </a>
                            </li>
                        @endif
                        <?php
                        $subSubMenu = ['account-group.index', 'account-group.edit'];
                        ?>
                        @if(auth()->user()->can('account_group'))
                            <li class="nav-item">
                                <a href="{{ route('account-group.index') }}"
                                   class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                    <i class="fa fa-history nav-icon"></i>
                                    <p>Lists</p>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif -->


            <?php
            $subSubMenu = ['account-head.index', 'account-head.create', 'account-head.edit'];
            ?>
            @if(auth()->user()->can('payment_modes'))
                <li class="nav-item {{ in_array(Route::currentRouteName(), $subSubMenu)  && request('payment_mode') != 0  ? 'menu-open' : '' }}">
                    <a href="#"
                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu)  && request('payment_mode') != 0  ? 'active custom-third-menu-bg' : '' }}">
                        <i class="nav-icon fa fa-list-alt"></i>
                        <p>
                            Payment Modes
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview custom-third-layer">
                        <?php
                        $subSubMenu = ['account-head.create'];
                        ?>
                        @if(auth()->user()->can('payment_modes_create'))
                            <li class="nav-item">
                                <a href="{{ route('account-head.create',['payment_mode'=>1]) }}"
                                   class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('payment_mode') != 0 ? 'active' : '' }}">
                                    <i class="fa fa-plus nav-icon"></i>
                                    <p>Add New</p>
                                </a>
                            </li>
                        @endif
                        <?php
                        $subSubMenu = ['account-head.index', 'account-head.edit'];
                        ?>
                        @if(auth()->user()->can('payment_modes'))
                            <li class="nav-item">
                                <a href="{{ route('account-head.index',['payment_mode'=>1]) }}"
                                   class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('payment_mode') != 0 ? 'active' : '' }}">
                                    <i class="fa fa-history nav-icon"></i>
                                    <p>Lists</p>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
            <?php
            $subSubMenu = ['account-head.index', 'account-head.create', 'account-head.edit'];
            ?>
            @if(auth()->user()->can('account_head'))
                <li class="nav-item {{ in_array(Route::currentRouteName(), $subSubMenu) && request('payment_mode') == 0  ? 'menu-open' : '' }}">
                    <a href="#"
                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('payment_mode') == 0  ? 'active custom-third-menu-bg' : '' }}">
                        <i class="nav-icon fa fa-list-ol"></i>
                        <p>
                            Chart of Accounts
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview custom-third-layer">
                        <?php
                        $subSubMenu = ['account-head.create'];
                        ?>
                        @if(auth()->user()->role != 'Superadmin' && auth()->user()->can('account_head_create'))
                            <li class="nav-item">
                                <a href="{{ route('account-head.create',['payment_mode'=>0]) }}"
                                   class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('payment_mode') == 0 ? 'active' : '' }}">
                                    <i class="fa fa-plus nav-icon"></i>
                                    <p>Add New</p>
                                </a>
                            </li>
                        @endif
                        <?php
                        $subSubMenu = ['account-head.index', 'account-head.edit'];
                        ?>
                        @if(auth()->user()->can('account_head'))
                            <li class="nav-item">
                                <a href="{{ route('account-head.index',['payment_mode'=>0]) }}"
                                   class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('payment_mode') == 0 ? 'active' : '' }}">
                                    <i class="fa fa-history nav-icon"></i>
                                    <p>Lists</p>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
            <?php
            $subSubMenu = ['voucher.index', 'voucher.details', 'voucher.create', 'voucher.edit'];
            ?>
            @if(auth()->user()->can('payment_voucher'))
                <li class="nav-item {{ in_array(Route::currentRouteName(), $subSubMenu) && request('voucher_type') == \App\Enumeration\VoucherType::$PAYMENT_VOUCHER  ? 'menu-open' : '' }}">
                    <a href="#"
                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('voucher_type') == \App\Enumeration\VoucherType::$PAYMENT_VOUCHER  ? 'active custom-third-menu-bg' : '' }}">
                        <i class="nav-icon fa fa-book"></i>
                        <p>
                            Payment Voucher
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview custom-third-layer">
                        <?php
                        $subSubMenu = ['voucher.create'];
                        ?>
                        @if(auth()->user()->role != 'Superadmin' && auth()->user()->can('payment_voucher'))
                            <li class="nav-item">
                                <a href="{{ route('voucher.create',['voucher_type'=>\App\Enumeration\VoucherType::$PAYMENT_VOUCHER]) }}"
                                   class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('voucher_type') == \App\Enumeration\VoucherType::$PAYMENT_VOUCHER ? 'active' : '' }}">
                                    <i class="fa fa-plus nav-icon"></i>
                                    <p>Add New</p>
                                </a>
                            </li>
                        @endif
                        <?php
                        $subSubMenu = ['voucher.index', 'voucher.details',
                            'voucher.edit'];
                        ?>
                        <li class="nav-item">
                            <a href="{{ route('voucher.index',['voucher_type'=>\App\Enumeration\VoucherType::$PAYMENT_VOUCHER]) }}"
                               class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('voucher_type') == \App\Enumeration\VoucherType::$PAYMENT_VOUCHER ? 'active' : '' }}">
                                <i class="fa fa-history nav-icon"></i>
                                <p>Lists</p>
                            </a>
                        </li>
                    </ul>
                </li>
            @endif
            <?php
            $subSubMenu = ['voucher.index', 'voucher.details', 'voucher.create', 'voucher.edit'];
            ?>
            @if(auth()->user()->can('receipt_voucher'))
                <li class="nav-item {{ in_array(Route::currentRouteName(), $subSubMenu) && request('voucher_type') == \App\Enumeration\VoucherType::$COLLECTION_VOUCHER  ? 'menu-open' : '' }}">
                    <a href="#"
                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('voucher_type') == \App\Enumeration\VoucherType::$COLLECTION_VOUCHER  ? 'active custom-third-menu-bg' : '' }}">
                        <i class="nav-icon fa fa-book"></i>
                        <p>
                            Receipt Voucher
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview custom-third-layer">
                        <?php
                        $subSubMenu = ['voucher.create'];
                        ?>
                        @if(auth()->user()->role != 'Superadmin' && auth()->user()->can('receipt_voucher_create'))
                            <li class="nav-item">
                                <a href="{{ route('voucher.create',['voucher_type'=>\App\Enumeration\VoucherType::$COLLECTION_VOUCHER]) }}"
                                   class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('voucher_type') == \App\Enumeration\VoucherType::$COLLECTION_VOUCHER ? 'active' : '' }}">
                                    <i class="fa fa-plus nav-icon"></i>
                                    <p>Add New</p>
                                </a>
                            </li>
                        @endif
                        <?php
                        $subSubMenu = ['voucher.index', 'voucher.details',
                            'voucher.edit'];
                        ?>
                        <li class="nav-item">
                            <a href="{{ route('voucher.index',['voucher_type'=>\App\Enumeration\VoucherType::$COLLECTION_VOUCHER]) }}"
                               class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('voucher_type') == \App\Enumeration\VoucherType::$COLLECTION_VOUCHER ? 'active' : '' }}">
                                <i class="fa fa-history nav-icon"></i>
                                <p>Lists</p>
                            </a>
                        </li>
                    </ul>
                </li>

            @endif
            <?php
            $subSubMenu = ['voucher.index', 'voucher.details', 'voucher.create', 'voucher.edit'];
            ?>
            @if(auth()->user()->can('contra_voucher'))
                <li class="nav-item {{ in_array(Route::currentRouteName(), $subSubMenu) && request('voucher_type') == \App\Enumeration\VoucherType::$CONTRA_VOUCHER  ? 'menu-open' : '' }}">
                    <a href="#"
                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('voucher_type') == \App\Enumeration\VoucherType::$CONTRA_VOUCHER  ? 'active custom-third-menu-bg' : '' }}">
                        <i class="nav-icon fa fa-book"></i>
                        <p>
                            Contra Voucher
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview custom-third-layer">
                        <?php
                        $subSubMenu = ['voucher.create'];
                        ?>
                        @if(auth()->user()->role != 'Superadmin' && auth()->user()->can('contra_voucher_create'))
                            <li class="nav-item">
                                <a href="{{ route('voucher.create',['voucher_type'=>\App\Enumeration\VoucherType::$CONTRA_VOUCHER]) }}"
                                   class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('voucher_type') == \App\Enumeration\VoucherType::$CONTRA_VOUCHER ? 'active' : '' }}">
                                    <i class="fa fa-plus nav-icon"></i>
                                    <p>Add New</p>
                                </a>
                            </li>
                        @endif
                        <?php
                        $subSubMenu = ['voucher.index', 'voucher.details',
                            'voucher.edit'];
                        ?>
                        <li class="nav-item">
                            <a href="{{ route('voucher.index',['voucher_type'=>\App\Enumeration\VoucherType::$CONTRA_VOUCHER]) }}"
                               class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('voucher_type') == \App\Enumeration\VoucherType::$CONTRA_VOUCHER ? 'active' : '' }}">
                                <i class="fa fa-history nav-icon"></i>
                                <p>Lists</p>
                            </a>
                        </li>
                    </ul>
                </li>
            @endif
            <?php
            $subSubMenu = ['voucher.index', 'voucher.details', 'voucher.create', 'voucher.edit'];
            ?>
            @if(auth()->user()->can('journal_voucher'))
                <li class="nav-item {{ in_array(Route::currentRouteName(), $subSubMenu) && request('voucher_type') == \App\Enumeration\VoucherType::$JOURNAL_VOUCHER  ? 'menu-open' : '' }}">
                    <a href="#"
                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('voucher_type') == \App\Enumeration\VoucherType::$JOURNAL_VOUCHER  ? 'active custom-third-menu-bg' : '' }}">
                        <i class="nav-icon fa fa-book"></i>
                        <p>
                            Journal Voucher
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview custom-third-layer">
                        <?php
                        $subSubMenu = ['voucher.create'];
                        ?>
                        @if(auth()->user()->role != 'Superadmin' && auth()->user()->can('journal_voucher_create'))
                            <li class="nav-item">
                                <a href="{{ route('voucher.create',['voucher_type'=>\App\Enumeration\VoucherType::$JOURNAL_VOUCHER]) }}"
                                   class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('voucher_type') == \App\Enumeration\VoucherType::$JOURNAL_VOUCHER ? 'active' : '' }}">
                                    <i class="fa fa-plus nav-icon"></i>
                                    <p>Add New</p>
                                </a>
                            </li>
                        @endif
                        <?php
                        $subSubMenu = ['voucher.index', 'voucher.details',
                            'voucher.edit'];
                        ?>
                        <li class="nav-item">
                            <a href="{{ route('voucher.index',['voucher_type'=>\App\Enumeration\VoucherType::$JOURNAL_VOUCHER]) }}"
                               class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('voucher_type') == \App\Enumeration\VoucherType::$JOURNAL_VOUCHER ? 'active' : '' }}">
                                <i class="fa fa-history nav-icon"></i>
                                <p>Lists</p>
                            </a>
                        </li>
                    </ul>
                </li>

            @endif
        </ul>
    </li>
@endif


