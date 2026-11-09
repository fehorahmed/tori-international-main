<?php
$subMenu = [
    'trainee.index', 'trainee.create', 'trainee.edit', 'trainee.datatable',
    'trainee.tasks.index', 'trainee.tasks.distribute', 'trainee.tasks.manage', 'trainee.tasks.show',
    'trainee.visit_type.index', 'trainee.visit_type.create', 'trainee.visit_type.edit',
    'trainee.visits.index', 'trainee.visits.create'
];
?>

<li class="nav-item {{ in_array(Route::currentRouteName(), $subMenu) ? 'menu-open' : '' }}">
    <a href="#"
       class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
        <i class="nav-icon fas fa-user-graduate"></i>
        <p>
            Trainee Management
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('trainee.index') }}"
               class="nav-link {{ request()->is('admin/trainee') || request()->is('admin/trainee/*/edit') || request()->is('admin/trainee/create') ? 'active' : '' }}">
                <i class="{{ request()->is('admin/trainee') ? 'far fa-check-circle' : 'fas fa-arrow-right' }} nav-icon"></i>
                <p>Trainee List</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('trainee.tasks.index') }}"
               class="nav-link {{ request()->is('admin/trainee/tasks*') ? 'active' : '' }}">
                <i class="{{ request()->is('admin/trainee/tasks*') ? 'far fa-check-circle' : 'fas fa-arrow-right' }} nav-icon"></i>
                <p>Trainee Tasks</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('trainee.visit_type.index') }}"
               class="nav-link {{ request()->is('admin/trainee/visit-type*') ? 'active' : '' }}">
                <i class="{{ request()->is('admin/trainee/visit-type*') ? 'far fa-check-circle' : 'fas fa-arrow-right' }} nav-icon"></i>
                <p>Visit Types</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('trainee.visits.index') }}"
               class="nav-link {{ request()->is('admin/trainee/visits*') ? 'active' : '' }}">
                <i class="{{ request()->is('admin/trainee/visits*') ? 'far fa-check-circle' : 'fas fa-arrow-right' }} nav-icon"></i>
                <p>Visit Logs</p>
            </a>
        </li>
    </ul>
</li>
