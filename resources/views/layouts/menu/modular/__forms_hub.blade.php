<li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.75rem;">Forms Hub Module</li>

@if(auth()->user()->can('forms_hub'))
<li class="nav-item">
    <a href="{{ route('forms_hub.dashboard') }}" class="nav-link {{ $currentRoute == 'forms_hub.dashboard' ? 'active' : '' }}">
        <i class="nav-icon fas fa-file-pdf"></i>
        <p>Dashboard</p>
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('forms_hub.customer') }}" class="nav-link {{ $currentRoute == 'forms_hub.customer' ? 'active' : '' }}" target="_blank">
        <i class="nav-icon fas fa-users"></i>
        <p>Customer Form</p>
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('forms_hub.supplier') }}" class="nav-link {{ $currentRoute == 'forms_hub.supplier' ? 'active' : '' }}" target="_blank">
        <i class="nav-icon fas fa-truck"></i>
        <p>Supplier Form</p>
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('forms_hub.dealer') }}" class="nav-link {{ $currentRoute == 'forms_hub.dealer' ? 'active' : '' }}" target="_blank">
        <i class="nav-icon fas fa-handshake"></i>
        <p>Dealer Form</p>
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('forms_hub.dps') }}" class="nav-link {{ $currentRoute == 'forms_hub.dps' ? 'active' : '' }}" target="_blank">
        <i class="nav-icon fas fa-leaf"></i>
        <p>DPS Form</p>
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('forms_hub.fdr') }}" class="nav-link {{ $currentRoute == 'forms_hub.fdr' ? 'active' : '' }}" target="_blank">
        <i class="nav-icon fas fa-lock"></i>
        <p>FDR Form</p>
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('forms_hub.shomriddhi.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'forms_hub.shomriddhi') ? 'active' : '' }}">
        <i class="nav-icon fas fa-piggy-bank"></i>
        <p>Shomriddhi Forms</p>
    </a>
</li>
@endif
