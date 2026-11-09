@can('office_management') 
<li class="nav-header text-uppercase font-weight-bold text-muted" style="font-size: 0.75rem;">Office Utility</li>
<li class="nav-item">
    <a href="{{ route('office_management.dashboard') }}" class="nav-link {{ Route::currentRouteName() == 'office_management.dashboard' ? 'active' : '' }}">
        <i class="nav-icon fas fa-tachometer-alt"></i>
        <p>Dashboard</p>
    </a>
</li>
@can('notice_view')
<li class="nav-item">
    <a href="{{ route('notices.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'notices.') ? 'active' : '' }}">
        <i class="nav-icon fas fa-bullhorn"></i>
        <p>Notices</p>
    </a>
</li>
@endcan
@can('meeting_view')
<li class="nav-item">
    <a href="{{ route('meetings.index') }}" class="nav-link {{ str_starts_with($currentRoute, 'meetings.') ? 'active' : '' }}">
        <i class="nav-icon fas fa-handshake"></i>
        <p>Meeting Summary</p>
    </a>
</li>
@endcan
@endcan