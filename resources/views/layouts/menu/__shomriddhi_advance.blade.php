<li class="nav-item {{ request()->is('admin/shomriddhi-advance*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ request()->is('admin/shomriddhi-advance*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-piggy-bank"></i>
        <p>
            Shomriddhi Advance
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('shomriddhi-advance.create') }}" class="nav-link {{ request()->routeIs('shomriddhi-advance.create') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon text-success"></i>
                <p>Deposit / Withdraw Entry</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('shomriddhi-advance.accounts.index') ?? '#' }}" class="nav-link {{ request()->routeIs('shomriddhi-advance.accounts.index') || request()->routeIs('shomriddhi-advance.accounts.datatable') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon text-primary"></i>
                <p>Accounts List</p>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('shomriddhi-advance.index') }}" class="nav-link {{ request()->routeIs('shomriddhi-advance.index') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon text-info"></i>
                <p>Pending Transactions</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('shomriddhi-advance.slabs.index') ?? '#' }}" class="nav-link {{ request()->routeIs('shomriddhi-advance.slabs.*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon text-warning"></i>
                <p>Interest Slabs (Settings)</p>
            </a>
        </li>
    </ul>
</li>
