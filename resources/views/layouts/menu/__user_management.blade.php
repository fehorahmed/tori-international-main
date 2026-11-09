<?php
    $subMenu = [
        'user.index', 'user.create','user.edit','user.permission',
        'role.index', 'role.create','role.edit',
    ];
?>
@if(auth()->user()->can('user_management'))
    <li class="nav-item {{ in_array(Route::currentRouteName(), $subMenu) ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
            <i class="nav-icon fa fa-user-cog"></i>
            <p>Access Control <i class="right fas fa-angle-left"></i></p>
        </a>
        <ul class="nav nav-treeview">
            @can('users')
            <li class="nav-item">
                <a href="{{ route('user.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['user.index', 'user.create','user.edit','user.permission']) ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Users List</p>
                </a>
            </li>
            @endcan
            @can('role_management')
            <li class="nav-item">
                <a href="{{ route('role.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['role.index', 'role.create','role.edit']) ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Role List</p>
                </a>
            </li>
            @endcan
        </ul>
    </li>
@endif
