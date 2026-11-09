<li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.75rem;">Incentive Management</li>

@if(auth()->user()->can('incentive_settings'))
<li class="nav-item">
    <a href="{{ route('incentive-settings.index') }}" class="nav-link {{ in_array($currentRoute, ['incentive-settings.index', 'incentive-settings.create', 'incentive-settings.global']) ? 'active' : '' }}">
        <i class="nav-icon fas fa-sliders-h"></i>
        <p>Incentive Settings</p>
    </a>
</li>
@endif

@if(auth()->user()->can('incentive_payment'))
<li class="nav-item">
    <a href="{{ route('incentive-payments.index') }}" class="nav-link {{ in_array($currentRoute, ['incentive-payments.index', 'incentive-payments.ledger-page']) ? 'active' : '' }}">
        <i class="nav-icon fas fa-hand-holding-usd"></i>
        <p>Employee Incentive</p>
    </a>
</li>
@endif
