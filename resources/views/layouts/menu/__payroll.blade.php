<?php
$subMenu = [
    'salary-processes.index',
    'salary-processes.create',
    'salary-processes.item_edit',
    'salary-processes.edit',
    'salary-processes.details',
    'report.salary-sheet',
];
?>
@if(auth()->user()->can('payroll'))
    <li class="nav-item {{ in_array(Route::currentRouteName(), $subMenu) ? 'menu-open' : '' }}">
        <a href="#"
           class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
            <i class="nav-icon fas fa-users"></i>
            <p>
                Payroll
                <i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <?php
            $subSubMenu = [
                'salary-processes.index',
                'salary-processes.create',
                'salary-processes.edit',
                'salary-processes.details',
                'salary-processes.item_edit',
            ];
            ?>

            <li class="nav-item {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'menu-open' : '' }}">
                <a href="#"
                   class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active custom-third-menu-bg' : '' }}">
                    <i class="nav-icon fa fa-spinner"></i>
                    <p>
                        Salary
                        <i class="right fas fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview custom-third-layer">
                    <?php
                    $subSubMenu = ['salary-processes.create'];
                    ?>
                    @if(auth()->user()->can('salary_process'))
                        <li class="nav-item">
                            <a href="{{ route('salary-processes.create') }}"
                               class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                <i class="fa fa-plus nav-icon"></i>
                                <p>Salary Process</p>
                            </a>
                        </li>
                    @endif
                    <?php
                    $subSubMenu = [
                        'salary-processes.index',
                        'salary-processes.edit',
                        'salary-processes.details',

                    ];
                    ?>
                    @if(auth()->user()->can('salary_process_lists'))
                        <li class="nav-item">
                            <a href="{{ route('salary-processes.index') }}"
                               class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                <i class="fa fa-history nav-icon"></i>
                                <p>Salary Lists</p>
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
            <?php
            $subSubMenu = [
                'report.salary-sheet',
            ];
            ?>

            @if(auth()->user()->can('payroll_reports'))
                <li class="nav-item {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'menu-open' : '' }}">
                    <a href="#"
                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active custom-third-menu-bg' : '' }}">
                        <i class="nav-icon fa fa-list"></i>
                        <p>
                            Reports
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview custom-third-layer">

                        @if(auth()->user()->can('salary_sheet'))
                            <li class="nav-item">
                                <a href="{{ route('report.salary-sheet') }}"
                                   class="nav-link {{ Route::currentRouteName() == 'report.salary-sheet' ? 'active' : '' }}">
                                    <i class="far  {{ Route::currentRouteName() == 'report.salary-sheet' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                    <p>Payroll Sheet</p>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
        </ul>
    </li>
@endif
