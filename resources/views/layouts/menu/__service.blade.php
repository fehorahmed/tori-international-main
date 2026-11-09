<?php
$subMenu = [
    'service.request.index', 'service.request.create', 'service.request.edit', 'service.request.details',
    'service.technician.index', 'service.technician.create', 'service.technician.edit',
    'service.panel.index', 'service.panel.update_form'
];
?>
<li class="nav-item {{ in_array(Route::currentRouteName(), $subMenu) ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
        <i class="nav-icon fas fa-tools"></i>
        <p>
            Service Management
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        @if(auth()->user()->role == 'Admin' || auth()->user()->role == 'Staff')
            <li class="nav-item">
                <a href="{{ route('service.request.index') }}"
                   class="nav-link {{ request()->is('*service/request*') ? 'active' : '' }}">
                    <i class="far {{ request()->is('*service/request*') ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>Service Requests</p>
                </a>
            </li>
        @endif

        @if(auth()->user()->role == 'Admin')
            <li class="nav-item">
                <a href="{{ route('service.technician.index') }}"
                   class="nav-link {{ request()->is('*service/technician*') ? 'active' : '' }}">
                    <i class="far {{ request()->is('*service/technician*') ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>Technicians</p>
                </a>
            </li>
        @endif

        @if(auth()->user()->role == 'Technician')
            <li class="nav-item">
                <a href="{{ route('service.panel.index') }}"
                   class="nav-link {{ request()->is('*service/panel*') ? 'active' : '' }}">
                    <i class="far {{ request()->is('*service/panel*') ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                    <p>Technician Panel</p>
                </a>
            </li>
        @endif
    </ul>
</li>
