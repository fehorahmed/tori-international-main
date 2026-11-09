<?php
$subMenu = [
    'sr.index','sr.create','sr.edit','sr.show',
    'sr-visits.index', 'sr-visits.create', 'sr-visits.show',
];
?>
@if(auth()->user()->can('sr_management'))
<li class="nav-item {{ in_array(Route::currentRouteName(), $subMenu) || request()->is('admin/tasks/sr*') ? 'menu-open' : '' }}">
    <a href="#"
        class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) || request()->is('admin/tasks/sr*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-person-walking"></i>
        <p>
            SR Management
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <?php
        $subSubMenu = ['sr.index','sr.create','sr.edit','sr.show'];
        ?>
        <li class="nav-item">
            <a href="{{ route('sr.index') }}"
                class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                <i class="{{ in_array(Route::currentRouteName(), $subSubMenu) ? 'far fa-check-circle' : 'fas fa-arrow-right' }} nav-icon"></i>
                <p>SRs</p>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('tasks.index', 'sr') }}"
                class="nav-link {{ request()->is('admin/tasks/sr*') ? 'active' : '' }}">
                <i class="{{ request()->is('admin/tasks/sr*') ? 'far fa-check-circle' : 'fas fa-arrow-right' }} nav-icon"></i>
                <p>SR Tasks</p>
            </a>
        </li>

        <?php
        $subSubMenu = ['sr-visits.index', 'sr-visits.create', 'sr-visits.show'];
        ?>
        <li class="nav-item">
            <a href="{{ route('sr-visits.index') }}"
                class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                <i class="{{ in_array(Route::currentRouteName(), $subSubMenu) ? 'far fa-check-circle' : 'fas fa-arrow-right' }} nav-icon"></i>
                <p>SR Visits</p>
            </a>
        </li>        

    </ul>
</li>
@endif




