<?php
$subMenu = ['tracking.live_location','tracking.location_history'];
?>
@if(auth()->user()->can('monitoring'))
<li class="nav-item {{ in_array(Route::currentRouteName(), $subMenu) ? 'menu-open' : '' }}">
    <a href="#"
        class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
        <i class="nav-icon fas fa-bullseye"></i>
        <p>
            Monitoring
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <?php
        $subSubMenu = ['tracking.live_location'];
        ?>
        @if(auth()->user()->can('live_location'))
        <li class="nav-item">
            <a href="{{ route('tracking.live_location') }}"
                class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                <i
                    class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                <p>Live Location</p>
            </a>
        </li>
        @endif
        
        <?php
        $subSubMenu = ['tracking.location_history'];
        ?>
        @if(auth()->user()->can('location_history'))
        <li class="nav-item">
            <a href="{{ route('tracking.location_history') }}"
                class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                <i
                    class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                <p>Location History</p>
            </a>
        </li>
        @endif
    </ul>
</li>
@endif
