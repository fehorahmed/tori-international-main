@php
    $currentRoute = Route::currentRouteName();
@endphp

@if(auth()->user()->can('payroll'))
    <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px;">
        Payroll Module
    </li>
    <!-- Dashboard -->
    <li class="nav-item">
        <a href="{{ route('payroll.dashboard') }}" class="nav-link {{ Route::currentRouteName() == 'payroll.dashboard' ? 'active' : '' }}">
            <i class="nav-icon fas fa-chart-pie"></i>
            <p>Dashboard</p>
        </a>
    </li>

    <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px; margin-top: 10px;">
        Advance Operations
    </li>

    <!-- Employee Advance -->
    <li class="nav-item">
        <a href="{{ route('employee-advances.index') }}"
           class="nav-link {{ in_array($currentRoute, ['employee-advances.index']) ? 'active' : '' }}">
            <i class="nav-icon fas fa-hand-holding-usd"></i>
            <p>Employee Advance</p>
        </a>
    </li>
    
    <!-- Advance Requests -->
    <li class="nav-item">
        <a href="{{ route('employee-advances.requests') }}"
           class="nav-link {{ in_array($currentRoute, ['employee-advances.requests', 'employee-advances.create']) ? 'active' : '' }}">
            <i class="nav-icon fas fa-paper-plane"></i>
            <p>Advance Requests</p>
        </a>
    </li>

    <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px; margin-top: 10px;">
        Salary Settings
    </li>

    <!-- Salary Structures -->
    <li class="nav-item">
        <a href="{{ route('salary-structures.index') }}"
           class="nav-link {{ in_array($currentRoute, ['salary-structures.index', 'salary-structures.create', 'salary-structures.edit']) ? 'active' : '' }}">
            <i class="nav-icon fas fa-sitemap"></i>
            <p>Salary Structures</p>
        </a>
    </li>

    <!-- Category 1: Salary Operations -->
    <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px; margin-top: 10px;">
        Salary Operations
    </li>

    <!-- 1. Salary Process -->
    @if(auth()->user()->can('salary_process'))
        <li class="nav-item">
            <a href="{{ route('salary-processes.create') }}"
               class="nav-link {{ $currentRoute == 'salary-processes.create' ? 'active' : '' }}">
                <i class="nav-icon fas fa-spinner"></i>
                <p>Salary Process</p>
            </a>
        </li>
    @endif

    <!-- 2. Salary Lists -->
    @if(auth()->user()->can('salary_process_lists'))
        <li class="nav-item">
            <a href="{{ route('salary-processes.index') }}"
               class="nav-link {{ in_array($currentRoute, ['salary-processes.index', 'salary-processes.edit', 'salary-processes.details', 'salary-processes.item_edit']) ? 'active' : '' }}">
                <i class="nav-icon fas fa-history"></i>
                <p>Salary Lists</p>
            </a>
        </li>
    @endif

    <!-- Category 2: Payroll Reports -->
    @if(auth()->user()->can('payroll_reports'))
        <li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; padding-left: 10px; margin-top: 15px;">
            Payroll Reports
        </li>

        <!-- 3. Payroll Sheet -->
        @if(auth()->user()->can('salary_sheet'))
            <li class="nav-item">
                <a href="{{ route('report.salary-sheet') }}"
                   class="nav-link {{ $currentRoute == 'report.salary-sheet' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-file-invoice-dollar"></i>
                    <p>Payroll Sheet</p>
                </a>
            </li>
        @endif
        
        <!-- 4. Employee Advance Report -->
        <li class="nav-item">
            <a href="{{ route('report.employee-advance') }}"
               class="nav-link {{ $currentRoute == 'report.employee-advance' ? 'active' : '' }}">
                <i class="nav-icon fas fa-list-alt"></i>
                <p>Employee Advance</p>
            </a>
        </li>
    @endif
@endif
