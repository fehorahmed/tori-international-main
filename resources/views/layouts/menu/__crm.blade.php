<?php
$subMenu = [
    'crm-statuses.create',
    'crm-statuses.edit',
    'crm-statuses.index',
    'leads.index',
    'leads.create',
    'leads.edit',
    'leads.update_history',
];

?>
@if(auth()->user()->can('crm'))
    <li class="nav-item {{ in_array(Route::currentRouteName(), $subMenu) ? 'menu-open' : '' }}">
        <a href="#"
           class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
            <i class="nav-icon fas fa-tasks"></i>
            <p>
                CRM
                <i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <?php
            $subSubMenu = [
                'crm-statuses.create',
                'crm-statuses.edit',
                'crm-statuses.index',
            ];
            ?>
            @if(auth()->user()->can('crm_status'))
                <li class="nav-item">
                    <a href="{{ route('crm-statuses.index') }}"
                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                        <i class="fa fa-cogs  nav-icon"></i>
                        <p>Lead Type</p>
                    </a>
                </li>
            @endif
            <?php
                $subSubMenu = ['leads.create'];
            ?>
            @if(auth()->user()->can('lead_entry'))
                <li class="nav-item">
                    <a href="{{ route('leads.create') }}"
                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                        <i class="fa fa-plus nav-icon"></i>
                        <p>Lead Entry</p>
                    </a>
                </li>
            @endif
                <?php
                $subSubMenu = [
                    'leads.index',
                    'leads.edit',
                    'leads.update_history',
                ];
                ?>
            @if(auth()->user()->can('lead_manage'))
                <li class="nav-item">
                    <a href="{{ route('leads.index') }}"
                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                        <i class="fa fa-history nav-icon"></i>
                        <p>Leads Manage</p>
                    </a>
                </li>
            @endif
        </ul>
    </li>

@endif
